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

@author Krajowy Integrator Płatności S.A.*/

declare(strict_types=1);

namespace Tpay\Util;

use Configuration;
use Context;

class Helper
{
    /** Generate string */
    public static function generateRandomString(int $length = 46): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $length; ++$i) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

    public static function getFields(): array
    {
        return [
            'TPAY_CLIENT_ID',
            'TPAY_SECRET_KEY',
            'TPAY_BLIK_ACTIVE',
            'TPAY_BLIK_BNPL_ACTIVE',
            'TPAY_BLIK_WIDGET',
            'TPAY_TRANSFER_WIDGET',
            'TPAY_CUSTOM_ORDER[]',
            'TPAY_CUSTOM_ORDER',
            'TPAY_CARD_WIDGET',
            'TPAY_CARD_ACTIVE',
            'TPAY_CARD_RSA',
            'TPAY_MERCHANT_SECRET',
            'TPAY_CRC_FORM',
            'TPAY_APPLEPAY_ACTIVE',
            'TPAY_SANDBOX',
            'TPAY_REDIRECT_TO_CHANNEL',
            'TPAY_SURCHARGE_ACTIVE',
            'TPAY_SURCHARGE_TYPE',
            'TPAY_SURCHARGE_VALUE',
            'TPAY_BANNER',
            'TPAY_NOTIFICATION_EMAILS',
            'TPAY_SUMMARY',
            'TPAY_CONFIRMED',
            'TPAY_VIRTUAL_CONFIRMED',
            'TPAY_ERROR',
            'TPAY_PENDING',
            'TPAY_GLOBAL_SETTINGS',
            'TPAY_GENERIC_PAYMENTS[]',
            'TPAY_GENERIC_PAYMENTS',
            'TPAY_NOTIFICATION_ADDRESS',
            'TPAY_MERCHANT_ID',
            'TPAY_AUTO_CANCEL_ACTIVE',
            'TPAY_AUTO_CANCEL_DAYS',
            'TPAY_AUTO_CANCEL_FRONTEND_RUN',
            'TPAY_PEKAO_INSTALLMENTS_ACTIVE',
            'TPAY_PEKAO_INSTALLMENTS_PRODUCT_PAGE',
            'TPAY_PEKAO_INSTALLMENTS_SHOPPING_CART',
            'TPAY_PEKAO_INSTALLMENTS_CHECKOUT',
        ];
    }

    public static function getFieldsDefaultValues(): array
    {
        return [
            'TPAY_BLIK_ACTIVE' => '1',
            'TPAY_BLIK_WIDGET' => '1',
            'TPAY_BLIK_BNPL_ACTIVE' => '0',
            'TPAY_TRANSFER_WIDGET' => '1',
            'TPAY_CARD_WIDGET' => '1',
            'TPAY_CARD_ACTIVE' => '0',
            'TPAY_APPLEPAY_ACTIVE' => '0',
            'TPAY_SANDBOX' => '0',
            'TPAY_REDIRECT_TO_CHANNEL' => '0',
            'TPAY_SURCHARGE_ACTIVE' => '0',
            'TPAY_SURCHARGE_TYPE' => '0',
            'TPAY_SURCHARGE_VALUE' => '0',
            'TPAY_GLOBAL_SETTINGS' => '0',
            'TPAY_NOTIFICATION_ADDRESS' => '0',
            'TPAY_AUTO_CANCEL_ACTIVE' => 0,
            'TPAY_AUTO_CANCEL_FRONTEND_RUN' => 0,
            'TPAY_AUTO_CANCEL_DAYS' => 7,
        ];
    }

    public static function getMultistoreConfigurationValue($name)
    {
        if ('1' === Configuration::get('TPAY_GLOBAL_SETTINGS', null, Context::getContext()->shop->id_shop_group, Context::getContext()->shop->id)) {
            return Configuration::getGlobalValue($name);
        }

        return Configuration::get($name);
    }
}
