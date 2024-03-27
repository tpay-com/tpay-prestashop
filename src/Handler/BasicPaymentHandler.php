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

namespace Tpay\Handler;

use Tpay\Exception\PaymentException;
use Tpay\Exception\TransactionException;
use Tpay\Service\TransactionService;
use Tpay\Util\Helper;

class BasicPaymentHandler implements PaymentMethodHandler
{
    public const TYPE = 'transfer';
    private $clientData;

    /**
     * @var \Tpay
     */
    private $module;
    /**
     * @var \Order
     */
    private $order;
    /**
     * @var \Customer
     */
    private $customer;
    /**
     * @var \Context
     */
    private $context;

    public function getName(): string
    {
        return self::TYPE;
    }

    /**
     * @throws PaymentException|TransactionException
     * @throws \Exception
     */
    public function createPayment(
        \Tpay     $module,
        \Order    $order,
        \Customer $customer,
        \Context  $context,
        array     $clientData,
        array     $data
    )
    {
        $this->module = $module;
        $this->order = $order;
        $this->customer = $customer;
        $this->context = $context;
        $this->clientData = $clientData;

        $this->updatePayData($data);

        $transaction = $this->createTransaction();

        $this->initTransactionProcess($transaction, $this->module->currentOrder);


        throw new PaymentException(
            'Unable to create payment method. Response: ' . json_encode($transaction)
        );
    }

    /**
     * Process of saving the transaction
     *
     * @param $transaction
     * @param $orderId
     * @param bool $redirect
     *
     * @throws \Exception
     */
    public function initTransactionProcess($transaction, $orderId, bool $redirect = true): void
    {
        if (isset($transaction['transactionId'])) {
            /** @var TransactionService $transactionService */
            $transactionService = $this->module->getService('tpay.service.transaction');
            $transactionService->transactionProcess(
                $transaction,
                self::TYPE,
                (int)$orderId,
                $redirect
            );
        }
    }

    /**
     * Create api transaction
     *
     * @throws TransactionException
     */
    private function createTransaction()
    {
        $result = $this->module->api->Transactions->createTransaction(
            $this->clientData
        );

        if (!isset($result['transactionId'])) {
            throw new TransactionException(
                'Unable to create transaction. Response: ' . json_encode($result)
            );
        }

        if (isset($this->clientData['pay']['channelId']) && $this->clientData['pay']['channelId']) {
            $result['transactionPaymentUrl'] = str_replace('gtitle', 'title', $result['transactionPaymentUrl']);
        }

        return $result;
    }

    private function updatePayData(array $data)
    {
        if ($data['type'] == 'transfer' && !Helper::getMultistoreConfigurationValue('TPAY_TRANSFER_WIDGET')) {
            unset($this->clientData['pay']);
        } else {
            $this->checkPayType($data);
        }
    }

    private function checkPayType(array $data)
    {
        $gatewayId = $data['tpay_transfer_id'] ?? 0;
        $channelId = $data['tpay_channel_id'] ?? 0;

        if ($channelId) {
            $this->clientData['pay']['channelId'] = (int)$channelId;
        } else {
            $this->clientData['pay']['groupId'] = (int)$gatewayId;
        }
    }
}
