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

    /** @var array */
    protected $clientData;

    /** @var \Tpay */
    protected $module;

    public function getName(): string
    {
        return self::TYPE;
    }

    /**
     * @throws PaymentException|TransactionException
     * @throws \Exception
     */
    public function createPayment(
        \Tpay $module,
        \Order $order,
        \Customer $customer,
        \Context $context,
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
            'Unable to create payment method. Response: ' . json_encode($transaction)
        );
    }

    /**
     * @param array|string $transaction
     * @throws \Exception
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

    /**
     * @throws TransactionException
     */
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
        if ($data['type'] == 'transfer' && !Helper::getMultistoreConfigurationValue('TPAY_TRANSFER_WIDGET')) {
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
            $this->clientData['pay']['channelId'] = (int)$channelId;
        } elseif ($gatewayId) {
            $this->clientData['pay']['groupId'] = (int)$gatewayId;
        } else {
            unset($this->clientData['pay']);
        }
    }

    protected function updateLang(array $data): void
    {
        $this->clientData['lang'] = in_array($data['isolang'], ['pl', 'en']) ? $data['isolang'] : 'en';
    }
}
