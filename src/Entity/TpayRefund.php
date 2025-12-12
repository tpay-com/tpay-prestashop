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
class TpayRefund
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
     * @var int
     *
     * @ORM\Column(name="order_id", type="integer")
     */
    private $orderId;

    /**
     * @var string
     *
     * @ORM\Column(name="transaction_id", type="string", length=255)
     */
    private $transactionId;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float")
     */
    private $amount;

    public function __construct(
        int $orderId,
        string $transactionId,
        float $amount
    ) {
        $this->orderId = $orderId;
        $this->transactionId = $transactionId;
        $this->amount = $amount;
        $this->date = new DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getTransactionTitle(): string
    {
        return $this->transactionId;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }
}
