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

use Configuration as Cfg;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Tpay\Config\Config;
use Tpay\Repository\CreditCardsRepository;
use Tpay\Service\SurchargeService;
use Tpay\Util\Helper;

class Card implements GatewayType
{
    private $method = 'payment';

    /** @var \Context */
    private $context;

    public function __construct(\Context $context)
    {
        $this->context = $context;
    }

    /** @throws \Exception */
    public function getPaymentOption(\Tpay $module, PaymentOption $paymentOption, array $data = []): PaymentOption
    {
        $moduleLink = $this->context->link->getModuleLink('tpay', $this->method, [], true);

        /** @var CreditCardsRepository $creditCardRepository */
        $creditCardRepository = $module->getService('tpay.repository.credit_card');
        $savedCreditCards = $creditCardRepository->getAllCreditCardsByUserId($this->context->customer->id);

        $creditCardsArray = [];
        if ($savedCreditCards) {
            foreach ($savedCreditCards as $card) {
                $creditCardsArray[] = $card;
            }
        }

        $this->context->smarty->assign(
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

    public function isActive(\Cart $cart, SurchargeService $surchargeService): bool
    {
        return Cfg::get('TPAY_CARD_ACTIVE') && !empty(Cfg::get('TPAY_CARD_RSA'));
    }
}
