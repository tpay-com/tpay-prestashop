<?php
/**
 * @author Krajowy Integrator Płatności S.A.
 * @copyright Krajowy Integrator Płatności S.A.
 * @license MIT
 *
 * Copyright (c) 2026 Krajowy Integrator Płatności S.A.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Tpay\Handler;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Tpay\CustomerData;

class PaymentHandler
{
    /** @var \Tpay */
    private $module;

    /** @var \Context */
    private $context;

    /** @var PaymentMethodHandler */
    private $paymentMethodHandler;

    /** @var \Customer */
    private $customer;

    /** @var \Order */
    private $order;

    /** @var \AddressCore */
    private $address;

    /** @var array */
    private $data;

    public function __construct(\Tpay $module, PaymentMethodHandler $paymentMethodHandler, \Order $order, \Customer $customer, \AddressCore $address, array $data = [])
    {
        $this->module = $module;
        $this->paymentMethodHandler = $paymentMethodHandler;
        $this->order = $order;
        $this->customer = $customer;
        $this->address = $address;
        $this->data = $data;
        $this->context = $this->module->getContext();
    }

    /** @throws \Exception */
    public function getCustomerDetails(): array
    {
        $cart = new \Cart($this->order->id_cart);

        $customer = new CustomerData(
            $this->address,
            $this->customer,
            $this->context,
            $cart,
            $this->order
        );
        $customer->createCallbacks($this->order, $this->paymentMethodHandler->getName());
        $customer->createDescription($this->order);

        return $customer->getData();
    }

    /** @throws \Exception */
    public function get(): void
    {
        $this->paymentMethodHandler->createPayment(
            $this->module,
            $this->order,
            $this->customer,
            $this->context,
            $this->getCustomerDetails(),
            $this->data
        );
    }
}
