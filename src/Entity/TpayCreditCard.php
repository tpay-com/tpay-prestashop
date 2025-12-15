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

    /**
     * @return self 
     */
    public function setCardToken(string $cardToken)
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
