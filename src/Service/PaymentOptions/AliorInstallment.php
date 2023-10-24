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

class AliorInstallment implements GatewayType
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
        ]);

        $paymentOption->setCallToActionText($module->l('Alior Raty'))
            ->setAction($moduleLink)
            ->setInputs([
                [
                    'type' => 'hidden',
                    'name' => 'tpay',
                    'value' => true,
                ],
                [
                    'type' => 'hidden',
                    'name' => 'type',
                    'value' => Config::TPAY_PAYMENT_INSTALLMENTS,
                ],
                [
                    'type' => 'hidden',
                    'name' => 'tpay_transfer_id',
                    'value' => Config::TPAY_GATEWAY_ALIOR_RATY,
                ],
           ])
            ->setLogo($data['img'])
            ->setAdditionalInformation(
                $module->fetch('module:tpay/views/templates/hook/installments.tpl')
            );

        return $paymentOption;
    }
}
