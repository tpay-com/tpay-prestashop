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

namespace Tpay\Repository;

use DateTime;
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

    /** @var Connection the Database connection */
    private $connection;

    /** @var EntityManager */
    private $entityManager;

    /** @var RepositoryQueryHandler */
    private $repositoryQueryHandler;

    /** @var string the Database prefix */
    private $dbPrefix;

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
     * @throws BaseException|RepositoryException
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
     * @throws BaseException|RepositoryException
     */
    public function getTransactionByOrderId($orderId)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->addSelect('*')
            ->from($this->dbPrefix . self::TABLE, 't')
            ->andWhere('t.order_id = :orderId')
            ->setParameter('orderId', (int) $orderId);

        return $this->repositoryQueryHandler->execute($qb, 'Error get transaction by transaction id', 'fetch');
    }

    /**
     * @throws BaseException|RepositoryException
     */
    public function getTransactionsQualifiedToCancel($timegapInDays)
    {
        $date = new DateTime('now -' . ((int) $timegapInDays) . ' days');
        $dateMin = clone $date;
        $dateMin->modify('-1 day');
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->addSelect('distinct o.id_order, o.valid, t.transaction_id')
            ->from($this->dbPrefix . self::TABLE, 't')
            ->join('t', $this->dbPrefix . 'orders', 'o', 't.order_id = o.id_order')
            ->join('t', $this->dbPrefix . 'order_state', 'os', 't.order_id = o.id_order')
            ->andWhere('o.date_add >= :dateMin')
            ->andWhere('o.date_add <= :dateMax')
            ->andWhere('t.status = "pending"')
            ->setParameter('dateMin', $dateMin->format('Y-m-d 00:00:00'))
            ->setParameter('dateMax', $date->format('Y-m-d H:i:s'));

        return $this->repositoryQueryHandler->execute($qb, 'Error get transaction qualified to cancel', 'fetchAll');
    }

    /**
     * @throws BaseException|RepositoryException
     */
    public function updateTransaction($orderId, $oldTransactionId, $crc, $transactionId, $paymentType)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->update($this->dbPrefix . self::TABLE)
            ->set('crc', ':crc')
            ->set('transaction_id', ':transactionId')
            ->set('payment_type', ':paymentType')
            ->andWhere('order_id = :orderId')
            ->andWhere('transaction_id = :oldTransactionId')
            ->setParameter('crc', $crc)
            ->setParameter('transactionId', $transactionId)
            ->setParameter('paymentType', $paymentType)
            ->setParameter('orderId', $orderId)
            ->setParameter('oldTransactionId', $oldTransactionId);

        $this->repositoryQueryHandler->execute($qb, 'Update transaction status error');
    }

    /**
     * @throws BaseException|RepositoryException
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
     * @throws BaseException|RepositoryException
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
     * @throws BaseException|RepositoryException
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
     * @throws BaseException|RepositoryException
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
     * @throws RepositoryException
     * @throws BaseException
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
     * Update transaction status
     *
     * @throws RepositoryException
     * @throws BaseException
     */
    public function updateTransactionStatusByTransactionId(string $transactionId, string $status): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->update($this->dbPrefix . self::TABLE)
            ->set('status', ':status')
            ->andWhere('transaction_id = :transaction_id')
            ->setParameter('status', $status)
            ->setParameter('transaction_id', $transactionId);
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
