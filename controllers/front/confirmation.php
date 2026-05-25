<?php
/**
@author Krajowy Integrator Płatności S.A.
@copyright Krajowy Integrator Płatności S.A.
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
SOFTWARE.*/

use Tpay\Config\Config;
use Tpay\Handler\ExceptionHandler;

class TpayConfirmationModuleFrontController extends ModuleFrontController
{
    /** @throws PrestaShopException */
    public function initContent()
    {
        $type = Tools::getValue('type');

        switch ($type) {
            case Config::TPAY_PAYMENT_BASIC:
            case Config::TPAY_PAYMENT_BLIK:
            case Config::TPAY_PAYMENT_CARDS:
                $this->confirmPaymentBasic();
                break;
            default:
                PrestaShopLogger::addLog('Incorrect payment type', 3);
                throw new PrestaShopException('Incorrect payment type');
        }

        $this->setTemplate(Config::TPAY_PATH . '/redirect.tpl');
    }

    private function confirmPaymentBasic(): void
    {
        try {
            $orderId = (int) Tools::getValue('order_id');

            if (0 === $orderId) {
                throw new Exception('No order exist in Tpay table for the specified crc');
            }

            $this->redirectSuccess($orderId);
        } catch (Exception $e) {
            ExceptionHandler::handle($e);
        }
    }

    private function redirectSuccess($orderId): void
    {
        $order = new Order($orderId);
        $cart = new Cart($order->id_cart);
        $customer = new Customer($order->id_customer);

        Tools::redirect(
            'index.php?controller=order-confirmation&id_cart=' . (int) $cart->id . '&id_module='
            . (int) $this->module->id . '&id_order=' . $order->id . '&key=' . $customer->secure_key
        );
    }
}
