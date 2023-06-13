<?php

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
                        'order_id' => $order->id
                    ]
                )
            );
        } else {
            \PrestaShopLogger::addLog('Unknown blik status', 3);
            throw new PaymentException('Unknown blik status');
        }
    }
}
