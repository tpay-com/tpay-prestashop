<?php
/**
@author Krajowy Integrator Płatności S.A.
@copyright Krajowy Integrator Płatności S.A.
@license MIT

Copyright (c) 2026 Krajowy Integrator Płatności S.A.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.*/

declare(strict_types=1);

namespace Tpay\Factory;

use PrestaShopLogger;
use Tpay\Config\Config;
use Tpay\Exception\PaymentException;
use Tpay\Handler\BasicPaymentHandler;
use Tpay\Handler\CreditCardPaymentHandler;
use Tpay\Handler\InstantPaymentHandler;
use Tpay\Handler\PaymentMethodHandler;

class PaymentFactory
{
    /**
     * Get a payment method by type
     *
     * @throws PaymentException
     *
     * @return PaymentMethodHandler
     */
    public static function getPaymentMethod(string $type)
    {
        switch ($type) {
            case Config::TPAY_PAYMENT_BASIC:
            case Config::TPAY_PAYMENT_GPAY:
            case Config::TPAY_PAYMENT_BLIK:
                return new BasicPaymentHandler();
            case Config::TPAY_PAYMENT_GENERIC:
                return new InstantPaymentHandler();
            case Config::TPAY_PAYMENT_CARDS:
                return new CreditCardPaymentHandler();
            default:
                PrestaShopLogger::addLog('Unknown Payment Method', 3);
                throw new PaymentException('Unknown Payment Method');
        }
    }
}
