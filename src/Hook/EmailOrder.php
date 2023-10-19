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

use Exception;
use PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException;
use Tools;

class EmailOrder extends AbstractHook
{
    public const AVAILABLE_HOOKS = [
        'actionEmailAddAfterContent',
        'displayInvoiceLegalFreeText',
        'displayPDFInvoice',
    ];

    /**
     * @throws LocalizationException
     * @throws Exception
     */
    public function actionEmailAddAfterContent($params)
    {
        $cart = $this->context->cart;
        $order = $cart ? \Order::getByCartId($cart->id) : null;

        if (
            !$this->module->active ||
            !$order ||
            ($order->module !== $this->module->name) ||
            !empty($this->context->controller->errors) ||
            (substr_count($params['template'], 'error') > 0)
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
            $total = Tools::displayPrice($total, $this->context->currency);

            $emailFooter = str_replace("{total_paid}", $total, (string)$emailFooter);
            $params['template_html'] = $emailHeader . $this->renderExtraChargeDataInMail(
                $this->getSurchargeCost()
            ) . $emailFooter;
        }

        return $params;
    }

    private function renderExtraChargeDataInMail($surchargeCost): string
    {
        if ($surchargeCost === 0.00) {
            return '';
        }

        $this->context->smarty->assign([
            'surchargeCost' => Tools::displayPrice($surchargeCost, $this->context->currency)
        ]);

        return $this->module->fetch('module:tpay/views/templates/hook/emailSurcharge.tpl');
    }

    /**
     * @throws Exception
     */
    private function getSurchargeCost()
    {
        $orderTotal = (float) $this->context->cart->getOrderTotal();
        $surchargeService = $this->module->getService('tpay.service.surcharge');
        return $surchargeService->getSurchargeValue($orderTotal);
    }

    /**
     * @throws Exception
     */
    private function getOrderSurchargeCost($orderId): float
    {
        $surchargeService = $this->module->getService('tpay.repository.transaction');
        return (float) $surchargeService->getSurchargeValueByOrderId($orderId);
    }

    /**
     * @throws LocalizationException
     * @throws Exception
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
            $this->context->smarty->assign([
                'surchargeCost' => Tools::displayPrice(
                    $surchargeValue,
                    $this->context->currency
                )
            ]);

            return $this->module->fetch('module:tpay/views/templates/_admin/invoiceSurcharge.tpl');
        }

        return '';
    }
}
