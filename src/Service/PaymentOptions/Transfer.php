<?php
/**
 * @author Krajowy Integrator Płatności S.A.
 * @copyright Krajowy Integrator Płatności S.A.
 * @license MIT
 *
 * Copyright (c) 2026 Krajowy Integrator Płatności S.A.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Tpay\Service\PaymentOptions;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Tpay\Config\Config;
use Tpay\Service\GenericPayments\GenericPaymentsManager;
use Tpay\Util\Helper;

class Transfer implements GatewayType
{
    private $method = 'payment';

    /** @var \Context */
    private $context;

    public function __construct(\Context $context)
    {
        $this->context = $context;
    }

    public function getPaymentOption(
        \Tpay $module,
        PaymentOption $paymentOption,
        array $data = []
    ): PaymentOption {
        $moduleLink = $this->context->link->getModuleLink('tpay', $this->method, [], true);
        $this->context->smarty->assign(
            [
                'transfer_type' => Helper::getMultistoreConfigurationValue('TPAY_TRANSFER_WIDGET') ? 'widget' : 'redirect',
                'transfer_gateway' => $data['id'],
                'transfer_moduleLink' => $moduleLink,
                'gateways' => $this->sortGateways($data['gateways']),
                'isDirect' => (bool) \Configuration::get('TPAY_REDIRECT_TO_CHANNEL'),
            ]
        );

        $paymentOption->setCallToActionText($module->getTranslator()->trans('Pay by online transfer with Tpay', [], 'Modules.Tpay.Shop'))
            ->setAction($moduleLink)
            ->setLogo($data['img'])
            ->setForm($this->generateForm());

        return $paymentOption;
    }

    protected function generateForm()
    {
        $this->context->smarty->assign(
            [
                'action' => $this->context->link->getModuleLink('tpay', $this->method, [], true),
                'tpay' => true,
                'type' => Config::TPAY_PAYMENT_BASIC,
                'tpay_transfer_id' => 0,
            ]
        );

        return $this->context->smarty->fetch('module:tpay/views/templates/hook/payment.tpl');
    }

    private function sortGateways(array $gateways)
    {
        $gateways = array_filter(
            $gateways,
            function ($gateway) {
                return !GenericPaymentsManager::isChannelExcluded((int) $gateway['mainChannel']);
            }
        );

        if ((bool) \Configuration::get('TPAY_REDIRECT_TO_CHANNEL') && !empty(\Configuration::get('TPAY_CUSTOM_ORDER'))) {
            $orderedList = [];
            $customOrder = json_decode(\Configuration::get('TPAY_CUSTOM_ORDER'), true);

            foreach ($customOrder as $orderNumber) {
                foreach ($gateways as $gateway) {
                    if ($gateway['mainChannel'] == $orderNumber) {
                        $orderedList[$gateway['mainChannel']] = $gateway;
                        unset($gateways[$gateway['mainChannel']]);
                    }
                }
            }

            return array_merge($orderedList, $gateways);
        }

        return $gateways;
    }
}
