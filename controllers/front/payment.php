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

use Configuration as Cfg;
use Tpay\Config\Config;
use Tpay\Factory\PaymentFactory;
use Tpay\Handler\ExceptionHandler;
use Tpay\Handler\PaymentHandler;
use Tpay\Service\SurchargeService;

class TpayPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $data;
    public $display_column_left = false;

    /**
     * @throws PrestaShopException
     */
    public function initContent()
    {
        parent::initContent();

        $cart = $this->module->getContext()->cart;
        $this->data = Tools::getAllValues();
        $this->updateLang($cart);

        if (empty($cart->id)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        if (true === $cart->orderExists()) {
            exit(
            $this->l('Cart cannot be loaded or an order has already been placed using this cart')
            );
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $orderTotal = $this->getOrderTotal($cart);

        $this->processPayment(
            $orderTotal,
            $customer,
            $cart
        );

        $this->setTemplate(Config::TPAY_PATH . '/redirect.tpl');
    }

    private function processPayment($orderTotal, $customer, $cart)
    {
        try {
            $this->validateUserOrder($orderTotal, $customer);
            $this->module->getContext()->smarty->assign(['nbProducts' => $cart->nbProducts()]);

            $paymentType = PaymentFactory::getPaymentMethod(Tools::getValue('type'));
            $order = new Order($this->module->currentOrder);
            $invoiceAddressId = $this->module->getContext()->cart->id_address_invoice;
            $address = new AddressCore($invoiceAddressId);

            $controller = new PaymentHandler(
                $this->module,
                $paymentType,
                $order,
                $customer,
                $address,
                $this->data
            );
            $controller->get();
        } catch (\Exception $e) {
            ExceptionHandler::handle($e);
        }
    }

    /**
     * Validate current order
     *
     * @param float $orderTotal
     * @param $customer
     *
     * @return void
     */
    private function validateUserOrder(float $orderTotal, $customer): void
    {
        $this->module->validateOrder(
            (int)$this->module->getContext()->cart->id,
            (int)Cfg::get('TPAY_PENDING'),
            $orderTotal,
            $this->module->displayName,
            null,
            [],
            (int)$this->module->getContext()->currency->id,
            $customer->isGuest() ? 0 : 1,
            $customer->secure_key
        );
    }

    private function getOrderTotal($cart): float
    {
        $surcharge = new SurchargeService();
        $orderTotal = $cart->getOrderTotal();
        $surchargeTotal = $surcharge->getSurchargeValue($orderTotal);
        return (float)$orderTotal + $surchargeTotal;
    }

    private function updateLang(Cart $cart): void
    {
        if (!isset($this->data['isolang'])) {
            $this->data['isolang'] = $cart->getAssociatedLanguage()->getIsoCode();
        }
    }
}
