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
use Tpay\Service\PaymentOptions\Transfer;

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
            case Config::GATEWAY_CARD:
                $paymentMethod = new Card();
                break;
            case Config::GATEWAY_BLIK:
                $paymentMethod = new Blik();
                break;
            default:
                $paymentMethod = '';
        }
        return $paymentMethod;
    }
}
