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

namespace Tpay\Config;

class Config
{
    public const TPAY_PAYMENT_BASIC = 'transfer';
    public const TPAY_PAYMENT_CARDS = 'cards';
    public const TPAY_PAYMENT_BLIK = 'blik';
    public const TPAY_PAYMENT_GPAY = 'gpay';
    public const TPAY_PAYMENT_GENERIC = 'generic';

    public const GATEWAY_CARD = 53;
    public const GATEWAY_BLIK = 64;
    public const GATEWAY_BLIK_0 = 150;

    public const GATEWAY_TRANSFER = 999;

    public const CARD_GROUP_ID = 103;

    public const TPAY_VIEW_REDIRECT = 0;
    public const TPAY_SURCHARGE_AMOUNT = 0;
    public const TPAY_SURCHARGE_PERCENT = 1;
    public const TPAY_PATH = 'module:tpay/views/templates/front';

    public const PEKAO_INSTALLMENT_MIN = 100;
    public const PEKAO_INSTALLMENT_MAX = 20000;
}
