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
use Tpay\Entity\TpayBlik;
use Tpay\Util\Helper;

class Blik implements GatewayType
{
    private $method;

    public function getPaymentOption(
        \Tpay $module,
        PaymentOption $paymentOption,
        array $data = []
    ): PaymentOption {
        $this->typeMethod();

        $blikSavedAliases = $this->getSavedBlikAliases(
            $module,
            \Context::getContext()->customer->id
        );


        $moduleLink = Context::getContext()->link->getModuleLink('tpay', $this->method, [], true);
        Context::getContext()->smarty->assign([
            'blik_type' => Helper::getMultistoreConfigurationValue('TPAY_BLIK_WIDGET') ? 'widget' : 'redirect',
            'blik_gateway' => $data['id'],
            'blik_moduleLink' => $moduleLink,
            'blik_saved_aliases' => $blikSavedAliases,
            'blik_order_id' => \Context::getContext()->cart->id,
            'assets_path' => $module->getPath(),
        ]);

        $paymentOption->setCallToActionText($module->l('BLIK'))
            ->setAction($moduleLink)
            ->setLogo($data['img'])
            ->setInputs([
                [
                    'type' => 'hidden',
                    'name' => 'tpay',
                    'value' => true,
                ],
                [
                    'type' => 'hidden',
                    'name' => 'type',
                    'value' => Config::TPAY_PAYMENT_BLIK,
                ],
                [
                    'type' => 'hidden',
                    'name' => 'tpay_transfer_id',
                    'value' => Config::GATEWAY_BLIK_0,
                ],
            ])
            ->setAdditionalInformation(
                $module->fetch('module:tpay/views/templates/hook/blik.tpl')
            );

        return $paymentOption;
    }

    public function getSavedBlikAliases($module, $userId)
    {
        $blikRepository = $module->getService('tpay.repository.blik');
        return $blikRepository->getBlikAliasIdByUserId($userId);
    }

    private function typeMethod()
    {
        $this->method = 'payment';

        if (Helper::getMultistoreConfigurationValue('TPAY_BLIK_WIDGET')) {
            $this->method = 'chargeBlik';
        }
    }
}
