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

declare(strict_types=1);

namespace Tpay\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException;
use Tpay\Repository\TransactionsRepository;
use Tpay\Service\SurchargeService;

class EmailOrder extends AbstractHook
{
    public const AVAILABLE_HOOKS = [
        'actionEmailAddAfterContent',
        'displayInvoiceLegalFreeText',
        'displayPDFInvoice',
    ];

    /**
     * @throws LocalizationException
     * @throws \Exception
     */
    public function actionEmailAddAfterContent($params)
    {
        $cart = $this->context->cart;
        $order = $cart ? \Order::getByCartId($cart->id) : null;

        if (!$this->module->active
            || !$order
            || ($order->module !== $this->module->name)
            || !empty($this->context->controller->errors)
            || (substr_count($params['template'], 'error') > 0)
        ) {
            return $params;
        }

        $search = false !== strpos(
            $params['template_html'],
            '<tr class="conf_body">'
        ) ? '<tr class="conf_body">' : '<tr class="order_summary">';

        $emailHeader = strstr($params['template_html'], $search, true);
        $emailFooter = strstr($params['template_html'], $search);

        if ($emailHeader) {
            $total = $cart->getOrderTotal();
            $total += $this->getSurchargeCost();
            $total = $this->context->getCurrentLocale()->formatPrice(
                $total,
                $this->context->currency->iso_code
            );

            $emailFooter = str_replace('{total_paid}', $total, (string) $emailFooter);
            $params['template_html'] = $emailHeader . $this->renderExtraChargeDataInMail(
                $this->getSurchargeCost()
            ) . $emailFooter;
        }

        return $params;
    }

    /**
     * @throws LocalizationException
     * @throws \Exception
     */
    public function displayPDFInvoice($params): string
    {
        if (!isset($params['object'])) {
            return '';
        }
        if (!$params['object'] instanceof \OrderInvoice) {
            return '';
        }

        $surchargeValue = $this->getOrderSurchargeCost($params['object']->id_order);

        if ($surchargeValue > 0.00) {
            $this->context->smarty->assign(
                [
                    'surchargeCost' => \Context::getContext()->getCurrentLocale()->formatPrice(
                        $surchargeValue,
                        $this->context->currency->iso_code
                    ),
                ]
            );

            return $this->module->fetch('module:tpay/views/templates/admin/invoiceSurcharge.tpl');
        }

        return '';
    }

    private function renderExtraChargeDataInMail($surchargeCost): string
    {
        if (0.00 === $surchargeCost) {
            return '';
        }

        $this->context->smarty->assign(
            [
                'surchargeCost' => \Context::getContext()->getCurrentLocale()->formatPrice(
                    $surchargeCost,
                    $this->context->currency->iso_code
                ),
            ]
        );

        return $this->module->fetch('module:tpay/views/templates/hook/emailSurcharge.tpl');
    }

    /** @throws \Exception */
    private function getSurchargeCost()
    {
        $orderTotal = (float) $this->context->cart->getOrderTotal();
        /** @var SurchargeService $surchargeService */
        $surchargeService = $this->module->getService('tpay.service.surcharge');

        return $surchargeService->getSurchargeValue($orderTotal);
    }

    /**
     * @throws \Exception
     */
    private function getOrderSurchargeCost($orderId): float
    {
        /** @var TransactionsRepository $transactionRepository */
        $transactionRepository = $this->module->getService('tpay.repository.transaction');

        return (float) $transactionRepository->getSurchargeValueByOrderId($orderId);
    }
}
