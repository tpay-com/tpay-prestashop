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

if (!defined('_PS_VERSION_')) {
    exit;
}

use Tpay\Config\Config;
use Tpay\Exception\PaymentException;

class TpayBlikModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws PaymentException
     */
    public function initContent()
    {
        parent::initContent();
        $orderId = Tools::getValue('orderId');
        $status = true;

        $order = new Order($orderId);
        $customer = new Customer($order->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect(__PS_BASE_URI__ . 'order.php?step=1');
        }

        if (!$this->module->active) {
            $status = false;
        }

        if ($status) {
            Tools::redirect(
                $this->context->link->getModuleLink(
                    'tpay',
                    'confirmation',
                    [
                        'type' => Config::TPAY_PAYMENT_BLIK,
                        'order_id' => $order->id,
                    ]
                )
            );
        } else {
            PrestaShopLogger::addLog('Unknown blik status', 3);
            throw new PaymentException('Unknown blik status');
        }
    }
}
