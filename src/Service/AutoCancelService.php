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
use PrestaShop\PrestaShop\Core\Domain\Order\Command\BulkChangeOrderStatusCommand;
use Tpay\Repository\TransactionsRepository;

class AutoCancelService
{

    /**
     * @var TransactionsRepository
     */
    private $repository;

    /**
     * @var \OrderHistory
     */
    private $orderHistory;
    /**
     * @var CommandBusInterface
     */
    private $commandBus;
    /**
     * @var \Tpay
     */
    private $tpay;


    public function __construct(
        TransactionsRepository $repository,
        CommandBusInterface $commandBus,
        \Tpay $tpay
    ) {
        $this->repository = $repository;
        $this->commandBus = $commandBus;
        $this->tpay = $tpay;
    }

    public function cancelTransactions()
    {
        //canceled

        $orderIds = [];


        if ($orderIds) {
            $this->commandBus->handle(
                new BulkChangeOrderStatusCommand($orderIds, (int)Cfg::get('PS_OS_CANCELED'))
            );
        }
    }


}
