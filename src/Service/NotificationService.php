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
use tpaySDK\Utilities\TpayException;
use tpaySDK\Webhook\PaymentNotification;

class NotificationService
{
    /**
     * @throws TpayException
     */
    public function checkPayment()
    {
        $NotificationWebhook = new PaymentNotification();
        $merchantSecret = Cfg::get('TPAY_MERCHANT_SECRET');
        if ($merchantSecret) {
            return $NotificationWebhook->getNotification('TRUE', Cfg::get('TPAY_MERCHANT_SECRET'));
        }
        return false;
    }
}
