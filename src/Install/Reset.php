<?php
/**MIT License
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
SOFTWARE.

@author Krajowy Integrator Płatności S.A.
@copyright Krajowy Integrator Płatności S.A.*/

declare(strict_types=1);

namespace Tpay\Install;

use Configuration;
use Exception;
use PrestaShopLogger;
use Tpay\Exception\BaseException;

class Reset
{
    /**
     * Deleting sql data
     *
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
            'TPAY_PEKAO_INSTALLMENTS_CHECKOUT', ];

        try {
            foreach ($configurations as $configName) {
                Configuration::deleteByName($configName);
            }

            return true;
        } catch (Exception $exception) {
            PrestaShopLogger::addLog($exception->getMessage(), 3);
            throw new BaseException($exception->getMessage());
        }
    }
}
