<?php

/**
 * NOTICE OF LICENSE.
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    tpay.com
 * @copyright 2010-2016 tpay.com
 * @license   LICENSE.txt
 */

use Tpay\Config\Config;

/**
 * Class TpayOrderErrorModuleFrontController.
 */
class TpayOrderErrorModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $orderId = (int)Tools::getValue('order_id');
        $order = new Order($orderId);
        $cart = new Cart($order->id_cart);
        $customer = new Customer($order->id_customer);

        Tools::redirect(
            'index.php?controller=order-confirmation&action=renew-payment&id_cart=' . (int)$cart->id . '&id_module=' .
            (int)$this->module->id . '&id_order=' . $order->id . '&key=' . $customer->secure_key
        );
    }
}
