<?php

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

        $this->initTransactionProcess($transaction, $this->module->currentOrder);

        throw new PaymentException(
            'Unable to create payment method. Response: '.json_encode($transaction)
        );
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
