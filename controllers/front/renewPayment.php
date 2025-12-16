<?php

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

        $this->setTemplate(Config::TPAY_PATH.'/renewPayment.tpl');
    }
}
