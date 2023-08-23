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

namespace Tpay\Service;

use Configuration as Cfg;
use Tpay\Config\Config;

class SurchargeService
{
    /**
     * Return surcharge value.
     *
     * @param null $orderTotal
     *
     * @throws \Exception
     * @return float
     */
    public function getSurchargeValue($orderTotal = null): float
    {
        if (!$this->activeSurcharge()) {
            return 0.00;
        }

        if (!$orderTotal) {
            $cart = \Context::getContext()->cart;
            $orderTotal = (float) $cart->getOrderTotal(true, \Cart::BOTH);
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

    /**
     * @throws \Exception
     */
    public function getTotalOrderAndSurchargeCost()
    {
        $cart = \Context::getContext()->cart;
        $orderTotal = (float) $cart->getOrderTotal(true, \Cart::BOTH);
        $surcharge = $this->getSurchargeValue();

        return (float) ($orderTotal + $surcharge);
    }




    public function activeSurcharge(): bool
    {
        $surchargeActive = (bool) Cfg::get('TPAY_SURCHARGE_ACTIVE');
        if (!$surchargeActive || $this->parseSurchargeValue() <= 0.00) {
            return false;
        }

        return true;
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

    private function parseSurchargeValue()
    {
        return (float)number_format(
            (float)str_replace(
                [',', ' ',],
                ['.', '',],
                Cfg::get('TPAY_SURCHARGE_VALUE')
            ),
            2,
            '.',
            ''
        );
    }
}
