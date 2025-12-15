<?php

use Configuration as Cfg;
use Tpay\CustomerData;
use Tpay\Handler\BasicPaymentHandler;
use Tpay\Service\SurchargeService;
use Tpay\Service\TransactionService;
use Tpay\Util\Helper;

if (!defined('_PS_VERSION_')) {
    exit;
}

class TpayChargeBlikModuleFrontController extends ModuleFrontController
{
    public const TYPE = 'blik';

    public $cartId;

    /**
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     */
    public function initContent()
    {
        parent::initContent();

        $cartId = Tools::getValue('cartId');
        $cart = $cartId ? new Cart($cartId) : $this->context->cart;

        $context = $this->context;
        $customer = new Customer($cart->id_customer);
        $address = new AddressCore($cart->id_address_invoice);
        $action = Tools::getValue('action');
        $status = true;

        if ($this->currentCartValidate($cart) || !$this->module->active) {
            $status = false;
        }

        if (!Validate::isLoadedObject($customer)) {
            $status = false;
        }

        if (!$status) {
            echo json_encode(
                [
                    'status' => 'FAILURE',
                    'message' => $this->trans('Client identificator not provided.', [], 'Modules.Tpay.Shop'),
                ]
            );
            exit;
        }

        switch ($action) {
            case 'createTransaction':
                // firstly - create order
                $order = $this->createOrder($cart, $customer);
                // I create an order if it does not exist with such a transaction id
                $this->createTransaction($address, $customer, $context, $cart, $order);
                break;
            case 'payBlikTransaction':
                $order = new Order(Order::getIdByCartId($cart->id));

                $this->payBlikTransaction($address, $customer, $context, $cart, $order, Tools::getValue('transactionId'));
                break;
            case 'payByTransfer':
                $order = new Order(Tools::getValue('orderIdForTransfer'));

                $this->payByTransfer($address, $customer, $context, $cart, $order, Tools::getValue('transactionId'));
                break;
            case 'blik0Status':
                $this->blik0Status(Tools::getValue('transactionId'));
                break;
        }
        exit;
    }

    public function blik0Status($transactionId)
    {
        $result = $this->waitForBlikAccept($transactionId, 0);

        $this->ajaxRender(
            json_encode(
                [
                'status' => $result['status'],
                'result' => $result,
                ]
            )
        );
    }

    /**
     * Create transaction
     *
     * @throws PrestaShopException
     * @throws Exception
     */
    public function createTransaction($address, $customer, $context, $cart, $order)
    {
        $transactionParams = $this->getCustomerData($address, $customer, $context, $cart, $order, self::TYPE);

        $isoCode = Language::getLanguage($cart->id_lang)['iso_code'];
        $transactionParams['lang'] = in_array($isoCode, ['pl', 'en']) ? $isoCode : 'en';

        $blikCode = Tools::getValue('blikCode');
        $transaction = $this->createBlikZero($transactionParams, $blikCode, $customer->isGuest() ? null : $cart);
        $transactionId = $transaction['transactionId'];

        $transactionRepository = $this->module->getService('tpay.repository.transaction');
        $transactionExists = $transactionRepository->getTransactionByTransactionId($transactionId);

        if (!$transactionExists) {
            $this->createTransactionInDb($transaction, $order->id, false);
        }

        $this->ajaxRender(
            json_encode(
                [
                'status' => $transaction['status'],
                'backUrl' => $this->module->getContext()->link->getModuleLink(
                    'tpay',
                    'blik',
                    [
                        'orderId' => $order->id,
                        'status' => $transaction['status'],
                    ]
                ),
                'orderId' => $order->id,
                'transactionId' => $transactionId,
                ]
            )
        );
    }

    /**
     * @throws PrestaShopException
     * @throws Exception
     */
    public function payBlikTransaction($address, $customer, $context, $cart, $order, $oldTransactionId)
    {
        $transactionParams = $this->getCustomerData($address, $customer, $context, $cart, $order, self::TYPE);

        $isoCode = Language::getLanguage($cart->id_lang)['iso_code'];
        $transactionParams['lang'] = in_array($isoCode, ['pl', 'en']) ? $isoCode : 'en';

        $blikCode = $this->validateBlikCode(Tools::getValue('blikCode'));
        $transaction = $this->createBlikZero($transactionParams, $blikCode, $customer->isGuest() ? null : $cart);

        $this->cancelTransaction($oldTransactionId);
        $transactionRepository = $this->module->getService('tpay.repository.transaction');
        $transactionExists = $transactionRepository->getTransactionByTransactionId($oldTransactionId);

        if ($transactionExists) {
            $this->updateTransactionInDb($transaction, $order->id, $oldTransactionId, self::TYPE);
        }

        if ('success' == $transaction['result']) {
            $result = $this->waitForBlikAccept($transaction['transactionId'], Tools::getValue('transactionCounter'));
        } else {
            $result['status'] = 'error';
        }

        $this->ajaxRender(
            json_encode(
                [
                'result' => $result['status'],
                ]
            )
        );
    }

    /**
     * Process of saving the transaction
     *
     * @throws Exception
     */
    public function createTransactionInDb($transaction, $orderId, bool $redirect = true): void
    {
        if (isset($transaction['transactionId'])) {
            /**
 * @var TransactionService $transactionService 
*/
            $transactionService = $this->module->getService('tpay.service.transaction');
            $transactionService->transactionProcess(
                $transaction,
                self::TYPE,
                (int) $orderId,
                $redirect
            );
        }
    }

    /**
     * Process of saving the transaction
     *
     * @throws Exception
     */
    public function updateTransactionInDb($transaction, $orderId, $oldTransactionId, $paymentType): void
    {
        if (isset($transaction['transactionId'])) {
            /**
 * @var TransactionService $transactionService 
*/
            $transactionService = $this->module->getService('tpay.service.transaction');
            $transactionService->updateTransaction($transaction, $oldTransactionId, $paymentType, $orderId);
        }
    }

    /**
     * Get customer data
     *
     * @throws Exception
     */
    public function getCustomerData($address, $customer, $context, $cart, $order, $type): array
    {
        $customerData = new CustomerData($address, $customer, $context, $cart, $order);

        if (self::TYPE === $type) {
            $customerData->createBlikCallbacks(self::TYPE);
        } else {
            $customerData->createCallbacks($order, BasicPaymentHandler::TYPE);
        }

        $customerData->createDescription();

        return $customerData->getData();
    }

    /**
     * @return false|string
     */
    public function validateBlikCode($blikCode)
    {
        if (preg_match('/[^a-z_\-0-9 ]/i', $blikCode) && 6 !== Tools::strlen($blikCode)) {
            return false;
        }

        return (string) $blikCode;
    }

    private function currentCartValidate($cart): bool
    {
        return 0 === $cart->id_customer || 0 === $cart->id_address_delivery || 0 === $cart->id_address_invoice;
    }

    /**
     * Validate order
     */
    private function blikValidateOrder($cartId, $orderTotal, $customer): void
    {
        $this->module->validateOrder(
            (int) $cartId,
            (int) Cfg::get('TPAY_PENDING'),
            $orderTotal,
            $this->module->displayName,
            null,
            [],
            (int) $this->context->currency->id,
            $customer->isGuest() ? 0 : 1,
            $customer->secure_key
        );
    }

    /**
     * @throws Exception
     */
    private function getOrderTotal($cart): float
    {
        $surcharge = new SurchargeService();
        $orderTotal = $cart->getOrderTotal();
        $surchargeTotal = $surcharge->getSurchargeValue($orderTotal);

        return (float) $orderTotal + $surchargeTotal;
    }

    private function createOrder($cart, $customer)
    {
        $order = new Order(Order::getIdByCartId($cart->id));
        if (!$order->id) {
            if (Validate::isLoadedObject($cart) && !$cart->OrderExists()) {
                $this->blikValidateOrder($cart->id, $this->getOrderTotal($cart), $customer);
                $order = new Order($this->module->currentOrder);
            }
        }

        return $order;
    }

    /**
     * @throws Exception
     */
    private function getBlikAlias($cart, $blikCode = '')
    {
        // Use new blik token
        $userAlias = 'user_'.$cart->id_customer.'_'.Helper::generateRandomString(5);

        // Use alias
        if (empty($blikCode)) {
            $userAlias = $this->module->getService('tpay.repository.blik')->getBlikAliasIdByUserId(
                $cart->id_customer
            );
        }

        return $userAlias;
    }

    private function createBlikZero(array $data, string $blikCode = '', $cart = null): array
    {
        $transaction = $this->module->api->transactions()->createTransactionWithInstantRedirection($data);
        $transactionId = $transaction['transactionId'];

        return $this->blik($transactionId, $blikCode, $cart);
    }

    private function blik(string $transactionId, string $blikCode, $cart = null): array
    {
        $additional_payment_data = [
            'channelId' => 64,
            'method' => 'pay_by_link',
            'blikPaymentData' => [
                'type' => 0,
            ],
        ];

        if ($cart) {
            $additional_payment_data['blikPaymentData']['aliases'] = [
                'value' => $this->getBlikAlias($cart, $blikCode),
                'type' => 'UID',
                'label' => Cfg::get('PS_SHOP_NAME'),
            ];

            if (!empty($blikCode)) {
                $additional_payment_data['blikPaymentData']['blikToken'] = $this->validateBlikCode($blikCode);
            }
        } else {
            $additional_payment_data['blikPaymentData']['blikToken'] = $blikCode;
        }

        return $this->module->api->transactions()->createInstantPaymentByTransactionId($additional_payment_data, $transactionId);
    }

    private function payByTransfer($address, $customer, $context, $cart, $order, string $oldTransactionId)
    {
        $transactionParams = $this->getCustomerData($address, $customer, $context, $cart, $order, 'transfer');
        $transactionParams['amount'] = $order->total_paid;
        $isoCode = Language::getLanguage($cart->id_lang)['iso_code'];
        $transactionParams['lang'] = in_array($isoCode, ['pl', 'en']) ? $isoCode : 'en';

        if (empty($transactionParams['payer']['name']) || ' ' == $transactionParams['payer']['name']) {
            $transactionParams['payer']['name'] = sprintf(
                '%s %s',
                $customer->firstname,
                $customer->lastname
            );
        }

        if (empty($transactionParams['payer']['email']) || !$transactionParams['payer']['email']) {
            $transactionParams['payer']['email'] = $customer->email;
        }

        $transaction = $this->module->api->transactions()->createTransaction($transactionParams);

        if (!isset($transaction['transactionPaymentUrl'])) {
            $this->ajaxRender(
                json_encode(
                    [
                    'status' => 'error',
                    ]
                )
            );
        }

        $this->cancelTransaction($oldTransactionId);
        $transactionRepository = $this->module->getService('tpay.repository.transaction');
        $transactionExists = $transactionRepository->getTransactionByTransactionId($oldTransactionId);

        if ($transactionExists) {
            $this->updateTransactionInDb($transaction, $order->id, $oldTransactionId, 'transfer');
        }

        $this->ajaxRender(
            json_encode(
                [
                'status' => 'correct',
                'payment_url' => $transaction['transactionPaymentUrl'],
                ]
            )
        );
    }

    private function waitForBlikAccept($transactionId, $transactionCounter): array
    {
        $stop = false;
        $i = 0;
        do {
            $correct = false;
            $result = $this->module->api->transactions()->getTransactionById($transactionId);
            $errors = 0;

            foreach ($result['payments']['attempts'] as $error) {
                if ('' != $error['paymentErrorCode']) {
                    $errors++;
                }
            }

            if ('correct' == $result['status']) {
                $correct = true;
            }

            if (60 == $i || $correct) {
                $stop = true;
            }

            if ($errors > $transactionCounter && !$correct) {
                $stop = true;
                $result['payments']['errors'] = $result['payments']['attempts'];
            }

            sleep(1);
            $i++;
        } while (!$stop);

        return $result;
    }

    private function cancelTransaction($transactionId)
    {
        $this->module->api->transactions()->run('POST', sprintf('/transactions/%s/cancel', $transactionId));
    }
}
