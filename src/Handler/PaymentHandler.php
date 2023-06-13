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

namespace Tpay\Handler;

use AddressCore;
use Context;
use Customer;
use Order;
use Tpay\CustomerData;

class PaymentHandler
{
    /**
     * @var Tpay
     */
    private $module;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var PaymentMethodHandler
     */
    private $paymentMethodHandler;
    /**
     * @var Customer
     */
    private $customer;
    /**
     * @var Order
     */
    private $order;
    /**
     * @var AddressCore
     */
    private $address;
    /**
     * @var array
     */
    private $data;

    public function __construct(
        \Module $module,
        PaymentMethodHandler $paymentMethodHandler,
        Order $order,
        Customer $customer,
        AddressCore $address,
        array $data = []
    ) {

        $this->module = $module;
        $this->paymentMethodHandler = $paymentMethodHandler;
        $this->order = $order;
        $this->customer = $customer;
        $this->address = $address;
        $this->data = $data;
        $this->context = $this->module->getContext();
    }

    /**
     * @throws \Exception
     * @return array
     */
    public function getCustomerDetails(): array
    {
        $customer = new CustomerData($this->address, $this->customer, $this->context, $this->context->cart);
        $customer->createCallbacks($this->order, $this->paymentMethodHandler->getName());
        $customer->createDescription($this->order);

        return $customer->getData();
    }

    /**
     * @throws \Exception
     */
    public function get()
    {
        $this->getPayment($this->paymentMethodHandler);
    }

    /**
     * @param PaymentMethodHandler $method
     *
     * @throws \Exception
     * @return mixed
     */
    public function getPayment(PaymentMethodHandler $method)
    {
        return $method->createPayment(
            $this->module,
            $this->order,
            $this->customer,
            $this->context,
            $this->getCustomerDetails(),
            $this->data
        );
    }
}
