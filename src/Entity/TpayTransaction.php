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

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class TpayTransaction
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
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
     * @ORM\Column(name="crc", type="string", length=255)
     */
    private $crc;

    /**
     * @var string
     *
     * @ORM\Column(name="transaction_id", type="string", length=255)
     */
    private $transactionId;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_type", type="string", length=255)
     */
    private $paymentType;

    /**
     * @var int
     *
     * @ORM\Column(name="register_user", type="integer")
     */
    private $registerUser;

    /**
     * @var float
     *
     * @ORM\Column(name="surcharge", type="float")
     */
    private $surcharge;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string")
     */
    private $status;

    public function __construct(
        int $orderId,
        string $crc,
        string $transactionId,
        string $paymentType,
        int $registerUser,
        float $surcharge,
        string $status
    ) {
        $this->orderId = $orderId;
        $this->crc = $crc;
        $this->transactionId = $transactionId;
        $this->paymentType = $paymentType;
        $this->registerUser = $registerUser;
        $this->surcharge = $surcharge;
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @return string
     */
    public function getCrc(): string
    {
        return $this->crc;
    }

    /**
     * @return string
     */
    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * @return string
     */
    public function getPaymentType(): string
    {
        return $this->paymentType;
    }

    /**
     * @return int
     */
    public function getRegisterUser(): int
    {
        return $this->registerUser;
    }

    /**
     * @return float
     */
    public function getSurcharge(): float
    {
        return $this->surcharge;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}
