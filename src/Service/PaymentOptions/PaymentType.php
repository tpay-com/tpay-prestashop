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

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Tpay;

class PaymentType
{
    /** @var GatewayType */
    private $gateway;

    public function __construct(
        GatewayType $gateway
    ) {
        $this->gateway = $gateway;
    }

    public function getPaymentOption(
        Tpay $module,
        PaymentOption $paymentOption,
        array $data = []
    ): PaymentOption {
        return $this->gateway->getPaymentOption(
            $module,
            $paymentOption,
            $data
        );
    }
}
