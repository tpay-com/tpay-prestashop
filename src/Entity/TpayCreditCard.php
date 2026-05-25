<?php
/**
@author Krajowy Integrator Płatności S.A.
@copyright Krajowy Integrator Płatności S.A.
@license MIT

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

namespace Tpay\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table
 *
 * @ORM\Entity
 */
class TpayCreditCard
{
    /**
     * @var int
     *
     * @ORM\Id
     *
     * @ORM\Column(name="id", type="integer")
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @phpstan-ignore-next-line
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="card_vendor", type="string", length=255)
     */
    private $cardVendor;

    /**
     * @var string
     *
     * @ORM\Column(name="card_shortcode", type="string", length=255)
     */
    private $cardShortcode;

    /**
     * @var string
     *
     * @ORM\Column(name="card_hash", type="string", length=255)
     */
    private $cardHash;

    /**
     * @var string
     *
     * @ORM\Column(name="card_token", type="string", length=255)
     */
    private $cardToken;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="crc", type="string")
     */
    private $crc;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date_add", type="datetime")
     */
    private $dateAdd;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date_update", type="datetime")
     *
     * @phpstan-ignore-next-line
     */
    private $dateUpdate;

    public function __construct(
        string $cardVendor,
        string $cardShortcode,
        string $cardHash,
        int $userId,
        string $crc
    ) {
        $this->cardVendor = $cardVendor;
        $this->cardShortcode = $cardShortcode;
        $this->cardHash = $cardHash;
        $this->userId = $userId;
        $this->crc = $crc;
        $this->dateAdd = new DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCardVendor(): string
    {
        return $this->cardVendor;
    }

    public function getCardShortcode(): string
    {
        return $this->cardShortcode;
    }

    public function getCardHash(): string
    {
        return $this->cardHash;
    }

    public function getCardToken(): string
    {
        return $this->cardToken;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getCrc(): string
    {
        return $this->crc;
    }

    public function getDateAdd(): DateTime
    {
        return $this->dateAdd;
    }

    public function getDateUpdate(): DateTime
    {
        return $this->dateUpdate;
    }

    public function setCardToken(string $cardToken): self
    {
        $this->cardToken = $cardToken;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'card_vendor' => $this->getCardVendor(),
            'card_shortcode' => $this->getCardShortcode(),
            'card_hash' => $this->getCardHash(),
            'user_id' => $this->getUserId(),
            'crc' => $this->getCrc(),
        ];
    }
}
