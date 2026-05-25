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

namespace Tpay\Handler;

use Context;
use Customer;
use Exception;
use Order;
use Tpay;
use Tpay\Exception\PaymentException;
use Tpay\Exception\TransactionException;
use Tpay\Service\TransactionService;
use Tpay\Util\Helper;

class BasicPaymentHandler implements PaymentMethodHandler
{
    public const TYPE = 'transfer';

    /** @var array */
    protected $clientData;

    /** @var Tpay */
    protected $module;

    public function getName(): string
    {
        return self::TYPE;
    }

    /**
     * @throws PaymentException|TransactionException
     * @throws Exception
     */
    public function createPayment(
        Tpay $module,
        Order $order,
        Customer $customer,
        Context $context,
        array $clientData,
        array $data
    ): void {
        $this->module = $module;
        $this->clientData = $clientData;

        $this->updatePayData($data);
        $this->updateLang($data);

        $transaction = $this->createTransaction();

        $this->initTransactionProcess($transaction, (int) $order->id);

        throw new PaymentException('Unable to create payment method. Response: ' . json_encode($transaction));
    }

    /**
     * @param array|string $transaction
     *
     * @throws Exception
     */
    public function initTransactionProcess($transaction, int $orderId, bool $redirect = true): void
    {
        if (!isset($transaction['transactionId'])) {
            return;
        }

        /** @var TransactionService $transactionService */
        $transactionService = $this->module->getService('tpay.service.transaction');
        $transactionService->transactionProcess(
            $transaction,
            self::TYPE,
            $orderId,
            $redirect
        );
    }

    /** @throws TransactionException */
    protected function createTransaction(): array
    {
        $result = $this->module->api()->transactions()->createTransaction($this->clientData);

        if (!isset($result['transactionId'])) {
            throw TransactionException::unableToCreateTransaction($result);
        }

        if (isset($this->clientData['pay']['channelId']) && $this->clientData['pay']['channelId']) {
            $result['transactionPaymentUrl'] = str_replace('gtitle', 'title', $result['transactionPaymentUrl']);
        }

        return $result;
    }

    protected function updatePayData(array $data): void
    {
        $type = $data['type'] ?? self::TYPE;
        if ('transfer' == $type && !Helper::getMultistoreConfigurationValue('TPAY_TRANSFER_WIDGET')) {
            unset($this->clientData['pay']);
        } else {
            $this->checkPayType($data);
        }
    }

    protected function checkPayType(array $data): void
    {
        $gatewayId = $data['tpay_transfer_id'] ?? 0;
        $channelId = $data['tpay_channel_id'] ?? 0;

        if ($channelId) {
            $this->clientData['pay']['channelId'] = (int) $channelId;
        } elseif ($gatewayId) {
            $this->clientData['pay']['groupId'] = (int) $gatewayId;
        } else {
            unset($this->clientData['pay']);
        }
    }

    protected function updateLang(array $data): void
    {
        $lang = $data['isolang'] ?? 'en';
        $this->clientData['lang'] = in_array($lang, ['pl', 'en']) ? $lang : 'en';
    }
}
