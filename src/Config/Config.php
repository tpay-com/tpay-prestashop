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
