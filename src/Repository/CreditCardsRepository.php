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

if (!defined('_PS_VERSION_')) {
    exit;
}

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Tpay\Entity\TpayCreditCard;
use Tpay\Exception\BaseException;
use Tpay\Exception\RepositoryException;
use Tpay\Handler\RepositoryQueryHandler;

class CreditCardsRepository
{
    public const TABLE = 'tpay_credit_card';

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
     * Get all credit cards by user id
     *
     * @return array
     *
     * @throws RepositoryException
     * @throws BaseException
     */
    public function getAllCreditCardsByUserId($userId): array
    {
        if (!$userId) {
            return [];
        }

        $qb = $this->connection->createQueryBuilder();
        $qb
            ->addSelect('*')
            ->from($this->dbPrefix . self::TABLE, 'cc')
            ->andWhere('cc.user_id = :userId')
            ->andWhere('cc.card_token != ""')
            ->andWhere('cc.card_token IS NOT NULL')
            ->addOrderBy('cc.date_update', 'DESC');
        $qb->setParameter('userId', $userId);

        $result = $this->repositoryQueryHandler->execute($qb, 'Get credit card by user id', 'fetchAll');

        return is_array($result) ? $result : [];
    }

    /**
     * Get credit card by card_hash
     *
     * @return int|Statement
     *
     * @throws BaseException
     * @throws RepositoryException
     */
    public function getCreditCardByCardHashAndCrc(string $cardHash, string $crc)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->addSelect('*')
            ->from($this->dbPrefix . self::TABLE, 'cc')
            ->andWhere('cc.card_hash = :cardHash')
            ->andWhere('cc.crc = :crc');
        $qb->setParameter('cardHash', $cardHash);
        $qb->setParameter('crc', $crc);

        return $this->repositoryQueryHandler->execute($qb, 'Get credit card by hash', 'fetchAll');
    }

    /**
     * Get credit card by crc
     *
     * @return int|Statement
     *
     * @throws BaseException
     * @throws RepositoryException
     */
    public function getCreditCardTokenByCardCrc(string $crc)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->addSelect('card_token')
            ->from($this->dbPrefix . self::TABLE, 'cc')
            ->andWhere('cc.crc = :crc');

        $qb->setParameter('crc', $crc);

        return $this->repositoryQueryHandler->execute($qb, 'Get credit card crc', 'fetchColumn');
    }

    /**
     * Delete selected card
     *
     * @throws BaseException|RepositoryException
     */
    public function deleteCard(int $id, int $customerId): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->delete($this->dbPrefix . self::TABLE)
            ->where('id = :id')
            ->andWhere('user_id = :userId')
            ->setParameter('id', $id)
            ->setParameter('userId', $customerId);
        $this->repositoryQueryHandler->execute($qb, 'Delete error');
    }

    /**
     * Update card token after successful transaction
     *
     * @throws BaseException|RepositoryException
     */
    public function updateToken(string $crc, string $cardToken): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->update($this->dbPrefix . self::TABLE)
            ->set('card_token', ':cardToken')
            ->andWhere('crc = :crc')
            ->setParameter('cardToken', $cardToken)
            ->setParameter('crc', $crc);
        $this->repositoryQueryHandler->execute($qb, 'Update token error');
    }

    /**
     * Update the card
     *
     * @throws BaseException|RepositoryException
     */
    public function updateCard(string $cardHash, string $crc): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->update($this->dbPrefix . self::TABLE)
            ->set('date_update', ':dateUpdate')
            ->set('crc', ':crc')
            ->andWhere('card_hash = :cardHash')
            ->setParameter('dateUpdate', date('Y-m-d H:i:s'))
            ->setParameter('cardHash', $cardHash)
            ->setParameter('crc', $crc);

        $this->repositoryQueryHandler->execute($qb, 'Update card error');
    }

    /**
     * Saving the card
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createCard(
        string $cardVendor,
        string $cardShortCode,
        string $cardHash,
        int $userId,
        string $crc
    ): void {
        $transaction = new TpayCreditCard(
            $cardVendor,
            $cardShortCode,
            $cardHash,
            $userId,
            $crc
        );
        $this->entityManager->persist($transaction);
        $this->entityManager->flush();
    }
}
