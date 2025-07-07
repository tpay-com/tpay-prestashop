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

use Configuration as Cfg;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use Tpay\Repository\TransactionsRepository;

class AutoCancelService
{

    /**
     * @var TransactionsRepository
     */
    private $repository;

    /**
     * @var \Tpay
     */
    private $tpay;


    public function __construct(
        TransactionsRepository $repository,
        \Tpay $tpay
    ) {
        $this->repository = $repository;
        $this->tpay = $tpay;
    }

    public function cancelTransactions()
    {
        //canceled

        $orderIds = [];
        $transactionIds = [];

        $timespan = (int)Cfg::get('TPAY_AUTO_CANCEL_DAYS');
        if($timespan <= 0){
            $timespan = 7;
        }
        $transactions = $this->repository->getTransactionsQualifiedToCancel($timespan);

        foreach($transactions as $transaction){
            if(!$transaction['valid']){
                $orderIds[] = $transaction['id_order'];
            }
            $transactionIds[] = $transaction['transaction_id'];
        }

        foreach($transactionIds as $transactionId){
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
                    new $commandName($orderIds, (int)Cfg::get('PS_OS_CANCELED'))
                );
            } else {
                \PrestaShopLogger::addLog('Class for handling order cancellation not found', 3);
            }
        }
    }


}
