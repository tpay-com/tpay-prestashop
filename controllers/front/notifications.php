<?php
/**
 * @author Krajowy Integrator Płatności S.A.
 * @copyright Krajowy Integrator Płatności S.A.
 * @license MIT
 *
 * Copyright (c) 2026 Krajowy Integrator Płatności S.A.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use Configuration as Cfg;
use Tpay\Exception\NotificationHandlingException;
use Tpay\Handler\OrderStatusHandler;
use Tpay\OpenApi\Model\Objects\NotificationBody\BasicPayment;
use Tpay\OpenApi\Model\Objects\NotificationBody\BlikAliasRegister;
use Tpay\OpenApi\Model\Objects\NotificationBody\BlikAliasUnregister;
use Tpay\OpenApi\Utilities\CacheCertificateProvider;
use Tpay\OpenApi\Utilities\TpayException;
use Tpay\OpenApi\Webhook\JWSVerifiedPaymentNotification;
use Tpay\Repository\BlikRepository;
use Tpay\Repository\CreditCardsRepository;
use Tpay\Repository\TransactionsRepository;
use Tpay\Util\PsrCache;

/**
 * @property Tpay $module
 */
class TpayNotificationsModuleFrontController extends ModuleFrontController
{
    /** @var OrderStatusHandler */
    private $statusHandler;

    public function initContent()
    {
        if ('POST' !== $_SERVER['REQUEST_METHOD']) {
            $this->badRequestResponse();
        }

        try {
            /** @var OrderStatusHandler $statusHandler */
            $statusHandler = $this->module->getService('tpay.handler.order_status_handler');
            $this->statusHandler = $statusHandler;

            $isProduction = true !== (bool) Cfg::get('TPAY_SANDBOX');

            $notificationHandler = new JWSVerifiedPaymentNotification(
                new CacheCertificateProvider(
                    new Tpay\OpenApi\Utilities\Cache(null, new PsrCache())
                ),
                html_entity_decode(Cfg::get('TPAY_MERCHANT_SECRET')),
                $isProduction
            );

            $notification = $notificationHandler->getNotification();

            $this->handleNotification($notification);

            echo 'TRUE';
        } catch (TpayException $e) {
            PrestaShopLogger::addLog($e->getMessage(), 3);
            echo 'FALSE - ' . $e->getMessage();
            $this->badRequestResponse();
        } catch (Exception $e) {
            PrestaShopLogger::addLog($e->getMessage(), 3);
            echo 'FALSE - Internal error';
            $this->badRequestResponse();
        }

        exit;
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function addTestModeNoteToOrder(Order $order): void
    {
        $id_customer_thread = CustomerThread::getIdCustomerThreadByEmailAndIdOrder($order->getCustomer()->email, $order->id);
        if (!$id_customer_thread) {
            $customer_thread = new CustomerThread();
            $customer_thread->id_contact = 0;
            $customer_thread->id_customer = (int) $order->id_customer;
            $customer_thread->id_shop = (int) $this->context->shop->id;
            $customer_thread->id_order = (int) $order->id;
            $customer_thread->id_lang = (int) $this->context->language->id;
            $customer_thread->email = $order->getCustomer()->email;
            $customer_thread->status = 'open';
            $customer_thread->token = Tools::passwdGen(12);
            $customer_thread->add();
        } else {
            $customer_thread = new CustomerThread((int) $id_customer_thread);
        }

        $customer_message = new CustomerMessage();
        $customer_message->id_customer_thread = $customer_thread->id;
        $customer_message->message = 'Odebrano potwierdzenie płatności Tpay w trybie testowym - środki nie zostały pobrane od klienta';
        $customer_message->id_employee = 0;
        /*
         * backward compatibility
         */
        if (CustomerMessageCore::$definition['fields']['private']['type'] == ObjectModel::TYPE_INT) {
            $customer_message->private = 1;
        } else {
            $customer_message->private = true;
        }
        $customer_message->add();
    }

    private function handleNotification($notification): void
    {
        switch (true) {
            case $notification instanceof BasicPayment:
                $this->handleBasicPayment($notification);
                break;
            case $notification instanceof BlikAliasRegister:
                $this->handleBlikRegister($notification);
                break;
            case $notification instanceof BlikAliasUnregister:
                $this->handleBlikUnregister($notification);
                break;
            default:
                throw new TpayException('Unsupported notification type: ' . get_class($notification));
        }
    }

    private function handleBlikRegister($notification): void
    {
        $aliasValue = $notification->value->getValue();
        $userId = explode('_', $aliasValue)[1];

        /** @var BlikRepository $blikRepository */
        $blikRepository = $this->module->getService('tpay.repository.blik');
        $blikRepository->saveBlikAlias((int) $userId, $aliasValue);
    }

    private function handleBlikUnregister($notification): void
    {
        $aliasValue = $notification->value->getValue();
        $userId = explode('_', $aliasValue)[1];

        /** @var BlikRepository $blikRepository */
        $blikRepository = $this->module->getService('tpay.repository.blik');
        $blikRepository->removeBlikAlias((int) $userId, $aliasValue);
    }

    private function handleBasicPayment($notification): void
    {
        /** @var BasicPayment $notification */
        if ($notification->isTestNotification()) {
            PrestaShopLogger::addLog(
                'Odebrano testowe powiadomienie: ' . print_r($notification->getNotificationAssociative(), true)
            );

            return;
        }

        $trStatus = $notification->tr_status->getValue();
        $trError = $notification->tr_error->getValue();
        $trCrc = $notification->tr_crc->getValue();

        if (!in_array($trStatus, ['TRUE', 'CHARGEBACK']) || 'none' !== $trError) {
            return;
        }

        /** @var TransactionsRepository $transactionRepository */
        $transactionRepository = $this->module->getService('tpay.repository.transaction');
        $transaction = $transactionRepository->getTransactionByCrc($trCrc);

        if (!$transaction) {
            if ('TRUE' === $trStatus) {
                $notificationData = $notification->getNotificationAssociative();

                $transaction = $this->forceSaveTransaction(
                    $transactionRepository,
                    $notificationData
                );
            } else {
                throw new NotificationHandlingException('Transaction not found for CRC: ' . $trCrc);
            }
        }

        $order = new Order((int) $transaction['order_id']);

        if (!$this->validateCurrency($order, $notification)) {
            $notificationCurrency = null;

            if ($notification->tr_currency) {
                $notificationCurrency = $notification->tr_currency->getValue();
            }

            PrestaShopLogger::addLog(
                sprintf(
                    'Currency mismatch: order=%s, notification=%s',
                    (new Currency((int) $order->id_currency))->iso_code,
                    var_export($notificationCurrency, true)
                ),
                3
            );

            throw new TpayException('Order currency mismatch');
        }

        if (!$this->validateAmount($order, $notification)) {
            PrestaShopLogger::addLog(
                sprintf(
                    'Niezgodna kwota zamówienia: order=%s, notification=%s',
                    $order->total_paid,
                    $notification->tr_amount->getValue()
                ),
                3
            );

            throw new TpayException('Order amount mismatch');
        }

        if (1 === $notification->test_mode->getValue()) {
            PrestaShopLogger::addLog(
                sprintf(
                    'Powiadomienie Tpay dla zamówienia w trybie testowym %s (%s)',
                    $order->id,
                    $notification->tr_id->getValue()
                ),
                1
            );

            $this->addTestModeNoteToOrder($order);

            return;
        }

        $this->transactionStatusUpdate(
            $transactionRepository,
            $transaction,
            $trStatus
        );

        if ($notification->card_token && $notification->card_token->getValue()) {
            /** @var CreditCardsRepository $cardsRepository */
            $cardsRepository = $this->module->getService('tpay.repository.credit_card');
            $hasToken = (bool) $cardsRepository->getCreditCardTokenByCardCrc($trCrc);

            if (!$hasToken) {
                $cardsRepository->updateToken(
                    $trCrc,
                    $notification->card_token->getValue()
                );
            }
        }
    }

    private function transactionStatusUpdate($transactionRepository, $transaction, $status): void
    {
        try {
            $currentStatus = $transaction['status'] ?? '';

            if ('pending' === $currentStatus) {
                $changeStatus = 'TRUE' === $status ? 'success' : 'error';
                $transactionRepository->updateTransactionStatus($transaction['crc'], $changeStatus);
                $this->setConfirmed($transaction['order_id'], $transaction['transaction_id']);
            }

            // / Charge
            if ('CHARGEBACK' === $status) {
                $sqlTransaction = $transactionRepository->getTransactionByCrc($transaction['crc']);
                $orderId = (int) $sqlTransaction['order_id'];

                $orderHistory = new OrderHistory();
                $orderHistory->id_order = $orderId;
                $orderHistory->changeIdOrderState((int) Cfg::get('PS_OS_REFUND'), $orderId);
                $orderHistory->addWithemail(true, []);
            }
        } catch (Exception $exception) {
            PrestaShopLogger::addLog($exception->getMessage(), 3);
        }
    }

    private function setConfirmed($orderId, $transactionId): void
    {
        $this->statusHandler->setOrdersAsConfirmed(
            new Order($orderId),
            $transactionId
        );
    }

    /**
     * @throws Exception
     */
    private function forceSaveTransaction($transactionRepository, array $notificationData): array
    {
        $crcForm = Cfg::get('TPAY_CRC_FORM');

        if ('order_id' === $crcForm) {
            $orderId = (int) $notificationData['tr_crc'];
        } elseif ('order_id_and_rest' === $crcForm) {
            $orderId = (int) strstr($notificationData['tr_crc'], '-', true);
        } else {
            throw new NotificationHandlingException('CRC mismatch and recovery disabled. CRC: ' . $notificationData['tr_crc']);
        }

        $transactionRepository->processCreateTransaction(
            $orderId,
            $notificationData['tr_crc'],
            $notificationData['tr_id'],
            'transfer',
            0,
            0,
            'pending'
        );

        return $transactionRepository->getTransactionByCrc($notificationData['tr_crc']);
    }

    private function badRequestResponse(): void
    {
        header('HTTP/1.1 400 Bad Request', true, 400);
        exit;
    }

    private function validateAmount(Order $order, BasicPayment $notification): bool
    {
        $orderAmount = number_format((float) $order->total_paid, 2, '.', '');
        $notificationAmount = number_format((float) $notification->tr_amount->getValue(), 2, '.', '');

        return $orderAmount === $notificationAmount;
    }

    private function validateCurrency(Order $order, BasicPayment $notification): bool
    {
        $value = null;

        if (isset($notification->tr_currency)) {
            $value = $notification->tr_currency->getValue();
        }

        if (!is_string($value) || '' === trim($value)) {
            return true;
        }

        $notificationCurrency = strtoupper(trim($value));

        $currency = new Currency((int) $order->id_currency);
        $orderCurrency = $currency->iso_code;

        return strtoupper(trim($orderCurrency)) === $notificationCurrency;
    }
}
