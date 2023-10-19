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

namespace Tpay\Factory;

use Tpay\Config\Config;
use Tpay\Service\PaymentOptions\Blik;
use Tpay\Service\PaymentOptions\Card;
use Tpay\Service\PaymentOptions\GatewayType;
use Tpay\Service\PaymentOptions\GooglePay;
use Tpay\Service\PaymentOptions\AliorInstallment;
use Tpay\Service\PaymentOptions\PeakoInstallment;
use Tpay\Service\PaymentOptions\PekaoInstallment;
use Tpay\Service\PaymentOptions\Transfer;
use Tpay\Service\PaymentOptions\Twisto;

class PaymentOptionsFactory
{
    /**
     * Build payment method
     * @param int $id
     * @return GatewayType|string
     */
    public static function getOptionById(int $id)
    {
        switch ($id) {
            case Config::GATEWAY_TRANSFER:
                $paymentMethod = new Transfer();
                break;
            case Config::GATEWAY_ALIOR_RATY:
                $paymentMethod = new AliorInstallment();
                break;
            case Config::GATEWAY_CARD:
                $paymentMethod = new Card();
                break;
            case Config::GATEWAY_TWISTO:
                $paymentMethod = new Twisto();
                break;
            case Config::GATEWAY_BLIK:
                $paymentMethod = new Blik();
                break;
            case Config::GATEWAY_GOOGLE_PAY:
                $paymentMethod = new GooglePay();
                break;
            case Config::GATEWAYS_PEKAO_RATY:
            case Config::GATEWAYS_PEKAO_RATY_50:
            case Config::GATEWAYS_PEKAO_RATY_10x0:
            case Config::GATEWAYS_PEKAO_RATY_3x0:
                $paymentMethod = new PekaoInstallment();
                break;
            default:
                $paymentMethod = '';
        }
        return $paymentMethod;
    }
}
