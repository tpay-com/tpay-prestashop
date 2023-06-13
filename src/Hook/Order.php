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

use Cart;
use Tools;
use Exception;
use PrestaShopException;

class Order extends AbstractHook
{
    public const AVAILABLE_HOOKS = [
        'displayOrderDetail',
        'displayOrderConfirmation',
        'actionValidateOrder',
    ];

    /**
     * Hook display order detail.
     *
     * @param $params
     *
     * @throws Exception
     * @return string
     */
    public function displayOrderDetail($params): string
    {
        return $this->getSurchargeSmartyTemplate($params);
    }

    /**
     * Hook display order confirmation.
     *
     * @param $params
     *
     * @throws Exception
     * @return string
     */
    public function displayOrderConfirmation($params): string
    {
        return $this->getSurchargeSmartyTemplate($params);
    }

    /**
     * @throws Exception
     */
    private function getSurchargeSmartyTemplate($params = []): string
    {
        if (!isset($params['order']) || $params['order']->module !== 'tpay') {
            return '';
        }

        $orderId = $params['order']->id;
        $surchargeService = $this->module->getService('tpay.service.surcharge');
        $transactionService = $this->module->getService('tpay.repository.transaction');

        if ($surchargeService->hasOrderSurcharge($transactionService, $orderId)) {
            $surchargeValue = $surchargeService->getOrderSurcharge($transactionService, $orderId);
            if ($surchargeValue > 0.00) {
                $this->context->smarty->assign(
                    [
                        'surcharge_title' => $this->module->l('Online payment fee'),
                        'surcharge_cost' => $surchargeValue . ' ' . $this->module->getContext()->currency->symbol
                    ]
                );
                return $this->module->fetch('module:tpay/views/templates/hook/orderConfirmationSurcharge.tpl');
            }
        }

        return '';
    }

    /**
     * @throws PrestaShopException
     * @throws Exception
     */
    public function actionValidateOrder($params)
    {
        if ($params['order']->module !== 'tpay') {
            return;
        }

        if (isset($params['order'], $params['order']->id)) {
            $order = $params['order'];
            $cart = Cart::getCartByOrderId($order->id);

            $surchargeService = $this->module->getService('tpay.service.surcharge');
            $surchargeValue = $surchargeService->getSurchargeValue();

            if ($surchargeValue > 0.00) {
                $this->addSurchargeToOrderCreated(
                    $surchargeValue,
                    $order,
                    $cart
                );
            }
        }
    }

    /**
     * Add a surcharge to a created order
     * @throws PrestaShopException
     */
    public function addSurchargeToOrderCreated($surchargeValue, $order, $cart)
    {
        if (!$order instanceof \Order) {
            return;
        }

        $amountWithTax = $cart->getOrderTotal(true, Cart::BOTH);
        $amountWithoutTax = $cart->getOrderTotal(false, Cart::BOTH);

        $order->total_paid_tax_excl = Tools::ps_round(
            $amountWithTax + $surchargeValue,
            $this->context->getComputingPrecision()
        );
        $order->total_paid_tax_incl = Tools::ps_round(
            $amountWithoutTax + $surchargeValue,
            $this->context->getComputingPrecision()
        );

        $order->total_paid = Tools::ps_round(
            $order->total_paid + $surchargeValue,
            $this->context->getComputingPrecision()
        );

        $order->total_paid_real = Tools::ps_round(
            $order->total_paid_real + $surchargeValue,
            $this->context->getComputingPrecision()
        );

        $order->update();
    }
}
