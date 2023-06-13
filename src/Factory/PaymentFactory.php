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
use Tpay\Exception\PaymentException;
use Tpay\Handler\BasicPaymentHandler;
use Tpay\Handler\CreditCardPaymentHandler;
use Tpay\Handler\PaymentMethodHandler;

class PaymentFactory
{
    /**
     * Get a payment method by type
     *
     * @param string $type
     *
     * @throws PaymentException
     * @return PaymentMethodHandler
     */
    public static function getPaymentMethod(string $type)
    {
        switch ($type) {
            case Config::TPAY_PAYMENT_BASIC:
            case Config::TPAY_PAYMENT_INSTALLMENTS:
            case Config::TPAY_PAYMENT_GPAY:
            case Config::TPAY_PAYMENT_BLIK:
                return new BasicPaymentHandler();
            case Config::TPAY_PAYMENT_CARDS:
                return new CreditCardPaymentHandler();
            default:
                \PrestaShopLogger::addLog('Unknown Payment Method', 3);
                throw new PaymentException('Unknown Payment Method');
        }
    }
}
