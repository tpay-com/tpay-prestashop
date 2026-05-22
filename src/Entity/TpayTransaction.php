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
SOFTWARE.

@author Krajowy Integrator Płatności S.A.*/

declare(strict_types=1);

namespace Tpay\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table
 *
 * @ORM\Entity
 */
class TpayTransaction
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

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getCrc(): string
    {
        return $this->crc;
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getPaymentType(): string
    {
        return $this->paymentType;
    }

    public function getRegisterUser(): int
    {
        return $this->registerUser;
    }

    public function getSurcharge(): float
    {
        return $this->surcharge;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
