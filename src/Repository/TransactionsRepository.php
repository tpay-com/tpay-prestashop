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

namespace Tpay\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Tpay\Entity\TpayTransaction;
use Tpay\Exception\BaseException;
use Tpay\Exception\RepositoryException;
use Tpay\Handler\RepositoryQueryHandler;

class TransactionsRepository
{
    public const TABLE = 'tpay_transaction';

    /**
     * @var Connection the Database connection
     */
    private $connection;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var RepositoryQueryHandler
     */
    private $repositoryQueryHandler;

    /**
     * @var string the Database prefix
     */
    private $dbPrefix;

    /**
     * @param Connection $connection
     * @param EntityManager $entityManager
     * @param RepositoryQueryHandler $repositoryQueryHandler
     * @param string $dbPrefix
     */
    public function __construct(
        Connection $connection,
        EntityManager $entityManager,
        RepositoryQueryHandler $repositoryQueryHandler,
        string $dbPrefix
    ) {
        $this->connection = $connection;
        $this->entityManager = $entityManager;
        $this->repositoryQueryHandler = $repositoryQueryHandler;
        $this->dbPrefix = $dbPrefix;
    }

    /**
     * @throws RepositoryException|BaseException
     */
    public function getTransactionIdByOrderId($orderId)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->addSelect('transaction_id')
            ->from($this->dbPrefix . self::TABLE, 't')
            ->andWhere('t.order_id = :orderId')
            ->setParameter('orderId', (int) $orderId);

        return $this->repositoryQueryHandler->execute($qb, 'Error get transaction by order id', 'fetchColumn');
    }


    /**
     * @throws RepositoryException|BaseException
     */
    public function getSurchargeValueByOrderId($orderId)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->addSelect('surcharge')
            ->from($this->dbPrefix . self::TABLE, 't')
            ->andWhere('t.order_id = :orderId')
            ->setParameter('orderId', (int) $orderId);

        return $this->repositoryQueryHandler->execute($qb, 'Error get surcharge by order id', 'fetchColumn');
    }

    /**
     * @throws RepositoryException|BaseException
     */
    public function getTransactionByCrc($crc)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->addSelect('*')
            ->from($this->dbPrefix . self::TABLE, 't')
            ->andWhere('t.crc = :crc')
            ->setParameter('crc', $crc);

        return $this->repositoryQueryHandler->execute($qb, 'Error get transaction by crc', 'fetch');
    }

    /**
     * @throws RepositoryException|BaseException
     */
    public function getTransactionByTransactionId($transactionId)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->addSelect('*')
            ->from($this->dbPrefix . self::TABLE, 't')
            ->andWhere('t.transaction_id = :transactionId')
            ->setParameter('transactionId', $transactionId);

        return $this->repositoryQueryHandler->execute($qb, 'Error get transaction by transaction id', 'fetch');
    }

    /**
     * @throws RepositoryException|BaseException
     */
    public function getPaymentType($orderId)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->addSelect('payment_type')
            ->from($this->dbPrefix . self::TABLE, 'r')
            ->andWhere('r.order_id = :orderId')
            ->setParameter('orderId', (int) $orderId);
        return $this->repositoryQueryHandler->execute($qb, 'Error get payment type', 'fetchColumn');
    }

    public function getOrderStatusHistory($orderId): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->addSelect('id_order_state')
            ->from($this->dbPrefix . 'order_history', 'cc')
            ->andWhere('cc.id_order = :orderId')
            ->setParameter('orderId', $orderId);

        $rows = $qb->execute()->fetchAll();

        $statuses = [];
        foreach ($rows as $value) {
            foreach ($value as $statusID) {
                $statuses[] = $statusID;
            }
        }

        return $statuses;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function processCreateTransaction(
        $orderId,
        $crc,
        $transactionId,
        $type,
        $registerUser,
        $surcharge,
        $status
    ): void {
        $transaction = new TpayTransaction(
            $orderId,
            $crc,
            $transactionId,
            $type,
            $registerUser,
            $surcharge,
            $status
        );

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();
    }

    /**
     * Update transaction status
     *
     * @param string $crc
     * @param string $status
     *
     * @throws RepositoryException
     * @throws BaseException
     * @return void
     */
    public function updateTransactionStatus(string $crc, string $status): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->update($this->dbPrefix . self::TABLE)
            ->set('status', ':status')
            ->andWhere('crc = :crc')
            ->setParameter('status', $status)
            ->setParameter('crc', $crc);
        $this->repositoryQueryHandler->execute($qb, 'Update transaction status error');
    }

    /**
     * @throws BaseException
     * @throws RepositoryException
     */
    public function setTransactionOrderId(string $transactionId, int $orderId): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->update($this->dbPrefix . self::TABLE)
            ->set('order_id', ':orderId')
            ->andWhere('transaction_id = :transactionId')
            ->setParameter('orderId', $orderId)
            ->setParameter('transactionId', $transactionId);
        $this->repositoryQueryHandler->execute($qb, 'Update transaction order id error');
    }
}
