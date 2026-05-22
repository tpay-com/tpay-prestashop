<?php

/**MIT License

Copyright (c) 2026 Krajowy Integrator Płatności S.A.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.*/

declare(strict_types=1);

namespace Tpay\Hook;

use Cart;
use Currency;
use Exception;
use Order as PrestaOrder;
use PrestaShopException;
use Tools;
use Tpay\OpenApi\Utilities\Util;

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
     * @throws Exception
     */
    public function displayOrderDetail($params): string
    {
        return $this->getSurchargeSmartyTemplate($params);
    }

    /**
     * Hook display order confirmation.
     *
     * @throws Exception
     */
    public function displayOrderConfirmation($params): string
    {
        return $this->getSurchargeSmartyTemplate($params);
    }

    /**
     * @throws PrestaShopException
     * @throws Exception
     */
    public function actionValidateOrder($params)
    {
        if ('tpay' !== $params['order']->module) {
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
     *
     * @throws PrestaShopException
     */
    public function addSurchargeToOrderCreated($surchargeValue, $order, $cart)
    {
        if (!$order instanceof PrestaOrder) {
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

    /**
     * @throws Exception
     */
    private function getSurchargeSmartyTemplate($params = []): string
    {
        if (!isset($params['order']) || 'tpay' !== $params['order']->module) {
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
                        'surcharge_title' => $this->module->getTranslator()->trans('Online payment fee', [], 'Modules.Tpay.Shop'),
                        'surcharge_cost' => Util::numberFormat($surchargeValue) . ' ' . $currency->getSign(),
                    ]
                );

                return $this->module->fetch('module:tpay/views/templates/hook/orderConfirmationSurcharge.tpl');
            }
        }

        return '';
    }
}
