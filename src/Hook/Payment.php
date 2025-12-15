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

use PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException;
use PrestaShopException;
use Tools;
use Tpay\Service\PaymentOptions\PaymentOptionsService;

class Payment extends AbstractHook
{
    public const AVAILABLE_HOOKS = [
        'paymentOptions',
        'paymentReturn',
    ];

    /**
     * Create payment methods
     *
     * @throws LocalizationException|PrestaShopException
     */
    public function paymentOptions($params)
    {
        if (!$this->module->active || !$this->module->api) {
            return [];
        }

        if (!$this->module->checkCurrency($params['cart'])) {
            return [];
        }
        $surcharge = $this->getSurchargeCost();

        $langData = [
            'regulation_url' => 'https://tpay.com/user/assets/files_for_download/payment-terms-and-conditions.pdf',
            'clause_url' => 'https://tpay.com/user/assets/files_for_download/information-clause-payer.pdf',
        ];

        if ('pl' == $this->context->language->iso_code) {
            $langData = [
                'regulation_url' => 'https://secure.tpay.com/regulamin.pdf',
                'clause_url' => 'https://tpay.com/user/assets/files_for_download/klauzula-informacyjna-platnik.pdf',
            ];
        }

        $this->context->smarty->assign(
            array_merge(
                $langData,
                [
                'tpay_path' => Tools::getHttpHost(true).__PS_BASE_URI__.'modules/tpay/views/',
                'surcharge' => $surcharge > 0 ? Tools::displayPrice($this->getSurchargeCost()) : false,
                ]
            )
        );

        $paymentService = new PaymentOptionsService($this->module);

        $payments = $paymentService->getActivePayments();
        if ($surcharge > 0) {
            $surchargeInfo = $this->module->fetch('module:tpay/views/templates/hook/tpay_surcharge_cost.tpl');
            foreach ($payments as $payment) {
                $info = $surchargeInfo;
                $info .= $payment->getAdditionalInformation();
                $payment->setAdditionalInformation($info);
            }
        }

        return $payments;
    }

    /**
     * Return payment/order confirmation step hook
     *
     * @return string|void
     */
    public function paymentReturn()
    {
        if (!$this->module->active) {
            return;
        }
        $this->context->smarty->assign(
            [
            'status' => Tools::getValue('status'),
            'historyLink' => 'index.php?controller=history',
            'homeLink' => 'index.php',
            'contactLink' => 'index.php?controller=contact',
            'modulesDir' => Tools::getHttpHost(true).__PS_BASE_URI__.'modules/',
            ]
        );

        return $this->module->fetch('module:tpay/views/templates/hook/paymentReturn.tpl');
    }

    private function getSurchargeCost()
    {
        $orderTotal = (float) $this->context->cart->getOrderTotal();
        $surchargeService = $this->module->getService('tpay.service.surcharge');

        return $surchargeService->getSurchargeValue($orderTotal);
    }
}
