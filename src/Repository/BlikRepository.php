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
use Tpay\Entity\TpayBlik;
use Tpay\Exception\BaseException;
use Tpay\Exception\RepositoryException;
use Tpay\Handler\RepositoryQueryHandler;

class BlikRepository
{
    public const TABLE = 'tpay_blik';

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
     * @throws RepositoryException
     * @throws BaseException
     */
    public function getBlikAliasIdByUserId($userId)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->addSelect('alias')
            ->from($this->dbPrefix.self::TABLE, 't')
            ->andWhere('t.user_id = :userId')

            ->setParameter('userId', (int) $userId);

        return $this->repositoryQueryHandler->execute($qb, 'Get blik Alias', 'fetchColumn');
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function saveBlikAlias($userId, $alias): void
    {
        $aliasValue = new TpayBlik(
            $userId,
            $alias
        );

        $this->entityManager->persist($aliasValue);
        $this->entityManager->flush();
    }

    /**
     * @throws RepositoryException
     * @throws BaseException
     */
    public function removeBlikAlias($userId, $alias): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->delete($this->dbPrefix.self::TABLE)
            ->where('user_id = :userId')
            ->andWhere('alias = :alias')
            ->setParameter('userId', $userId)
            ->setParameter('alias', $alias);
        $this->repositoryQueryHandler->execute($qb, 'Delete alias error');
    }
}
