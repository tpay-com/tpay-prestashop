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

namespace Tpay\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Tpay\Exception\RepositoryException;
use Tpay\Repository\CreditCardsRepository;

class CardService
{
    /** @var CreditCardsRepository */
    private $repository;

    public function __construct(CreditCardsRepository $repository) {
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
