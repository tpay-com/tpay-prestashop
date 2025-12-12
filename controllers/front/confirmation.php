<?php
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

        $this->setTemplate(Config::TPAY_PATH.'/redirect.tpl');
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
            'index.php?controller=order-confirmation&id_cart='.(int) $cart->id.'&id_module='
            .(int) $this->module->id.'&id_order='.$order->id.'&key='.$customer->secure_key
        );
    }
}
