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

use Configuration as Cfg;
use Order;
use OrderHistory;
use Tpay\Repository\TransactionsRepository;

class OrderStatusHandler
{
    /**
     * @var OrderHistory
     */
    public $orderHistory;

    /**
     * @var transactionsRepository
     */
    private $transactionsRepository;

    public function __construct(
        OrderHistory $orderHistory,
        transactionsRepository $transactionsRepository
    ) {
        $this->orderHistory = $orderHistory;
        $this->transactionsRepository = $transactionsRepository;
    }

    /**
     * Update orders statuses.
     * @param Order $order
     * @param string $tpayPaymentId
     * @param bool $error
     * @return void
     */
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

    /**
     * Update order status.
     * @param Order $order
     * @param string $tpayPaymentId
     * @param bool $error
     * @return void
     */
    private function changeOrderStatus(Order $order, string $tpayPaymentId, bool $error = false): void
    {
        $orderStateId = !$error ? Cfg::get('TPAY_CONFIRMED') : Cfg::get('TPAY_ERROR');
        $orderStatusesHistory = $this->transactionsRepository->getOrderStatusHistory($order->id);
        if (!in_array($orderStateId, $orderStatusesHistory)) {
            if (!$error) {
                $order->addOrderPayment($order->getOrdersTotalPaid(), 'Tpay', $tpayPaymentId);
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
}
