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
SOFTWARE.*/

declare(strict_types=1);

namespace Tpay\Service;

use Configuration as Cfg;
use PrestaShopLogger;
use Tpay;
use Tpay\Repository\TransactionsRepository;

class AutoCancelService
{
    /** @var TransactionsRepository */
    private $repository;

    /** @var Tpay */
    private $tpay;

    public function __construct(
        TransactionsRepository $repository,
        Tpay $tpay
    ) {
        $this->repository = $repository;
        $this->tpay = $tpay;
    }

    public function cancelTransactions()
    {
        // canceled

        $orderIds = [];
        $transactionIds = [];

        $timespan = (int) Cfg::get('TPAY_AUTO_CANCEL_DAYS');
        if ($timespan <= 0) {
            $timespan = 7;
        }
        $transactions = $this->repository->getTransactionsQualifiedToCancel($timespan);

        foreach ($transactions as $transaction) {
            if (!$transaction['valid']) {
                $orderIds[] = $transaction['id_order'];
            }
            $transactionIds[] = $transaction['transaction_id'];
        }

        foreach ($transactionIds as $transactionId) {
            $this->tpay->api()->transactions()->cancelTransaction($transactionId);
            $this->repository->updateTransactionStatusByTransactionId($transactionId, 'canceled');
        }

        if ($orderIds) {
            $commandName = null;
            $handler = null;

            if (class_exists('PrestaShop\PrestaShop\Core\Domain\Order\Command\BulkChangeOrderStatusCommand')) {
                $commandName = 'PrestaShop\PrestaShop\Core\Domain\Order\Command\BulkChangeOrderStatusCommand';
                $handler = 'PrestaShop\PrestaShop\Adapter\Order\CommandHandler\BulkChangeOrderStatusHandler';
            }
            if (!$commandName && class_exists('PrestaShop\PrestaShop\Core\Domain\Order\Command\ChangeOrdersStatusCommand')) {
                $commandName = 'PrestaShop\PrestaShop\Core\Domain\Order\Command\ChangeOrdersStatusCommand';
                $handler = 'PrestaShop\PrestaShop\Adapter\Order\CommandHandler\ChangeOrdersStatusHandler';
            }

            if ($commandName) {
                $handler = new $handler();
                $handler->handle(
                    // @phpstan-ignore-next-line
                    new $commandName($orderIds, (int) Cfg::get('PS_OS_CANCELED'))
                );
            } else {
                PrestaShopLogger::addLog('Class for handling order cancellation not found', 3);
            }
        }
    }
}
