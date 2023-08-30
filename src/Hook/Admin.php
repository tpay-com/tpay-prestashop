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

declare(strict_types=1);

namespace Tpay\Hook;

use Order;
use Currency;
use Tools;
use Configuration as Cfg;

class Admin extends AbstractHook
{
    const AVAILABLE_HOOKS = [
        'displayAdminOrderMainBottom',
        'displayAdminOrder'
    ];
    private static $refundsRendered = false;


    /**
     * Returns on the order page in the administration
     * @param array $params
     * @return string|void
     */
    public function displayAdminOrderMainBottom(array $params, $legacyTheme = false)
    {
        if (!$this->module->active || null === $this->module->api) {
            return;
        }
        if (true === self::$refundsRendered) {
            return;
        }
        self::$refundsRendered = true;

        $orderId = (int)$params['id_order'];
        $order = new Order($orderId);
        $orderPayments = $order->getOrderPayments()[0] ?? false;
        $refundSubmit = (bool)Tools::getValue('tpay-refund');
        $errors = [];
        if ($orderPayments && $orderPayments->payment_method === 'Tpay') {
            $this->getOrderRefunds($orderId);

            $transactionId = $orderPayments->transaction_id;
            $refundAmount = $this->parseRefundAmount(Tools::getValue('tpay_refund_amount'));
            $maxRefundAmount = (float)$orderPayments->amount;
            if ($refundSubmit) {
                if ($this->validRefundAllowedAmount($refundAmount, $maxRefundAmount)) {
                    $errors = sprintf(
                        $this->module->l('Unable to process refund - amount is greater than allowed %s'),
                        $maxRefundAmount
                    );
                }
                if ($this->validRefundMinAmount($refundAmount)) {
                    $errors = $this->module->l('Unable to process refund - invalid amount');
                }

                if (empty($errors)) {
                    try {
                        $result = $this->processRefund($transactionId, (float)$refundAmount);
                        if (
                            isset($result['result']) &&
                            $result['result'] === 'success' &&
                            $result['status'] === 'correct'
                        ) {
                            $refunds = $this->module->getService('tpay.repository.refund');
                            $refunds->insertRefund(
                                $orderId,
                                $transactionId,
                                $refundAmount
                            );

                            $this->createHistory($order, new \OrderHistory());

                            $this->context->smarty->assign([
                                'tpay_refund_status' => $this->module->displayConfirmation(
                                    $this->module->l('Refund successful. Return option is being processed please wait.')
                                ),
                            ]);
                        }

                        if (isset($result['result']) && $result['result'] === 'failed') {
                            $this->context->smarty->assign([
                                'tpay_refund_status' => $this->module->displayError(
                                    $this->module->l('Refund error.
                                    Check that the refund amount is correct and does not exceed the value of the order')
                                ),
                            ]);
                        }
                    } catch (\Exception $TException) {
                        $this->context->smarty->assign([
                            'tpay_refund_status' => $this->module->displayError($TException->getMessage()[0]),
                        ]);
                    }
                }
            }

            if (!empty($errors)) {
                $this->context->smarty->assign([
                    'tpay_refund_status' => $this->module->displayError($errors),
                ]);
            }
            $view = 'module:tpay/views/templates/hook/refunds.tpl';
            if($legacyTheme){
                $view = 'module:tpay/views/templates/hook/refundsLegacy.tpl';
            }
            return $this->module->fetch($view);
        }
    }


    private function createHistory($order, \OrderHistory $orderHistory)
    {
        $orderHistory->id_order = (int)$order->id;
        $orderHistory->changeIdOrderState(Cfg::get('PS_OS_REFUND'), (int)$order->id);
        $orderHistory->addWithemail(true, []);
    }


    private function parseRefundAmount($amount)
    {
        return number_format(
            (float)str_replace([',', ' '], ['.', ''], $amount),
            2,
            '.',
            ''
        );
    }


    /**
     * Validate refund amount
     * @param $refundAmount
     * @param $maxRefundAmount
     *
     * @return string
     */
    private function validRefundAmount($refundAmount, $maxRefundAmount): string
    {
        $error = '';

        if ($this->validRefundAllowedAmount($refundAmount, $maxRefundAmount)) {
            $error = sprintf($this->module->l('amount is greater than allowed %s'), $maxRefundAmount);
        }
        if ($this->validRefundMinAmount($refundAmount)) {
            $error = $this->module->l('invalid amount');
        }

        return $error;
    }


    private function validRefundMinAmount($refundAmount): bool
    {
        return (float)$refundAmount <= 0;
    }

    private function validRefundAllowedAmount($refundAmount, $maxRefundAmount): bool
    {
        return $refundAmount > $maxRefundAmount;
    }


    /**
     * Processing refund
     *
     * @param string $transactionId
     * @param float $refundAmount
     *
     * @return mixed
     */
    private function processRefund(string $transactionId, float $refundAmount)
    {
        return $this->module->api->Transactions->createRefundByTransactionId(
            ['amount' => $refundAmount],
            $transactionId
        );
    }

    /**
     * Show refunds in order
     *
     * @param int $orderId
     *
     * @throws \Exception
     */
    private function getOrderRefunds(int $orderId)
    {
        $refunds = $this->module->getService('tpay.repository.refund');
        $orderRefunds = $refunds->getOrderRefunds($orderId);
        $smartyRefunds = [];
        foreach ($orderRefunds as $refund) {
            $smartyRefunds[] = [
                'tpay_refund_date' => $refund['date'],
                'tpay_transaction_id' => $refund['transaction_id'],
                'tpay_refund_amount' => $refund['amount'],
            ];
        }
        $this->context->smarty->assign([
            'tpayRefunds' => $smartyRefunds,
        ]);
    }


    public function displayAdminOrder($params): string
    {
        if ($this->module->name != 'tpay') {
            return '';
        }

        $orderId = $params['id_order'];
        $order = new Order($orderId);
        $currency = new Currency($order->id_currency);
        $surchargeService = $this->module->getService('tpay.service.surcharge');
        $transactionService = $this->module->getService('tpay.repository.transaction');

        if ($surchargeService->hasOrderSurcharge($transactionService, $orderId)) {
            $surchargeValue = $surchargeService->getOrderSurcharge($transactionService, $orderId);
            if ($surchargeValue > 0.00) {
                $this->context->smarty->assign(
                    [
                        'surcharge_title' => $this->module->l('Online payment fee'),
                        'surcharge_cost' => $surchargeValue,
                        'currency' => $currency,
                        'order'=>$order
                    ]
                );
            }
        }
        $content = $this->module->fetch('module:tpay/views/templates/_admin/orderView.tpl');

        //there is no displayAdminOrderMainBottom hook
        if (version_compare(_PS_VERSION_, '1.7.7.0', '<')) {
            $content .= $this->displayAdminOrderMainBottom($params, true);
        }
        return $content;
    }

}
