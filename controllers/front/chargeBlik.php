<?php

/**
 * NOTICE OF LICENSE
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Tpay
 * @copyright 2010-2022 tpay.com
 * @license   LICENSE.txt
 */

use Configuration as Cfg;
use Tpay\Config\Config;
use Tpay\CustomerData;
use Tpay\Service\SurchargeService;
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
        $cart = $cartId ? new \Cart($cartId) : $this->context->cart;

        $context = $this->context;
        $customer = new \Customer($cart->id_customer);
        $address = new \AddressCore($cart->id_address_invoice);
        $action   = Tools::getValue('action');
        $status   = true;

        if ($this->currentCartValidate($cart) || !$this->module->active) {
            $status = false;
        }

        if (!Validate::isLoadedObject($customer)) {
            $status = false;
        }

        if (!$status) {
            echo json_encode(
                [
                    'status'  => 'FAILURE',
                    'message' => $this->module->l('Client identificator not provided.', 'tpay'),
                ]
            );
            exit;
        }

        switch ($action) {
            case 'createTransaction':
                // firstly - create order
                $order = new \Order(\Order::getIdByCartId($cart->id));
                if (!$order->id) {
                    if (Validate::isLoadedObject($cart) && !$cart->OrderExists()) {
                        $this->blikValidateOrder($cart->id, $this->getOrderTotal($cart), $customer);
                        $order = new \Order($this->module->currentOrder);
                    }
                }

                // I create an order if it does not exist with such a transaction id
                $this->createTransaction($address, $customer, $context, $cart, $order);
                break;

            case 'createTransactionById':
                $this->createTransactionById($cart);
                break;

            case 'assignTransactionId':
                $this->assignTransactionId($customer);
                break;
        }
        exit;
    }

    /**
     * @param $cart
     *
     * @return bool
     */
    private function currentCartValidate($cart): bool
    {
        return $cart->id_customer === 0 || $cart->id_address_delivery === 0 || $cart->id_address_invoice === 0;
    }

    /**
     * Create transaction
     *
     * @param $address
     * @param $customer
     * @param $context
     * @param $cart
     *
     * @throws PrestaShopException
     * @throws Exception
     */
    public function createTransaction($address, $customer, $context, $cart, $order)
    {
        $transactionParams = $this->getCustomerData($address, $customer, $context, $cart, $order);
        $transactionParams['pay'] = ['groupId' => Config::GATEWAY_BLIK_0,];

        $isoCode = Language::getLanguage($cart->id_lang)['iso_code'];
        $transactionParams['lang'] = in_array($isoCode, ['pl', 'en']) ? $isoCode : 'en';

        $transaction = $this->module->api->Transactions->createTransaction($transactionParams);

        $this->ajaxDie(
            json_encode(
                ['transaction' => $transaction]
            )
        );
    }

    /**
     * Create transaction by ID transaction
     *
     * @param $cart
     *
     * @throws PrestaShopException
     * @throws Exception
     */
    public function createTransactionById($cart)
    {
        $blikCode = Tools::getValue('blikCode');
        $transactionId = Tools::getValue('transactionId');

        $blikPaymentFields = [
            'groupId'         => Config::GATEWAY_BLIK_0,
            'method'          => 'transfer',
            'blikPaymentData' => [
                'type'    => 0,
                'aliases' => [
                    'value' => $this->getBlikAlias($cart, $blikCode),
                    'type'  => 'UID',
                    'label' => Cfg::get('PS_SHOP_NAME'),
                ],
            ],
        ];

        if (!empty($blikCode)) {
            $blikPaymentFields['blikPaymentData']['blikToken'] = $this->validateBlikCode($blikCode);
        }

        // Create transaction by ID
        $transaction = $this->module->api->Transactions->createPaymentByTransactionId(
            $blikPaymentFields,
            $transactionId
        );


        $transactionRepository = $this->module->getService('tpay.repository.transaction');
        $transactionExists = $transactionRepository->getTransactionByTransactionId($transactionId);

        if (!$transactionExists) {
            $this->createTransactionInDb($transaction, 0, false);
        }

        $this->ajaxDie(
            json_encode(
                [
                    'response'    => $transaction,
                    'transaction' => $this->getTransactionInfo($transactionId),
                    'backUrl'     => $this->module->getContext()->link->getModuleLink(
                        'tpay',
                        'blik'
                    ),
                ]
            )
        );
    }

    /**
     * @param $customer
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function assignTransactionId($customer)
    {
        $transactionId = Tools::getValue('transactionId');
        $cartId = Tools::getValue('cartId');
        $cart = new \Cart($cartId);

        $transactionRepository = $this->module->getService('tpay.repository.transaction');
        $orderId = null;

        // If repay
        $order = new \Order(\Order::getIdByCartId($cart->id));
        if ($order->id) {
            $orderId = $order->id;
        }

        $transactionRepository->setTransactionOrderId($transactionId, $orderId);
        $transaction = $transactionRepository->getTransactionByTransactionId($transactionId);

        $paymentResponse =  $this->getTransactionInfo($transactionId);

        if ($transaction['order_id'] !== 0) {
            $this->ajaxDie(
                json_encode([
                    'status' => $transaction['status'],
                    'backUrl' => $this->module->getContext()->link->getModuleLink(
                        'tpay',
                        'blik',
                        [
                            'orderId' => $transaction['order_id'],
                            'status' => $transaction['status'],
                        ]
                    ),
                    'orderId' => $transaction['order_id'],
                    'transactionId' => $transactionId,
                    'payments' => $paymentResponse['payments']
                ])
            );
        }
    }


    /**
     * Process of saving the transaction
     *
     * @param $transaction
     * @param $orderId
     * @param boolean     $redirect
     *
     * @throws Exception
     */
    public function createTransactionInDb($transaction, $orderId, bool $redirect = true): void
    {
        if (isset($transaction['transactionId'])) {
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
     * Get customer data
     *
     * @param $address
     * @param $customer
     * @param $context
     *
     * @throws Exception
     * @return array
     */
    public function getCustomerData($address, $customer, $context, $cart, $order): array
    {
        $customerData = new CustomerData($address, $customer, $context, $cart, $order);
        $customerData->createBlikCallbacks(self::TYPE);
        $customerData->createDescription();

        return $customerData->getData();
    }


    /**
     * Validate order
     *
     * @param $cartId
     * @param $orderTotal
     * @param $customer
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
     * @param $cart
     *
     * @throws Exception
     * @return float
     */
    private function getOrderTotal($cart): float
    {
        $surcharge = new SurchargeService();
        $orderTotal = $cart->getOrderTotal();
        $surchargeTotal = $surcharge->getSurchargeValue($orderTotal);
        return (float) $orderTotal + $surchargeTotal;
    }

    /**
     * @param $blikCode
     *
     * @return false|string
     */
    public function validateBlikCode($blikCode)
    {
        if (preg_match('/[^a-z_\-0-9 ]/i', $blikCode) && Tools::strlen($blikCode) !== 6) {
            return false;
        }

        return (string) $blikCode;
    }


    /**
     * @throws Exception
     */
    private function getBlikAlias($cart, $blikCode = '')
    {
        // Use new blik token
        $userAlias = 'user_' . $cart->id_customer . '_' . Helper::generateRandomString(5);

        // Use alias
        if (empty($blikCode)) {
            $userAlias = $this->module->getService("tpay.repository.blik")->getBlikAliasIdByUserId(
                $cart->id_customer
            );
        }

        return $userAlias;
    }


    private function getTransactionInfo($transactionId)
    {
        return $this->module->api->Transactions->getTransactionById(
            $transactionId
        );
    }
}
