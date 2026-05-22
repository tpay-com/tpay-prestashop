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
                    'tpay_path' => Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/tpay/views/',
                    'surcharge' => $surcharge > 0 ? $this->context->getCurrentLocale()->formatPrice($this->getSurchargeCost(), $this->context->currency->iso_code) : false,
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
                'modulesDir' => Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/',
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
