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

use Tpay\Exception\RepositoryException;
use Tpay\Repository\CreditCardsRepository;

class CardService
{
    /**
     * @var CreditCardsRepository 
     */
    private $repository;

    public function __construct(
        CreditCardsRepository $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * @throws RepositoryException
     */
    public function transactionSavedCard($savedCardId, $customerId): string
    {
        $accountCreditCards = $this->repository->getAllCreditCardsByUserId($customerId);
        $requestedCardId = (int) $savedCardId;

        $cardToken = '';

        foreach ($accountCreditCards as $card) {
            if ((int) $card['id'] === $requestedCardId) {
                $cardToken = (string) $card['card_token'];
            }
        }

        return $cardToken;
    }

    /**
     * @throws RepositoryException
     */
    public function updateUserCardDetails($cardHash, $crc): void
    {
        $this->repository->updateCard(
            (string) $cardHash,
            (string) $crc
        );
    }

    public function saveUserCardDetails($cardVendor, $cardShortcode, $cardHash, $userId, $crc): void
    {
        $this->repository->createCard(
            (string) $cardVendor,
            (string) $cardShortcode,
            (string) $cardHash,
            (int) $userId,
            (string) $crc
        );
    }

    /**
     * @throws RepositoryException
     */
    public function getCreditCardByCardHashAndCrc($cardHash, $crc)
    {
        return $this->repository->getCreditCardByCardHashAndCrc($cardHash, $crc);
    }
}
