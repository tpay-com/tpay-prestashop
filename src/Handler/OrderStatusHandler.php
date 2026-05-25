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

use Configuration as Cfg;
use Order;
use OrderHistory;
use Tpay\Repository\TransactionsRepository;

class OrderStatusHandler
{
    /** @var OrderHistory */
    public $orderHistory;

    /** @var TransactionsRepository */
    private $transactionsRepository;

    public function __construct(
        OrderHistory $orderHistory,
        TransactionsRepository $transactionsRepository
    ) {
        $this->orderHistory = $orderHistory;
        $this->transactionsRepository = $transactionsRepository;
    }

    /** Update orders statuses. */
    public function setOrdersAsConfirmed(Order $order, string $tpayPaymentId, bool $error = false): void
    {
        $reference = $order->reference;
        $referencedOrders = Order::getByReference($reference)->getResults();
        foreach ($referencedOrders as $orderObject) {
            if (!is_null($orderObject->id)) {
                $this->changeOrderStatus($orderObject, $tpayPaymentId, $error);
            }
        }
    }

    /** Update order status. */
    private function changeOrderStatus(Order $order, string $tpayPaymentId, bool $error = false): void
    {
        $orderStateId = $this->getOrderStatus($order, $error);
        $orderStatusesHistory = $this->transactionsRepository->getOrderStatusHistory($order->id);
        if (!in_array($orderStateId, $orderStatusesHistory)) {
            if (!$error) {
                $order->addOrderPayment((string) $order->getOrdersTotalPaid(), 'Tpay', $tpayPaymentId);
            }
            $this->orderHistory->id_order = $order->id;
            $this->orderHistory->changeIdOrderState(
                (int) $orderStateId,
                (int) $order->id,
                true
            );
            $this->orderHistory->addWithemail(true);
        }
    }

    private function getOrderStatus(Order $order, bool $error = false): string
    {
        $isVirtual = true;
        foreach ($order->getProducts() as $product) {
            if (!$product['is_virtual']) {
                $isVirtual = false;
                break;
            }
        }

        return !$error ? Cfg::get($isVirtual ? 'TPAY_VIRTUAL_CONFIRMED' : 'TPAY_CONFIRMED') : Cfg::get('TPAY_ERROR');
    }
}
