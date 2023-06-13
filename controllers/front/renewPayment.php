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

use Tpay\Config\Config;

class TpayRenewPaymentModuleFrontController extends ModuleFrontController
{
    /**
     * @throws PrestaShopException
     */
    public function initContent()
    {
        parent::initContent();

        $transactionId = \Tools::getValue('transactionId');

        $request = [
            'groupId' => Config::GATEWAY_BLIK_0,
            'method' => 'pay_by_link',
        ];

        if ($transactionId) {
            $response = $this->module->api->Transactions->createPaymentByTransactionId(
                $request,
                $transactionId
            );
            
            if (isset($response['transactionPaymentUrl'])) {
                Tools::redirect($response['transactionPaymentUrl']);
            }
        }

        $this->context->smarty->assign(
            [
                'error' => $this->module->l('Incorrect transaction id'),
            ]
        );

        $this->setTemplate(Config::TPAY_PATH . '/renewPayment.tpl');
    }
}
