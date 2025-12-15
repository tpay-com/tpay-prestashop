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

use Configuration as Cfg;
use Currency;
use Exception;
use Order;
use OrderHistory;
use PrestaShopBundle\Translation\TranslatorComponent;
use Tools;
use Tpay;

class Admin extends AbstractHook
{
    const AVAILABLE_HOOKS = [
        'displayAdminOrderMainBottom',
        'displayAdminOrder',
    ];

    private static $refundsRendered = false;

    /**
     * @var null|TranslatorComponent 
     */
    private $translator;

    public function __construct(Tpay $module)
    {
        $this->translator = $module->getTranslator();
        parent::__construct($module);
    }

    /**
     * Returns on the order page in the administration
     *
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

        $orderId = (int) $params['id_order'];
        $order = new Order($orderId);
        $orderPayments = $order->getOrderPayments()[0] ?? false;
        $refundSubmit = (bool) Tools::getValue('tpay-refund');
        $errors = [];
        if ($orderPayments && 'Tpay' === $orderPayments->payment_method) {
            $this->getOrderRefunds($orderId);

            $transactionId = $orderPayments->transaction_id;
            $refundAmount = $this->parseRefundAmount(Tools::getValue('tpay_refund_amount'));
            $maxRefundAmount = (float) $orderPayments->amount;
            if ($refundSubmit) {
                if ($this->validRefundAllowedAmount($refundAmount, $maxRefundAmount)) {
                    $errors = sprintf(
                        $this->translator->trans('Unable to process refund - amount is greater than allowed %s', [], 'Modules.Tpay.Admin'),
                        $maxRefundAmount
                    );
                }
                if ($this->validRefundMinAmount($refundAmount)) {
                    $errors = $this->translator->trans('Unable to process refund - invalid amount', [], 'Modules.Tpay.Admin');
                }

                if (empty($errors)) {
                    try {
                        $result = $this->processRefund($transactionId, (float) $refundAmount);
                        if (
                            isset($result['result'])
                            && 'success' === $result['result']
                            && 'correct' === $result['status']
                        ) {
                            $refunds = $this->module->getService('tpay.repository.refund');
                            $refunds->insertRefund(
                                $orderId,
                                $transactionId,
                                $refundAmount
                            );

                            $this->createHistory($order, new OrderHistory());

                            $this->context->smarty->assign(
                                [
                                'tpay_refund_status' => $this->module->displayConfirmation(
                                    $this->translator->trans('Refund successful. Return option is being processed please wait.', [], 'Modules.Tpay.Admin')
                                ),
                                ]
                            );
                        }

                        if (isset($result['result']) && 'failed' === $result['result']) {
                            $errorMessage = $this->getRefundErrorMessage($result['errors'] ?? []);

                            if (null !== $errorMessage) {
                                $this->context->smarty->assign(
                                    [
                                    'tpay_refund_status' => $this->module->displayError($errorMessage),
                                    ]
                                );
                            }
                        }
                    } catch (Exception $TException) {
                        $this->context->smarty->assign(
                            [
                            'tpay_refund_status' => $this->module->displayError($TException->getMessage()),
                            ]
                        );
                    }
                }
            }

            if (!empty($errors)) {
                $this->context->smarty->assign(
                    [
                    'tpay_refund_status' => $this->module->displayError($errors),
                    ]
                );
            }
            $view = 'module:tpay/views/templates/hook/refunds.tpl';
            if ($legacyTheme) {
                $view = 'module:tpay/views/templates/hook/refundsLegacy.tpl';
            }

            return $this->module->fetch($view);
        }
    }

    public function displayAdminOrder($params): string
    {
        $orderId = $params['id_order'];
        $order = new Order($orderId);

        if ('tpay' !== $order->module) {
            return '';
        }

        $currency = new Currency($order->id_currency);
        $surchargeService = $this->module->getService('tpay.service.surcharge');
        $transactionService = $this->module->getService('tpay.repository.transaction');

        if ($surchargeService->hasOrderSurcharge($transactionService, $orderId)) {
            $surchargeValue = $surchargeService->getOrderSurcharge($transactionService, $orderId);
            if ($surchargeValue > 0.00) {
                $this->context->smarty->assign(
                    [
                        'surcharge_title' => $this->translator->trans('Online payment fee', [], 'Modules.Tpay.Admin'),
                        'surcharge_cost' => $surchargeValue,
                        'currency' => $currency,
                        'order' => $order,
                    ]
                );
            }
        }
        $content = $this->module->fetch('module:tpay/views/templates/_admin/orderView.tpl');

        // there is no displayAdminOrderMainBottom hook
        if (version_compare(_PS_VERSION_, '1.7.7.0', '<')) {
            $content .= $this->displayAdminOrderMainBottom($params, true);
        }

        return $content;
    }

    private function getRefundErrorMessage(array $errors)
    {
        $errorMessages = $this->getRefundErrorCodeMessages();

        foreach ($errors as $error) {
            if (!isset($error['errorCode'])) {
                continue;
            }

            $code = $error['errorCode'];

            if (isset($errorMessages[$code])) {
                return $errorMessages[$code];
            }
        }

        return $this->translator->trans(
            'Refund error.
                                   Check that the refund amount is correct and does not exceed the value of the order',
            [],
            'Modules.Tpay.Admin'
        );
    }

    private function getRefundErrorCodeMessages()
    {
        return [
            'transaction_does_not_exist' => $this->translator->trans(
                'Refund error. Provided transaction id does not exist, is not available or the transaction has been paid',
                [],
                'Modules.Tpay.Admin'
            ),
            'refund_period_expired' => $this->translator->trans(
                'Refund error. Refund period for this transaction has expired',
                [],
                'Modules.Tpay.Admin'
            ),
            'cannot_refund_marketplace_transaction' => $this->translator->trans(
                'Refund error. You cannot refund marketplace transaction',
                [],
                'Modules.Tpay.Admin'
            ),
            'cannot_refund_collect_transaction' => $this->translator->trans(
                'Refund error. You cannot refund collect transaction',
                [],
                'Modules.Tpay.Admin'
            ),
            'cannot_create_refund' => $this->translator->trans(
                'Refund error. You can not make a refund for a transaction that has already had a refund request within the last 60 seconds',
                [],
                'Modules.Tpay.Admin'
            ),
            'already_refunded' => $this->translator->trans(
                'Refund error. You cannot refund transaction with status refunded',
                [],
                'Modules.Tpay.Admin'
            ),
            'incorrect_precision' => $this->translator->trans(
                'Refund error. Amount Value is outside of declared precision',
                [],
                'Modules.Tpay.Admin'
            ),
        ];
    }

    private function createHistory($order, OrderHistory $orderHistory)
    {
        $orderHistory->id_order = (int) $order->id;
        $orderHistory->changeIdOrderState(Cfg::get('PS_OS_REFUND'), (int) $order->id);
        $orderHistory->addWithemail(true, []);
    }

    private function parseRefundAmount($amount)
    {
        return number_format(
            (float) str_replace([',', ' '], ['.', ''], (string) $amount),
            2,
            '.',
            ''
        );
    }

    /**
     * Validate refund amount
     */
    private function validRefundAmount($refundAmount, $maxRefundAmount): string
    {
        $error = '';

        if ($this->validRefundAllowedAmount($refundAmount, $maxRefundAmount)) {
            $error = sprintf($this->translator->trans('amount is greater than allowed %s', [], 'Modules.Tpay.Admin'), $maxRefundAmount);
        }
        if ($this->validRefundMinAmount($refundAmount)) {
            $error = $this->translator->trans('invalid amount', [], 'Modules.Tpay.Admin');
        }

        return $error;
    }

    private function validRefundMinAmount($refundAmount): bool
    {
        return (float) $refundAmount <= 0;
    }

    private function validRefundAllowedAmount($refundAmount, $maxRefundAmount): bool
    {
        return $refundAmount > $maxRefundAmount;
    }

    /**
     * Processing refund 
     */
    private function processRefund(string $transactionId, float $refundAmount)
    {
        return $this->module->api()->transactions()->createRefundByTransactionId(
            ['amount' => $refundAmount],
            $transactionId
        );
    }

    /**
     * Show refunds in order
     *
     * @throws Exception
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
        $this->context->smarty->assign(
            [
            'tpayRefunds' => $smartyRefunds,
            ]
        );
    }
}
