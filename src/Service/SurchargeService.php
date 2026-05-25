<?php
/**MIT License

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
SOFTWARE.

@author Krajowy Integrator Płatności S.A.
@copyright Krajowy Integrator Płatności S.A.*/

declare(strict_types=1);

namespace Tpay\Service;

use Cart;
use Configuration as Cfg;
use Context;
use Exception;
use Tpay\Config\Config;

class SurchargeService
{
    /**
     * Return surcharge value.
     *
     * @throws Exception
     */
    public function getSurchargeValue(?float $orderTotal = null): float
    {
        if (!$this->activeSurcharge()) {
            return 0.00;
        }

        if (!$orderTotal) {
            $cart = Context::getContext()->cart;
            $orderTotal = (float) $cart->getOrderTotal(true, Cart::BOTH);
        }

        $surchargeValue = $this->parseSurchargeValue();
        $configSurcharge = Config::TPAY_SURCHARGE_PERCENT;

        if ((int) Cfg::get('TPAY_SURCHARGE_TYPE') === $configSurcharge) {
            $surcharge = ((float) $orderTotal / 100) * $surchargeValue;
        } else {
            $surcharge = $surchargeValue;
        }

        return $surcharge;
    }

    /** @throws Exception */
    public function getTotalOrderAndSurchargeCost(): float
    {
        $cart = Context::getContext()->cart;
        $orderTotal = (float) $cart->getOrderTotal(true, Cart::BOTH);
        $surcharge = $this->getSurchargeValue();

        return (float) ($orderTotal + $surcharge);
    }

    public function activeSurcharge(): bool
    {
        $surchargeActive = (bool) Cfg::get('TPAY_SURCHARGE_ACTIVE');

        return !(!$surchargeActive || $this->parseSurchargeValue() <= 0.00);
    }

    public function getOrderSurcharge($repository, $orderId): float
    {
        $surcharge = $repository->getSurchargeValueByOrderId($orderId);

        return (float) $surcharge;
    }

    public function hasOrderSurcharge($repository, $orderId): bool
    {
        return (bool) $repository->getSurchargeValueByOrderId($orderId);
    }

    private function parseSurchargeValue(): float
    {
        return (float) number_format(
            (float) str_replace(
                [',', ' '],
                ['.', ''],
                Cfg::get('TPAY_SURCHARGE_VALUE')
            ),
            2,
            '.',
            ''
        );
    }
}
