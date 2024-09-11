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
use Tpay\Config\Config;
use Tpay\Util\Helper;

class Installment extends AbstractHook
{
    public const AVAILABLE_HOOKS = [
        'displayShoppingCart',
        'displayPaymentTop',
        'displayProductPriceBlock'
    ];

    public function hookDisplayProductPriceBlock($params): string
    {
        if (Helper::getMultistoreConfigurationValue('TPAY_PEKAO_INSTALLMENTS_ACTIVE') && Helper::getMultistoreConfigurationValue('TPAY_PEKAO_INSTALLMENTS_PRODUCT_PAGE')) {
            $this->context->smarty->assign(array(
                'installmentText' => $this->module->l('Calculate installment!'),
                'merchantId' => Helper::getMultistoreConfigurationValue('TPAY_MERCHANT_ID'),
                'minAmount' => Config::PEKAO_INSTALLMENT_MIN,
                'maxAmount' => Config::PEKAO_INSTALLMENT_MAX,
            ));

            return $this->module->fetch('module:tpay/views/templates/hook/product_installment.tpl');
        }

        return '';
    }

    public function hookDisplayShoppingCart($params)
    {
        if (Helper::getMultistoreConfigurationValue('TPAY_PEKAO_INSTALLMENTS_ACTIVE') && Helper::getMultistoreConfigurationValue('TPAY_PEKAO_INSTALLMENTS_SHOPPING_CART')) {
            $this->context->smarty->assign(array(
                'installmentText' => $this->module->l('Calculate installment!'),
                'checkout_url' => $this->context->link->getPageLink('order'),
                'merchantId' => Helper::getMultistoreConfigurationValue('TPAY_MERCHANT_ID'),
                'minAmount' => Config::PEKAO_INSTALLMENT_MIN,
                'maxAmount' => Config::PEKAO_INSTALLMENT_MAX,
            ));

            return $this->module->fetch('module:tpay/views/templates/hook/cart_installment.tpl');
        }

        return '';
    }

    public function hookDisplayPaymentTop($params)
    {
        if (Helper::getMultistoreConfigurationValue('TPAY_PEKAO_INSTALLMENTS_ACTIVE') && Helper::getMultistoreConfigurationValue('TPAY_PEKAO_INSTALLMENTS_CHECKOUT')) {
            $cart = $params['cart'];
            $totalAmount = $cart->getOrderTotal(true, Cart::BOTH);

            $this->context->smarty->assign(array(
                'installmentText' => $this->module->l('Calculate installment!'),
                'merchantId' => Helper::getMultistoreConfigurationValue('TPAY_MERCHANT_ID'),
                'amount' => $totalAmount,
                'minAmount' => Config::PEKAO_INSTALLMENT_MIN,
                'maxAmount' => Config::PEKAO_INSTALLMENT_MAX,
            ));

            return $this->module->fetch('module:tpay/views/templates/hook/checkout_installments.tpl');
        }

        return '';
    }
}
