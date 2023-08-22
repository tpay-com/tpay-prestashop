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
use Tpay\Config\Config;
use Tpay\Handler\ExceptionHandler;

class TpayConfirmationModuleFrontController extends ModuleFrontController
{
    private $statusHandler;

    /**
     * @throws PrestaShopException
     */
    public function initContent()
    {
        $this->statusHandler = $this->module->getService('tpay.handler.order_status_handler');

        $type = Tools::getValue('type');

        switch ($type) {
            case Config::TPAY_PAYMENT_INSTALLMENTS:
            case Config::TPAY_PAYMENT_BASIC:
            case Config::TPAY_PAYMENT_BLIK:
            case Config::TPAY_PAYMENT_CARDS:
                $this->confirmPaymentBasic();
                break;
            default:
                \PrestaShopLogger::addLog('Incorrect payment type', 3);
                throw new \PrestaShopException('Incorrect payment type');
        }

        $this->setTemplate(Config::TPAY_PATH . '/redirect.tpl');
    }

    private function confirmPaymentBasic(): void
    {
        try {
            $orderId = (int)Tools::getValue('order_id');

            if ($orderId === 0) {
                throw new \Exception('No order exist in Tpay table for the specified crc');
            }

            $transactionRepository = $this->module->getService('tpay.repository.transaction');
            $transactionId = $transactionRepository->getTransactionIdByOrderId($orderId);

            $this->setConfirmed($orderId, $transactionId);
            $this->redirectSuccess($orderId);

        } catch (\Exception $e) {
            ExceptionHandler::handle($e);
        }
    }

    private function redirectSuccess($orderId): void
    {
        $order = new Order($orderId);
        $cart = new Cart($order->id_cart);
        $customer = new Customer($order->id_customer);

        Tools::redirect(
            'index.php?controller=order-confirmation&id_cart=' . (int)$cart->id . '&id_module=' .
            (int)$this->module->id . '&id_order=' . $order->id . '&key=' . $customer->secure_key
        );
    }

    private function setConfirmed($orderId, $transactionId): void
    {
        $this->statusHandler->setOrdersAsConfirmed(
            new \Order($orderId),
            $transactionId
        );
    }
}
