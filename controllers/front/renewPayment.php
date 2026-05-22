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

use Tpay\Config\Config;

class TpayRenewPaymentModuleFrontController extends ModuleFrontController
{
    /** @throws PrestaShopException */
    public function initContent()
    {
        parent::initContent();

        $transactionId = Tools::getValue('transactionId');

        $request = [
            'groupId' => Config::GATEWAY_BLIK_0,
            'method' => 'pay_by_link',
        ];

        if ($transactionId) {
            $response = $this->module->api->transactions()->createPaymentByTransactionId(
                $request,
                $transactionId
            );

            if (isset($response['transactionPaymentUrl'])) {
                Tools::redirect($response['transactionPaymentUrl']);
            }
        }

        $this->context->smarty->assign(
            [
                'error' => $this->trans('Incorrect transaction id', [], 'Modules.Tpay.Shop'),
            ]
        );

        $this->setTemplate(Config::TPAY_PATH . '/renewPayment.tpl');
    }
}
