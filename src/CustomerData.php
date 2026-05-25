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

namespace Tpay;

use AddressCore;
use Cart;
use Configuration as Cfg;
use Context;
use Customer;
use Exception;
use Order;
use PrestaShopDatabaseException;
use PrestaShopException;
use Tools;
use Tpay\Builder\PayerDataBuilder;
use Tpay\Service\SurchargeService;

class CustomerData
{
    /** @var AddressCore */
    private $address;

    /** @var Customer */
    private $customer;

    /** @var Context */
    private $context;

    /** @var Cart */
    private $cart;

    /** @var array<string, mixed> */
    private $customerDetails = [];

    /** @var Order */
    private $order;

    /** @throws Exception */
    public function __construct(
        AddressCore $address,
        Customer $customer,
        Context $context,
        Cart $cart,
        Order $order
    ) {
        $this->address = $address;
        $this->customer = $customer;
        $this->context = $context;
        $this->cart = $cart;
        $this->order = $order;

        $this->setBasicClient();
    }

    public function getData(): array
    {
        return $this->customerDetails;
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Exception
     */
    public function getOrderTotalAmount(): string
    {
        $surcharge = new SurchargeService();
        $orderTotal = $this->cart->getOrderTotal();
        $surchargeTotal = $surcharge->getSurchargeValue($orderTotal);
        $total = $orderTotal + $surchargeTotal;

        return (string) $total;
    }

    public function createDescription($order = null)
    {
        $context = $this->context;
        $customer = $context->customer;
        $firstName = $customer->firstname;
        $lastName = $customer->lastname;

        // @phpstan-ignore-next-line
        $context->cookie->customer_firstname = $firstName;
        // @phpstan-ignore-next-line
        $context->cookie->customer_lastname = $lastName;

        $this->customerDetails['description'] = '#BLIK - ' . $firstName . ' ' . $lastName;

        if ($order) {
            $reference = $order->reference;
            $this->customerDetails['description'] = $this->getCustomerTitle($reference, $context->cookie);
        }
    }

    public function createCallbacks($order, $type): void
    {
        $data = [
            'payerUrls' => [
                'success' => $this->context->link->getModuleLink(
                    'tpay',
                    'confirmation',
                    [
                        'type' => $type,
                        'order_id' => $order->id,
                    ]
                ),
                'error' => $this->context->link->getModuleLink(
                    'tpay',
                    'ordererror',
                    [
                        'type' => $type,
                        'order_id' => $order->id,
                    ]
                ),
            ],
            'notification' => [
                'url' => $this->context->link->getModuleLink('tpay', 'notifications'),
            ],
        ];

        if (Cfg::get('TPAY_NOTIFICATION_EMAILS')) {
            $data['notification']['email'] = Cfg::get('TPAY_NOTIFICATION_EMAILS');
        }

        $this->customerDetails['callbacks'] = $data;
    }

    public function createBlikCallbacks($type): void
    {
        $data = [
            'payerUrls' => [
                'success' => $this->context->link->getModuleLink(
                    'tpay',
                    'confirmation',
                    [
                        'type' => $type,
                    ]
                ),
                'error' => $this->context->link->getModuleLink(
                    'tpay',
                    'ordererror',
                    [
                        'type' => $type,
                    ]
                ),
            ],
            'notification' => [
                'url' => $this->context->link->getModuleLink('tpay', 'notifications'),
            ],
        ];

        if (Cfg::get('TPAY_NOTIFICATION_EMAILS')) {
            $data['notification']['email'] = Cfg::get('TPAY_NOTIFICATION_EMAILS');
        }

        $this->customerDetails['callbacks'] = $data;
    }

    public function getCustomerTitle($reference, $context): string
    {
        return '#' . $reference . ' - ' . $context->customer_firstname . ' ' . $context->customer_lastname;
    }

    /**
     * Create basic client data
     *
     * @throws Exception
     */
    private function setBasicClient(): void
    {
        $orderTotal = $this->getOrderTotalAmount();
        $phoneNumber = (!empty($this->address->phone_mobile) && strlen($this->address->phone_mobile) > 3) ? $this->address->phone_mobile : ($this->address->phone ?: '000');
        $email = $this->context->cookie->email ?? $this->customer->email;
        $customerFirstName = !empty($this->context->cookie->customer_firstname) ? $this->context->cookie->customer_firstname : $this->customer->firstname;
        $customerLastName = !empty($this->context->cookie->customer_lastname) ? $this->context->cookie->customer_lastname : $this->customer->lastname;

        $payer = (new PayerDataBuilder())
            ->set('email', $email)
            ->set('name', sprintf('%s %s', $customerFirstName, $customerLastName))
            ->add('phone', $phoneNumber)
            ->add('address', trim($this->address->address1 . ' ' . $this->address->address2))
            ->add('code', $this->address->postcode)
            ->add('city', $this->address->city)
            ->set('country', 'PL')
            ->set('ip', Tools::getRemoteAddr())
            ->set('userAgent', substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255))
            ->get();

        $data = [
            'amount' => number_format(
                (float) str_replace(
                    [',', ' '],
                    ['.', ''],
                    (string) $orderTotal
                ),
                2,
                '.',
                ''
            ),
            'hiddenDescription' => $this->createCrc(),
            'payer' => $payer,
        ];

        if (!empty($this->address->vat_number) && strlen($this->address->vat_number) > 1) {
            $data['payer']['taxId'] = $this->address->vat_number;
        }

        foreach ($data as $key => $value) {
            if (empty($value)) {
                unset($data[$key]);
            }
        }

        $this->customerDetails = $data;
    }

    /** Create crc */
    private function createCrc(): string
    {
        $order = $this->order;
        $customer = $this->customer;

        switch (Cfg::get('TPAY_CRC_FORM')) {
            case 'order_id_and_rest':
                return $order->id . '-' . md5($customer->secure_key . time());
            case 'order_id':
                return (string) $order->id;
            case 'md5_all':
            default:
                return md5($order->id . $customer->secure_key . time());
        }
    }
}
