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
SOFTWARE.

@author Krajowy Integrator Płatności S.A.*/

declare(strict_types=1);

namespace Tpay\Service\PaymentOptions;

use Context;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Tpay;
use Tpay\Service\GenericPayments\GenericPaymentsManager;

class Generic implements GatewayType
{
    public function getPaymentOption(Tpay $module, PaymentOption $paymentOption, array $data = []): PaymentOption
    {
        $moduleLink = Context::getContext()->link->getModuleLink('tpay', 'payment', [], true);
        $paymentOption->setCallToActionText($this->getActionText($module, $data))
            ->setAction($moduleLink)
            ->setLogo($data['image']['url'])
            ->setForm($this->generateForm($moduleLink, $data['id']));

        return $paymentOption;
    }

    private function getActionText(Tpay $module, $data): string
    {
        if (GenericPaymentsManager::CHANNEL_BLIK_BNPL === (int) $data['id']) {
            return $module->getTranslator()->trans('BLIK Pay Later', [], 'Modules.Tpay.Shop');
        }

        return $data['fullName'];
    }

    private function generateForm(string $moduleLink, $channelId): string
    {
        Context::getContext()->smarty->assign(
            [
                'action' => $moduleLink,
                'tpay' => 'true',
                'channelId' => $channelId,
                'blikBnplId' => GenericPaymentsManager::CHANNEL_BLIK_BNPL,
                'tpay_channel_id' => 0,
                'type' => 'generic',
            ]
        );

        return Context::getContext()->smarty->fetch('module:tpay/views/templates/hook/generic.tpl');
    }
}
