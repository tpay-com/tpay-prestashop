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
use Tpay\Service\NotificationService;
use tpaySDK\Utilities\TpayException;
use tpaySDK\Webhook\JWSVerifiedPaymentNotification;

class TpayNotificationsModuleFrontController extends ModuleFrontController
{
    /**
     * @throws TpayException|Exception
     */
    public function initContent()
    {
        if (!$_POST) {
            echo 'FALSE';
        } else {
            $event = $_POST['event'] ?? false;
            $alias = $_POST['msg_value'] ?? false;

            if (!empty($event) && !empty($alias)) {
                // if blik event process
                $this->blikAliasProcess($event, $alias);
                echo 'TRUE';
            } else {
                // default process transaction
                try {
                    $isProduction = (true !== (bool)Cfg::get('TPAY_SANDBOX'));
                    $NotificationWebhook = new JWSVerifiedPaymentNotification(
                        Cfg::get('TPAY_MERCHANT_SECRET'),
                        $isProduction
                    );
                    $notification = $NotificationWebhook->getNotification();
                    $notificationData = $notification->getNotificationAssociative();

                    if (!empty($notificationData)) {
                        $this->notificationTransaction($notification, $notificationData);
                    }
                    echo 'TRUE';
                } catch (\Exception $exception) {
                    \PrestaShopLogger::addLog($exception, 3);
                }
            }
        }

        ob_flush();
        exit();
    }

    public function notificationTransaction($notification, $notificationData): void
    {
        $id = $notificationData['id'];
        $trStatus = $notificationData['tr_status'];
        $trId = $notificationData['tr_id'];
        $trAmount = $notificationData['tr_amount'];
        $trPaid = $notificationData['tr_paid'];
        $trError = $notificationData['tr_error'];
        $trDate = $notificationData['tr_date'];
        $trCrc = $notificationData['tr_crc'];
        $trMd5sum = $notificationData['md5sum'];

        $merchantSecret = Cfg::get('TPAY_MERCHANT_SECRET');

        if (!$merchantSecret) {
            echo 'FALSE';
            \PrestaShopLogger::addLog('No merchant secret', 3);
        }

        // check transaction status
        if (($trStatus === 'TRUE' || $trStatus === 'CHARGEBACK') && $trError === 'none') {
            $loggerTransactionData = ' Transaction id ' . $trId . ' Date ' . $trDate . ' Amount ' . $trAmount .
                ' Paid ' . $trPaid . ' Status ' . $trDate;

            $md5sum = md5($id . $trId . $trAmount . $trCrc . $merchantSecret);
            $transactionRepository = $this->module->get('tpay.repository.transaction');
            $transaction = $transactionRepository->getTransactionByCrc($trCrc);
            $crc = $transaction['crc'] ?? '';

            /*
            Verification of md5sum
            */
            if ($trMd5sum !== $md5sum) {
                \PrestaShopLogger::addLog('Wrong transaction md5 sum', 3);
                \PrestaShopLogger::addLog($loggerTransactionData, 3);
                echo 'FALSE';
                return;
            }

            /*
            Verification transaction by CRC
            */
            if ($crc !== $trCrc) {
                \PrestaShopLogger::addLog('Wrong crc', 3);
                echo 'FALSE';
                return;
            }

            $this->transactionStatusUpdate(
                $transactionRepository,
                $transaction,
                $trStatus
            );

            /*
             Payment card update token card
             */
            if ($notification->card_token->getValue()) {
                $cardsRepository = $this->module->get('tpay.repository.credit_card');

                //check if token is empty
                $hasToken = (bool)$cardsRepository->getCreditCardTokenByCardCrc($trCrc);

                if (!$hasToken) {
                    $cardsRepository->updateToken(
                        $trCrc,
                        $notification->card_token->getValue()
                    );
                }
            }
            echo 'TRUE';
        }
    }

    public function transactionStatusUpdate($transactionRepository, $transaction, $status): void
    {
        try {
            $currentStatus = $transaction['status'] ?? '';

            if ($currentStatus === 'pending') {
                $changeStatus = $status === 'TRUE' ? 'success' : 'error';
                $transactionRepository->updateTransactionStatus($transaction['crc'], $changeStatus);
            }

            /// Charge
            if ($status === 'CHARGEBACK') {
                $sqlTransaction = $transactionRepository->getTransactionByCrc($transaction['crc']);
                $orderId = (int)$sqlTransaction['order_id'];

                $orderHistory = new \OrderHistory();
                $orderHistory->id_order = $orderId;
                $orderHistory->changeIdOrderState(Cfg::get('PS_OS_REFUND'), $orderId);
                $orderHistory->addWithemail(true, []);
            }
        } catch (\Exception $exception) {
            \PrestaShopLogger::addLog($exception, 3);
        }
    }

    /**
     * @throws Exception
     */
    public function blikAliasProcess($eventType, $alias): void
    {
        $userId = explode('_', $alias['value'])[1];
        $blikRepository = $this->module->get('tpay.repository.blik');

        if ($eventType === 'ALIAS_REGISTER') {
            $blikRepository->saveBlikAlias(
                (int)$userId,
                (string)$alias['value']
            );
        } elseif ($eventType === 'ALIAS_UNREGISTER') {
            $blikRepository->removeBlikAlias(
                (int)$userId,
                (string)$alias['value']
            );
        }
    }
}
