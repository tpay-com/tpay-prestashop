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

namespace Tpay\Install;

use Configuration;
use Tpay\Exception\BaseException;

class Reset
{
    /**
     * Deleting sql data
     * @return bool
     * @throws BaseException
     */
    public function resetDb(): bool
    {
        $configurations = ['TPAY_CLIENT_ID',
            'TPAY_SECRET_KEY',
            'TPAY_BLIK_ACTIVE',
            'TPAY_BLIK_WIDGET',
            'TPAY_BLIK_BNPL_ACTIVE',
            'TPAY_TRANSFER_WIDGET',
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
            'TPAY_GENERIC_PAYMENTS',
            'TPAY_NOTIFICATION_ADDRESS',
            'TPAY_MERCHANT_ID',
            'TPAY_AUTO_CANCEL_ACTIVE',
            'TPAY_AUTO_CANCEL_DAYS',
            'TPAY_AUTO_CANCEL_FRONTEND_RUN',
            'TPAY_PEKAO_INSTALLMENTS_ACTIVE',
            'TPAY_PEKAO_INSTALLMENTS_PRODUCT_PAGE',
            'TPAY_PEKAO_INSTALLMENTS_SHOPPING_CART',
            'TPAY_PEKAO_INSTALLMENTS_CHECKOUT'];

        try {
            foreach ($configurations as $configName) {
                Configuration::deleteByName($configName);
            }
            return true;
        } catch (\Exception $exception) {
            \PrestaShopLogger::addLog($exception->getMessage(), 3);
            throw new BaseException($exception->getMessage());
        }

    }
}
