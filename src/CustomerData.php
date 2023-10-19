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

namespace Tpay;

use Configuration as Cfg;
use Tpay\Service\SurchargeService;

class CustomerData
{
    /**
     * @var \AddressCore
     */
    private $address;

    /**
     * @var \Customer
     */
    private $customer;

    /**
     * @var \Context
     */
    private $context;

    /**
     * @var \Cart
     */
    private $cart;


    private $customerDetails;
    /**
     * @var \Order
     */
    private $order;

    /**
     * @throws \Exception
     */
    public function __construct(
        \AddressCore $address,
        \Customer    $customer,
        \Context     $context,
        \Cart        $cart,
        \Order       $order
    )
    {
        $this->address = $address;
        $this->customer = $customer;
        $this->context = $context;
        $this->cart = $cart;
        $this->order = $order;

        $this->setBasicClient();
    }


    public function getData()
    {
        return $this->customerDetails;
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function getOrderTotalAmount(): string
    {
        $surcharge = new SurchargeService();
        $orderTotal = $this->cart->getOrderTotal();
        $surchargeTotal = $surcharge->getSurchargeValue($orderTotal);
        $total = $orderTotal + $surchargeTotal;

        return (string)$total;
    }


    /**
     * Create basic client data
     *
     * @return void
     * @throws \Exception
     */
    private function setBasicClient(): void
    {
        $orderTotal = $this->getOrderTotalAmount();

        $data = [
            'amount' => number_format(
                (float)str_replace(
                    [',', ' ',],
                    ['.', '',],
                    (string)$orderTotal
                ),
                2,
                '.',
                ''
            ),
            'hiddenDescription' => $this->createCrc(),
            'payer' => [
                'email' => $this->context->cookie->email,
                'name' => sprintf(
                    '%s %s',
                    $this->context->cookie->customer_firstname,
                    $this->context->cookie->customer_lastname
                ),
                'phone' => $this->address->phone ?: '000',
                'address' => $this->address->address1 . ' ' . $this->address->address2,
                'code' => $this->address->postcode,
                'city' => $this->address->city,
                'country' => 'PL',
            ],
        ];

        foreach ($data as $key => $value) {
            if (empty($value)) {
                unset($data[$key]);
            }
        }

        $this->customerDetails = $data;
    }


    public function createDescription($order = null)
    {
        $context = $this->context;
        $customer = $context->customer;
        $firstName = $customer->firstname;
        $lastName = $customer->lastname;

        $context->cookie->customer_firstname = $firstName;
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
                        'order_id' => $order->id
                    ]
                ),
                'error' => $this->context->link->getModuleLink(
                    'tpay',
                    'ordererror',
                    [
                        'type' => $type,
                        'order_id' => $order->id
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
     * Create crc
     *
     * @param $context
     *
     * @return string
     */
    private function createCrc(): string
    {
        $order = $this->order;
        $customer = $this->customer;

        switch (\Configuration::get('TPAY_CRC_FORM')) {
            case 'order_id_and_rest':
                return $order->id . '-' . md5($customer->secure_key . time());
            case 'order_id':
                return (string)$order->id;
            case 'md5_all':
            default:
                return md5($order->id . $customer->secure_key . time());
        }
    }
}
