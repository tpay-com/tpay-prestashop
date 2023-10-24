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
    public const TPAY_PAYMENT_INSTALLMENTS = 'installments';

    public const TPAY_GATEWAY_ALIOR_RATY = 109;
    public const TPAY_GATEWAY_PEKAO_RATY = 169;
    public const TPAY_GATEWAY_TWISTO = 167;
    public const TPAY_GATEWAY_GOOGLE_PAY = 166;

    /// IDS to get bank channels
    public const GATEWAY_TWISTO = 71; //71
    public const GATEWAY_ALIOR_RATY = 49; // 49
    public const GATEWAY_CARD = 53;
    public const GATEWAY_BLIK = 64;
    public const GATEWAY_BLIK_0 = 150;
    public const GATEWAY_GOOGLE_PAY = 68;

    public const GATEWAY_TRANSFER = 999;

    public const GATEWAY_APPLE_PAY = 9999;
    public const GATEWAYS_PEKAO_RATY_3x0 = 78;
    public const GATEWAYS_PEKAO_RATY_10x0 = 80;

    public const GATEWAYS_PEKAO_RATY_50 = 81;
    public const GATEWAYS_PEKAO_RATY = 77;

    public const CARD_GROUP_ID = 103;

    public const TPAY_VIEW_REDIRECT = 0;
    public const TPAY_SURCHARGE_AMOUNT = 0;
    public const TPAY_SURCHARGE_PERCENT = 1;
    public const TPAY_PATH = 'module:tpay/views/templates/front';

    public const TWISTO_MIN = 1.00;
    public const TWISTO_MAX = 1500;
    public const ALIOR_RATY_MIN = 300;
    public const ALIOR_RATY_MAX = 9259.25;

    public const PEKAO_RATY_MIN = 100;
    public const PEKAO_RATY_MAX = 20000;
}
