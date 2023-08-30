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
use Order as PrestaOrder;
use Currency;
use Exception;
use PrestaShopException;
use tpaySDK\Utilities\Util;

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
     * @return string
     * @throws Exception
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
     * @return string
     * @throws Exception
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
        $order = new PrestaOrder($orderId);
        $currency = new Currency($order->id_currency);
        $surchargeService = $this->module->getService('tpay.service.surcharge');
        $transactionService = $this->module->getService('tpay.repository.transaction');

        if ($surchargeService->hasOrderSurcharge($transactionService, $orderId)) {
            $surchargeValue = $surchargeService->getOrderSurcharge($transactionService, $orderId);
            if ($surchargeValue > 0.00) {
                $this->context->smarty->assign(
                    [
                        'surcharge_title' => $this->module->l('Online payment fee'),
                        'surcharge_cost' => Util::numberFormat($surchargeValue) . ' ' . $currency->getSign(),
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
        $computePresicion = 2;
        if (method_exists($this->context, 'getComputingPrecision')) {
            $computePresicion = $this->context->getComputingPrecision();
        } elseif (defined(_PS_PRICE_COMPUTE_PRECISION_) && _PS_PRICE_COMPUTE_PRECISION_ !== null) {
            $computePresicion = _PS_PRICE_COMPUTE_PRECISION_;
        }

        $amountWithTax = $cart->getOrderTotal(true, Cart::BOTH);
        $amountWithoutTax = $cart->getOrderTotal(false, Cart::BOTH);

        $order->total_paid_tax_excl = Tools::ps_round(
            $amountWithoutTax + $surchargeValue,
            $computePresicion
        );
        $order->total_paid_tax_incl = Tools::ps_round(
            $amountWithTax + $surchargeValue,
            $computePresicion
        );

        $order->total_paid = Tools::ps_round(
            $order->total_paid + $surchargeValue,
            $computePresicion
        );

        $order->total_paid_real = Tools::ps_round(
            $order->total_paid_real + $surchargeValue,
            $computePresicion
        );

        $order->update();
    }
}
