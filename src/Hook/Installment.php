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

use Cart;
use Tpay\Config\Config;
use Tpay\Util\Helper;

class Installment extends AbstractHook
{
    public const AVAILABLE_HOOKS = [
        'displayShoppingCart',
        'displayCheckoutSummaryTop',
    ];

    public function displayShoppingCart($params)
    {
        if (Helper::getMultistoreConfigurationValue('TPAY_PEKAO_INSTALLMENTS_ACTIVE') && Helper::getMultistoreConfigurationValue('TPAY_PEKAO_INSTALLMENTS_SHOPPING_CART')) {
            $this->context->smarty->assign(
                [
                    'installmentText' => $this->module->getTranslator()->trans('Calculate installment!', [], 'Modules.Tpay.Shop'),
                    'checkout_url' => $this->context->link->getPageLink('order'),
                    'merchantId' => Helper::getMultistoreConfigurationValue('TPAY_MERCHANT_ID'),
                    'minAmount' => Config::PEKAO_INSTALLMENT_MIN,
                    'maxAmount' => Config::PEKAO_INSTALLMENT_MAX,
                ]
            );

            return $this->module->fetch('module:tpay/views/templates/hook/cart_installment.tpl');
        }

        return '';
    }

    public function displayCheckoutSummaryTop($params)
    {
        if (Helper::getMultistoreConfigurationValue('TPAY_PEKAO_INSTALLMENTS_ACTIVE') && Helper::getMultistoreConfigurationValue('TPAY_PEKAO_INSTALLMENTS_CHECKOUT')) {
            $cart = $params['cart'];
            $totalAmount = $cart->getOrderTotal(true, Cart::BOTH);

            if ($totalAmount >= Config::PEKAO_INSTALLMENT_MIN && $totalAmount <= Config::PEKAO_INSTALLMENT_MAX) {
                $this->context->smarty->assign(
                    [
                        'installmentText' => $this->module->getTranslator()->trans('Calculate installment!', [], 'Modules.Tpay.Shop'),
                        'merchantId' => Helper::getMultistoreConfigurationValue('TPAY_MERCHANT_ID'),
                        'amount' => $totalAmount,
                    ]
                );

                return $this->module->fetch('module:tpay/views/templates/hook/checkout_installments.tpl');
            }
        }

        return '';
    }
}
