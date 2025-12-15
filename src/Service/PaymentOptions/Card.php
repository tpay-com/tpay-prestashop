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

use Cart;
use Configuration as Cfg;
use Context;
use Exception;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Tpay;
use Tpay\Config\Config;
use Tpay\Service\SurchargeService;
use Tpay\Util\Helper;

class Card implements GatewayType
{
    private $method = 'payment';

    /** @throws Exception */
    public function getPaymentOption(
        Tpay $module,
        PaymentOption $paymentOption,
        array $data = []
    ): PaymentOption {
        $moduleLink = Context::getContext()->link->getModuleLink('tpay', $this->method, [], true);

        $creditCardRepository = $module->getService('tpay.repository.credit_card');
        $savedCreditCards = $creditCardRepository->getAllCreditCardsByUserId(Context::getContext()->customer->id);

        $creditCardsArray = [];
        if ($savedCreditCards) {
            foreach ($savedCreditCards as $card) {
                $creditCardsArray[] = $card;
            }
        }

        Context::getContext()->smarty->assign(
            [
                'card_type' => Helper::getMultistoreConfigurationValue('TPAY_CARD_WIDGET') ? 'widget' : 'redirect',
                'cards_moduleLink' => $moduleLink,
                'saved_cards' => $creditCardsArray,
                'assets_path' => $module->getPath(),
            ]
        );

        $paymentOption->setCallToActionText($module->getTranslator()->trans('Payment card', [], 'Modules.Tpay.Shop'))
            ->setAction($moduleLink)
            ->setLogo($data['img'])
            ->setInputs(
                [
                    [
                        'type' => 'hidden',
                        'name' => 'tpay',
                        'value' => true,
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'type',
                        'value' => Config::TPAY_PAYMENT_CARDS,
                    ],
                ]
            )
            ->setAdditionalInformation(
                $module->fetch('module:tpay/views/templates/hook/card.tpl')
            );

        return $paymentOption;
    }

    public function isActive(Cart $cart, SurchargeService $surchargeService): bool
    {
        return Cfg::get('TPAY_CARD_ACTIVE') && !empty(Cfg::get('TPAY_CARD_RSA'));
    }
}
