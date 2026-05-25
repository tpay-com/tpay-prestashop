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

use Configuration as Cfg;
use Tpay\Config\Config;
use Tpay\Factory\PaymentFactory;
use Tpay\Handler\PaymentHandler;
use Tpay\Service\SurchargeService;

class TpayPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $data;
    public $display_column_left = false;

    /** @throws PrestaShopException */
    public function initContent()
    {
        parent::initContent();

        $this->data = Tools::getAllValues();

        $retryOrderId = Tools::getValue('retry_order');
        if ($retryOrderId) {
            $this->handleRetry($retryOrderId);

            return;
        }

        $cart = $this->context->cart;
        $this->updateLang($cart);

        if (empty($cart->id)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        if (true === $cart->orderExists()) {
            exit(
                $this->trans(
                    'Cart cannot be loaded or an order has already been placed using this cart',
                    [],
                    'Modules.Tpay.Shop'
                )
            );
        }

        $orderTotal = $this->getOrderTotal($cart);

        $this->processPayment(
            $orderTotal,
            $customer,
            $cart
        );

        $this->setTemplate(Config::TPAY_PATH . '/redirect.tpl');
    }

    private function handleRetry($orderId)
    {
        $order = new Order((int) $orderId);
        if (!Validate::isLoadedObject($order) || $order->current_state !== (int) Cfg::get('TPAY_PENDING')) {
            Tools::redirect($this->context->link->getPageLink('history', true));
        }
        $customer = new Customer($order->id_customer);

        try {
            $type = $this->context->cookie->tpay_payment_type;
            $paymentType = PaymentFactory::getPaymentMethod($type);
            $this->createTransaction($order, $paymentType);
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Błąd Tpay przy zamówieniu ID ' . $order->id . ': ' . $e->getMessage(), 3);
            $this->context->cookie->tpay_errors = $this->trans(
                'Failed to create the transaction. Please try again.',
                [],
                'Modules.Tpay.Shop'
            );
            Tools::redirect(
                'index.php?controller=order-confirmation&action=renew-payment&id_cart=' . $order->id_cart . '&id_module='
                . (int) $this->module->id . '&id_order=' . $order->id . '&key=' . $customer->secure_key
            );
        }
    }

    private function processPayment($orderTotal, $customer, $cart)
    {
        try {
            $this->validateUserOrder($orderTotal, $customer);
            $order = new Order($this->module->currentOrder);
        } catch (Exception $e) {
            PrestaShopLogger::addLog($e->getMessage(), 3);
            Tools::redirect('index.php?controller=order&step=3');

            return;
        }

        try {
            $paymentType = PaymentFactory::getPaymentMethod(Tools::getValue('type'));
            $this->context->cookie->tpay_payment_type = $paymentType->getName();
            $this->module->getContext()->smarty->assign(['nbProducts' => $cart->nbProducts()]);
            $this->createTransaction($order, $paymentType);
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Błąd Tpay przy zamówieniu ID ' . $order->id . ': ' . $e->getMessage(), 3);

            $order->setCurrentState(Cfg::get('TPAY_FAILED'));
            $this->context->cookie->tpay_errors = $this->trans(
                'Failed to create the transaction. Please try again.',
                [],
                'Modules.Tpay.Shop'
            );

            Tools::redirect(
                'index.php?controller=order-confirmation&action=renew-payment&id_cart=' . (int) $cart->id . '&id_module='
                . (int) $this->module->id . '&id_order=' . $order->id . '&key=' . $customer->secure_key
            );
        }
    }

    private function createTransaction(Order $order, $paymentType)
    {
        $address = new AddressCore($order->id_address_invoice);
        $customer = new Customer($order->id_customer);

        $handler = new PaymentHandler(
            $this->module,
            $paymentType,
            $order,
            $customer,
            $address,
            $this->data
        );

        $handler->get();
    }

    /**
     * Validate current order
     */
    private function validateUserOrder(float $orderTotal, $customer): void
    {
        $this->module->validateOrder(
            (int) $this->module->getContext()->cart->id,
            (int) Cfg::get('TPAY_PENDING'),
            $orderTotal,
            $this->module->displayName,
            null,
            [],
            (int) $this->module->getContext()->currency->id,
            $customer->isGuest() ? 0 : 1,
            $customer->secure_key
        );
    }

    private function getOrderTotal($cart): float
    {
        $surcharge = new SurchargeService();
        $orderTotal = $cart->getOrderTotal();
        $surchargeTotal = $surcharge->getSurchargeValue($orderTotal);

        return (float) $orderTotal + $surchargeTotal;
    }

    private function updateLang(Cart $cart): void
    {
        if (!isset($this->data['isolang'])) {
            $this->data['isolang'] = $cart->getAssociatedLanguage()->getIsoCode();
        }
    }
}
