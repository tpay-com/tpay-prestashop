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
     */
    public function displayHeader(): void
    {
        $this->context->controller->addCSS($this->module->getPath().'views/css/main.css');
        $this->context->controller->addJS($this->module->getPath().'views/js/main.min.js');
        if (Cfg::get('TPAY_AUTO_CANCEL_ACTIVE') && Cfg::get('TPAY_AUTO_CANCEL_FRONTEND_RUN')) {
            $this->context->controller->addJS($this->module->getPath().'cron.php', false);
        }

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
        $translator = $this->module->getTranslator();
        Media::addJsDef(
            [
                'messages' => [
                    'payment_error' => $translator->trans('Payment error', [], 'Modules.Tpay.Shop'),
                    'blik_error' => $translator->trans(
                        'The code you entered is invalid or has expired.',
                        [],
                        'Modules.Tpay.Shop'
                    ),
                ],
                'surcharge_controller' => $ajax,
                'payment_error_controller' => $paymentErrorController,
                'cart_url' => $cartController,
                'blik_limit_attempt_msg' => $translator->trans('The blik code has expired', [], 'Modules.Tpay.Shop'),
                'blik_accept_msg' => $translator->trans('Accept blik code on mobile app', [], 'Modules.Tpay.Shop'),
                'blik_not_accepted' => $translator->trans(
                    'Transaction was not accepted in the bank\'s application',
                    [],
                    'Modules.Tpay.Shop'
                ),
                'blik_rejected_msg' => $translator->trans('Transaction rejected by payer', [], 'Modules.Tpay.Shop'),
                'blik_insufficient_funds_msg' => $translator->trans('Insufficient Funds', [], 'Modules.Tpay.Shop'),

                'blik_msg' => [
                    61 => $translator->trans('invalid BLIK code or alias data format', [], 'Modules.Tpay.Shop'),
                    62 => $translator->trans('error connecting BLIK system', [], 'Modules.Tpay.Shop'),
                    63 => $translator->trans('invalid BLIK six-digit code', [], 'Modules.Tpay.Shop'),
                    64 => $translator->trans(
                        'can not pay with BLIK code or alias for non BLIK transaction',
                        [],
                        'Modules.Tpay.Shop'
                    ),
                    65 => $translator->trans(
                        'incorrect transaction status - should be pending',
                        [],
                        'Modules.Tpay.Shop'
                    ),
                    66 => $translator->trans('BLIK POS is not available', [], 'Modules.Tpay.Shop'),
                    82 => $translator->trans('given alias is non-unique', [], 'Modules.Tpay.Shop'),
                    84 => $translator->trans(
                        'given alias has not been registered or has been deregistered',
                        [],
                        'Modules.Tpay.Shop'
                    ),
                    85 => $translator->trans('given alias section is incorrect', [], 'Modules.Tpay.Shop'),
                    100 => $translator->trans('BLIK other error', [], 'Modules.Tpay.Shop'),
                    101 => $translator->trans('BLIK payment declined by user', [], 'Modules.Tpay.Shop'),
                    102 => $translator->trans('BLIK system general error', [], 'Modules.Tpay.Shop'),
                    103 => $translator->trans(
                        'BLIK insufficient funds / user authorization error',
                        [],
                        'Modules.Tpay.Shop'
                    ),
                    104 => $translator->trans('BLIK user or system timeout', [], 'Modules.Tpay.Shop'),
                ],
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

            $this->context->controller->addJS($this->module->getPath().'views/js/jquery.formance.min.js');
            $this->context->controller->addJS($this->module->getPath().'views/js/jsencrypt.min.js');
            $this->context->controller->addJS($this->module->getPath().'views/js/string_routines.js');
            $this->context->controller->addJS($this->module->getPath().'views/js/jquery.payment.js');
            $this->context->controller->addJS($this->module->getPath().'views/js/cardPayment.js');
        }

        if (Cfg::get('TPAY_BLIK_ACTIVE') && Cfg::get('TPAY_BLIK_WIDGET')) {
            $this->context->controller->addJS($this->module->getPath().'views/js/blikPayment.js');
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
