<?php

use Configuration as Cfg;
use Tpay\Exception\NotificationHandlingException;
use Tpay\OpenApi\Model\Objects\NotificationBody\BasicPayment;
use Tpay\OpenApi\Model\Objects\NotificationBody\BlikAliasRegister;
use Tpay\OpenApi\Model\Objects\NotificationBody\BlikAliasUnregister;
use Tpay\OpenApi\Utilities\CacheCertificateProvider;
use Tpay\OpenApi\Utilities\TpayException;
use Tpay\OpenApi\Webhook\JWSVerifiedPaymentNotification;
use Tpay\Util\PsrCache;

class TpayNotificationsModuleFrontController extends ModuleFrontController
{
    private $statusHandler;

    public function initContent()
    {
        if ('POST' !== $_SERVER['REQUEST_METHOD']) {
            $this->badRequestResponse();
        }

        try {
            $this->statusHandler = $this->module->getService('tpay.handler.order_status_handler');

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
            echo 'FALSE - '.$e->getMessage();
            $this->badRequestResponse();
        } catch (Exception $e) {
            PrestaShopLogger::addLog($e->getMessage(), 3);
            echo 'FALSE - Internal error';
            $this->badRequestResponse();
        }

        exit;
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
                throw new TpayException('Unsupported notification type: '.get_class($notification));
        }
    }

    private function handleBlikRegister($notification): void
    {
        $aliasValue = $notification->value->getValue();
        $userId = explode('_', $aliasValue)[1];

        $blikRepository = $this->module->getService('tpay.repository.blik');
        $blikRepository->saveBlikAlias((int) $userId, $aliasValue);
    }

    private function handleBlikUnregister($notification): void
    {
        $aliasValue = $notification->value->getValue();
        $userId = explode('_', $aliasValue)[1];

        $blikRepository = $this->module->getService('tpay.repository.blik');
        $blikRepository->removeBlikAlias((int) $userId, $aliasValue);
    }

    private function handleBasicPayment($notification): void
    {
        if ($notification->isTestNotification()) {
            PrestaShopLogger::addLog(
                'Odebrano testowe powiadomienie: '.print_r($notification->getNotificationAssociative(), 1)
            );

            return;
        }

        $trStatus = $notification->tr_status->getValue();
        $trError = $notification->tr_error->getValue();
        $trCrc = $notification->tr_crc->getValue();

        if (!in_array($trStatus, ['TRUE', 'CHARGEBACK']) || 'none' !== $trError) {
            return;
        }

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
                throw new NotificationHandlingException(
                    'Transaction not found for CRC: '.$trCrc
                );
            }
        }

        $order = new Order((int) $transaction['order_id']);
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

        $this->transactionStatusUpdate(
            $transactionRepository,
            $transaction,
            $trStatus
        );

        if ($notification->card_token && $notification->card_token->getValue()) {
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

            /// Charge
            if ('CHARGEBACK' === $status) {
                $sqlTransaction = $transactionRepository->getTransactionByCrc($transaction['crc']);
                $orderId = (int) $sqlTransaction['order_id'];

                $orderHistory = new OrderHistory();
                $orderHistory->id_order = $orderId;
                $orderHistory->changeIdOrderState(Cfg::get('PS_OS_REFUND'), $orderId);
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
            throw new NotificationHandlingException(
                'CRC mismatch and recovery disabled. CRC: '.$notificationData['tr_crc']
            );
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
}
