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

namespace Tpay\Util;

class Helper
{
    /**
     * Generate string
     * @param int $length
     * @return string
     */
    public static function generateRandomString(int $length = 46): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
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
        ];
    }

    public static function getFieldsDefaultValues(): array
    {
        return [
            'TPAY_BLIK_ACTIVE' => '1',
            'TPAY_BLIK_WIDGET' => '1',
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
        ];
    }

    public static function getMultistoreConfigurationValue($name)
    {
        if (
            \Configuration::get(
                'TPAY_GLOBAL_SETTINGS',
                null,
                \Context::getContext()->shop->id_shop_group,
                \Context::getContext()->shop->id
            ) === '1'
        ) {
            return \Configuration::getGlobalValue($name);
        } else {
            return \Configuration::get($name);
        }
    }
}
