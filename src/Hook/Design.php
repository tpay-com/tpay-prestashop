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
use Media;
use Tpay\Config\Config;

class Design extends AbstractHook
{
    public const AVAILABLE_HOOKS = [
        'displayHeader',
        'displayProductAdditionalInfo',
        'displayCustomerAccount',
    ];

    /**
     * Module header register scripts and styles.
     *
     * @return void
     */
    public function displayHeader(): void
    {
        $this->context->controller->addCSS($this->module->getPath() . 'views/css/main.css');
        $this->context->controller->addJS($this->module->getPath() . 'views/js/main.min.js');

        $ajax = $this->context->link->getModuleLink('tpay', 'ajax', [], true);
        $paymentErrorController = $this->context->link->getModuleLink('tpay', 'error', [], true);
        $cartController = $this->context->link->getPageLink(
            'cart',
            null,
            $this->context->language->id,
            [
                'ajax' => 1,
                'action' => 'refresh',
            ]
        );

        Media::addJsDef(
            [
                'messages' => [
                    'payment_error' => $this->module->l('Payment error'),
                    'blik_error' => $this->module->l('The code you entered is invalid or has expired.'),
                ],
                'surcharge_controller' => $ajax,
                'payment_error_controller' => $paymentErrorController,
                'cart_url' => $cartController,
                'blik_limit_attempt_msg' => $this->module->l('The blik code has expired'),
                'blik_accept_msg' => $this->module->l('Accept blik code on mobile app'),
                'blik_not_accepted' => $this->module->l('Transaction was not accepted in the bank\'s application'),
                'blik_rejected_msg' => $this->module->l('Transaction rejected by payer'),
                'blik_insufficient_funds_msg' => $this->module->l('Insufficient Funds'),


                'blik_msg' => [
                    61 => $this->module->l('invalid BLIK code or alias data format'),
                    62 => $this->module->l('error connecting BLIK system'),
                    63 => $this->module->l('invalid BLIK six-digit code'),
                    64 => $this->module->l('can not pay with BLIK code or alias for non BLIK transaction'),
                    65 => $this->module->l('incorrect transaction status - should be pending'),
                    66 => $this->module->l('BLIK POS is not available'),
                    82 => $this->module->l('given alias is non-unique'),
                    84 => $this->module->l('given alias has not been registered or has been deregistered'),
                    85 => $this->module->l('given alias section is incorrect'),
                    100 => $this->module->l('BLIK other error'),
                    101 => $this->module->l('BLIK payment declined by user'),
                    102 => $this->module->l('BLIK system general error'),
                    103 => $this->module->l('BLIK insufficient funds / user authorization error'),
                    104 => $this->module->l('BLIK user or system timeout'),
                ]
            ]
        );

        if (Cfg::get('TPAY_CARD_ACTIVE') && Cfg::get('TPAY_CARD_RSA')) {
            Media::addJsDef(
                [
                    'redirect_path' => $this->context->link->getModuleLink(
                        'tpay',
                        'payment',
                        ['type' => Config::TPAY_PAYMENT_CARDS]
                    ),
                    'surcharge_controller' => $ajax,
                    'rsa_key' => Cfg::get('TPAY_CARD_RSA'),
                ]
            );

            $this->context->controller->addJS($this->module->getPath() . 'views/js/jquery.formance.min.js');
            $this->context->controller->addJS($this->module->getPath() . 'views/js/jsencrypt.min.js');
            $this->context->controller->addJS($this->module->getPath() . 'views/js/string_routines.js');
            $this->context->controller->addJS($this->module->getPath() . 'views/js/jquery.payment.js');
            $this->context->controller->addJS($this->module->getPath() . 'views/js/cardPayment.js');
        }

        if (Cfg::get('TPAY_BLIK_ACTIVE') && Cfg::get('TPAY_BLIK_WIDGET')) {
            $this->context->controller->addJS($this->module->getPath() . 'views/js/blikPayment.js');
        }
    }

    /**
     * Hook for displaying tpay logo on product pages.
     *
     * @return string|void
     */
    public function displayProductAdditionalInfo()
    {
        if (Cfg::get('PS_CATALOG_MODE') || !Cfg::get('TPAY_BANNER')) {
            return;
        }

        $this->context->smarty->assign(
            ['banner_img' => 'https://tpay.com/img/banners/tpay-160x75.svg']
        );

        return $this->module->fetch('module:tpay/views/templates/hook/paymentlogo.tpl');
    }

    /**
     * Show saved cards in account page
     *
     * @return string|void
     */
    public function displayCustomerAccount()
    {
        if (Cfg::get('TPAY_CARD_ACTIVE')) {
            $this->context->smarty->assign(
                [
                    'accountCreditCards' => $this->context->link->getModuleLink('tpay', 'savedCards'),
                ]
            );

            return $this->module->fetch('module:tpay/views/templates/hook/account_credit_cards.tpl');
        }
    }
}
