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
SOFTWARE.*/

declare(strict_types=1);

namespace Tpay\Handler;

use Context;
use Customer;
use Order;
use Tpay;
use Tpay\Exception\PaymentException;
use Tpay\Exception\TransactionException;

class InstantPaymentHandler extends BasicPaymentHandler
{
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

    protected function createTransaction(): array
    {
        $result = $this->module->api()->transactions()->createTransactionWithInstantRedirection($this->clientData);

        if (!isset($result['transactionId'])) {
            throw TransactionException::unableToCreateTransaction($result);
        }

        if (isset($this->clientData['pay']['channelId']) && $this->clientData['pay']['channelId']) {
            $result['transactionPaymentUrl'] = str_replace('gtitle', 'title', $result['transactionPaymentUrl']);
        }

        return $result;
    }
}
