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
     * Get all credit cards by user id
     *
     * @throws RepositoryException
     * @throws BaseException
     *
     * @return int|Statement
     */
    public function getAllCreditCardsByUserId($userId)
    {
        if (!$userId) {
            return;
        }

        $qb = $this->connection->createQueryBuilder();
        $qb
            ->addSelect('*')
            ->from($this->dbPrefix.self::TABLE, 'cc')
            ->andWhere('cc.user_id = :userId')
            ->andWhere('cc.card_token != ""')
            ->andWhere('cc.card_token IS NOT NULL')
            ->addOrderBy('cc.date_update', 'DESC');
        $qb->setParameter('userId', $userId);

        return $this->repositoryQueryHandler->execute($qb, 'Get credit card by user id', 'fetchAll');
    }

    /**
     * Get credit card by card_hash
     *
     * @throws BaseException
     * @throws RepositoryException
     *
     * @return int|Statement
     */
    public function getCreditCardByCardHashAndCrc(string $cardHash, string $crc)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->addSelect('*')
            ->from($this->dbPrefix.self::TABLE, 'cc')
            ->andWhere('cc.card_hash = :cardHash')
            ->andWhere('cc.crc = :crc');
        $qb->setParameter('cardHash', $cardHash);
        $qb->setParameter('crc', $crc);

        return $this->repositoryQueryHandler->execute($qb, 'Get credit card by hash', 'fetchAll');
    }

    /**
     * Get credit card by crc
     *
     * @throws BaseException
     * @throws RepositoryException
     *
     * @return int|Statement
     */
    public function getCreditCardTokenByCardCrc(string $crc)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->addSelect('card_token')
            ->from($this->dbPrefix.self::TABLE, 'cc')
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
            ->delete($this->dbPrefix.self::TABLE)
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
            ->update($this->dbPrefix.self::TABLE)
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
            ->update($this->dbPrefix.self::TABLE)
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
