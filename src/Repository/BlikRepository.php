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
     * @throws RepositoryException
     * @throws BaseException
     */
    public function getBlikAliasIdByUserId($userId)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->addSelect('alias')
            ->from($this->dbPrefix . self::TABLE, 't')
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
            ->delete($this->dbPrefix . self::TABLE)
            ->where('user_id = :userId')
            ->andWhere('alias = :alias')
            ->setParameter('userId', $userId)
            ->setParameter('alias', $alias);
        $this->repositoryQueryHandler->execute($qb, 'Delete alias error');
    }
}
