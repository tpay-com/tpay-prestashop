<?php


use Configuration as Cfg;
use Tpay\Exception\NotificationHandlingException;
use Tpay\OpenApi\Utilities\CacheCertificateProvider;
use Tpay\OpenApi\Utilities\TpayException;
use Tpay\OpenApi\Webhook\JWSVerifiedPaymentNotification;
use Tpay\Util\PsrCache;

class TpayNotificationsModuleFrontController extends ModuleFrontController
{
    private $statusHandler;

    /** @throws Exception|TpayException */
    public function initContent()
    {
        if ('POST' !== $_SERVER['REQUEST_METHOD']) {
            $controller = new PageNotFoundControllerCore();
            $controller->run();
            exit;
        }

        if (empty($_POST)) {
            echo 'False - Empty body received';
            $this->badRequestResponse();
        }

        $this->statusHandler = $this->module->getService('tpay.handler.order_status_handler');

        $event = $_POST['event'] ?? false;
        $alias = $_POST['msg_value'] ?? false;

        if (!empty($event) && !empty($alias)) {
            // if blik event process
            $this->blikAliasProcess($event, $alias);
            echo 'TRUE';
        } else {
            // default process transaction
            try {
                $isProduction = true !== (bool) Cfg::get('TPAY_SANDBOX');
                $NotificationWebhook = new JWSVerifiedPaymentNotification(
                    new CacheCertificateProvider(
                        new Tpay\OpenApi\Utilities\Cache(null, new PsrCache())
                    ),
                    html_entity_decode(Cfg::get('TPAY_MERCHANT_SECRET')),
                    $isProduction
                );
                $notification = $NotificationWebhook->getNotification();
                $notificationData = $notification->getNotificationAssociative();

                if (!empty($notificationData)) {
                    $this->notificationTransaction($notification, $notificationData);
                }
                echo 'TRUE';
            } catch (Exception $exception) {
                PrestaShopLogger::addLog($exception->getMessage(), 3);
                echo sprintf('%s - %s', 'FALSE', $exception->getMessage());
                $this->badRequestResponse();
            }
        }

        ob_flush();
        exit();
    }

    public function notificationTransaction($notification, $notificationData): void
    {
        $trStatus = $notificationData['tr_status'];
        $trError = $notificationData['tr_error'];
        $trCrc = $notificationData['tr_crc'];

        // check transaction status
        if (('TRUE' === $trStatus || 'CHARGEBACK' === $trStatus) && 'none' === $trError) {
            $transactionRepository = $this->module->getService('tpay.repository.transaction');
            $transaction = $transactionRepository->getTransactionByCrc($trCrc);
            $crc = $transaction['crc'] ?? '';

            if ($crc !== $trCrc) {
                if ('TRUE' === $trStatus && in_array(Cfg::get('TPAY_CRC_FORM'), ['order_id', 'order_id_and_rest'])) {
                    $transaction = $this->forceSaveTransaction($transactionRepository, $notificationData);
                } else {
                    throw new NotificationHandlingException('CRC mismatch expected from database: '.$crc.'. given: '.$trCrc);
                }
            }

            $this->transactionStatusUpdate(
                $transactionRepository,
                $transaction,
                $trStatus
            );
            // Payment card update token card
            if ($notification->card_token->getValue()) {
                $cardsRepository = $this->module->getService('tpay.repository.credit_card');

                // check if token is empty
                $hasToken = (bool) $cardsRepository->getCreditCardTokenByCardCrc($trCrc);

                if (!$hasToken) {
                    $cardsRepository->updateToken(
                        $trCrc,
                        $notification->card_token->getValue()
                    );
                }
            }
        }
    }

    public function transactionStatusUpdate($transactionRepository, $transaction, $status): void
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

    /**
     * @throws Exception
     */
    public function blikAliasProcess($eventType, $alias): void
    {
        $userId = explode('_', $alias['value'])[1];
        $blikRepository = $this->module->getService('tpay.repository.blik');

        if ('ALIAS_REGISTER' === $eventType) {
            $blikRepository->saveBlikAlias(
                (int) $userId,
                (string) $alias['value']
            );
        } elseif ('ALIAS_UNREGISTER' === $eventType) {
            $blikRepository->removeBlikAlias(
                (int) $userId,
                (string) $alias['value']
            );
        }
    }

    private function setConfirmed($orderId, $transactionId): void
    {
        $this->statusHandler->setOrdersAsConfirmed(
            new Order($orderId),
            $transactionId
        );
    }

    private function forceSaveTransaction($transactionRepository, $notificationData): array
    {
        $orderId = 'order_id' === Cfg::get('TPAY_CRC_FORM') ? $notificationData['tr_crc'] : strstr($notificationData['tr_crc'], '-', true);

        $transactionRepository->processCreateTransaction(
            (int) $orderId,
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
}
