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

namespace Tpay\Service;

use Tpay\Repository\TransactionsRepository;
use Tools;

class TransactionService
{
    /**
     * @var \Cart
     */
    private $cart;
    /**
     * @var TransactionsRepository
     */
    private $repository;
    /**
     * @var SurchargeService
     */
    private $surchargeService;
    /**
     * @var \Context
     */
    private $context;

    public function __construct(
        \Cart $cart,
        TransactionsRepository $repository,
        SurchargeService $surchargeService,
        \Context $context
    ) {
        $this->cart = $cart;
        $this->repository = $repository;
        $this->surchargeService = $surchargeService;
        $this->context = $context;
    }

    /**
     * @param $transaction
     * @param $type
     * @param $orderId
     * @param bool $redirect (redirect use new card/payment basic)
     *
     * @throws \Exception
     */
    public function transactionProcess($transaction, $type, $orderId, bool $redirect = true): void
    {
        if ($type !== 'blik' && $transaction['result'] !== 'success') {
            Tools::redirect($this->context->link->getModuleLink(
                'tpay',
                'ordererror'
            ));
        }

        $orderTotal = (float) $this->cart->getOrderTotal();
        $registeredUser = $this->context->customer->isGuest() ? 0 : 1;

        $this->repository->processCreateTransaction(
            $orderId,
            $transaction['hiddenDescription'],
            $transaction['transactionId'],
            $type,
            $registeredUser,
            $this->surchargeService->getSurchargeValue($orderTotal),
            $transaction['status']
        );

        if ($redirect) {
            Tools::redirect($transaction['transactionPaymentUrl']);
        }
    }

    /** @throws \Exception */
    public function updateTransaction($transaction, $oldTransactionId, $type, $orderId): void
    {
        $this->repository->updateTransaction(
            $orderId,
            $oldTransactionId,
            $transaction['hiddenDescription'],
            $transaction['transactionId'],
            $type
        );
    }
}
