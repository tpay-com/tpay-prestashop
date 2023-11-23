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

namespace Tpay\Service\PaymentOptions;

use Tpay\Config\Config;
use Context;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class PekaoInstallment implements GatewayType
{
    private $method = 'payment';

    public function getPaymentOption(
        \Tpay $module,
        PaymentOption $paymentOption,
        array $data = []
    ): PaymentOption {

        $moduleLink = Context::getContext()->link->getModuleLink('tpay', $this->method, [], true);
        Context::getContext()->smarty->assign([
            'installments_moduleLink' => $moduleLink,
            'available_channels' => $data['availablePaymentChannels']
        ]);
        $paymentOption->setCallToActionText($module->l('Raty Pekao'))
            ->setAction($moduleLink)
            ->setLogo($data['img'])
            ->setForm($this->generateForm());

        return $paymentOption;
    }
    protected function generateForm()
    {
        Context::getContext()->smarty->assign([
            'action' => Context::getContext()->link->getModuleLink('tpay', $this->method, [], true),
            'tpay' => true,
            'type' => Config::TPAY_PAYMENT_INSTALLMENTS,
            'tpay_channel_id' => 0,
        ]);

        return Context::getContext()->smarty->fetch('module:tpay/views/templates/hook/pekao_installments.tpl');
    }
}
