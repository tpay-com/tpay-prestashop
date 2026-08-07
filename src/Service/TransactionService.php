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

namespace Tpay\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Tpay\Repository\TransactionsRepository;

class TransactionService
{
    /** @var \Cart */
    private $cart;

    /** @var TransactionsRepository */
    private $repository;

    /** @var SurchargeService */
    private $surchargeService;

    /** @var \Context */
    private $context;

    public function __construct(\Cart $cart, TransactionsRepository $repository, SurchargeService $surchargeService, \Context $context)
    {
        $this->cart = $cart;
        $this->repository = $repository;
        $this->surchargeService = $surchargeService;
        $this->context = $context;
    }

    /**
     * @param bool $redirect (redirect use new card/payment basic)
     *
     * @throws \Exception
     */
    public function transactionProcess($transaction, $type, $orderId, bool $redirect = true): void
    {
        if ('blik' !== $type && 'success' !== $transaction['result']) {
            \Tools::redirect(
                $this->context->link->getModuleLink(
                    'tpay',
                    'ordererror'
                )
            );
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
            \Tools::redirect($transaction['transactionPaymentUrl']);
        }
    }

    /**
     * @throws \Exception
     */
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
