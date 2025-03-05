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
use Tpay\Entity\TpayRefund;
use Tpay\Exception\BaseException;
use Tpay\Exception\RepositoryException;
use Tpay\Handler\RepositoryQueryHandler;

class RefundsRepository
{
    public const TABLE = 'tpay_refund';

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
     * Refund
     *
     * @param $orderId
     * @param $transactionId
     * @param $amount
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @return void
     */
    public function insertRefund($orderId, $transactionId, $amount): void
    {
        $transaction = new TpayRefund(
            (int) $orderId,
            (string) $transactionId,
            (float) $amount
        );

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();
    }

    /**
     * @throws RepositoryException|BaseException
     */
    public function getOrderRefunds($orderId)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->addSelect('*')
            ->from($this->dbPrefix . self::TABLE, 'cc')
            ->andWhere('cc.order_id = :orderId')
            ->setParameter('orderId', (int) $orderId);

        return $this->repositoryQueryHandler->execute($qb, 'Error order refund', 'fetchAll');
    }
}
