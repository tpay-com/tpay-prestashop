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

use Tools;
use Tpay\Config\Config;
use Tpay\Exception\PaymentException;
use Tpay\Exception\TransactionException;

class CreditCardPaymentHandler implements PaymentMethodHandler
{
    public const TYPE = 'cards';

    private $cardRepository;
    private $cardService;
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
     * @throws TransactionException
     * @throws PaymentException
     */
    public function createPayment(
        \Tpay $module,
        \Order $order,
        \Customer $customer,
        \Context $context,
        array $clientData,
        array $data
    ) {
        $this->module = $module;
        $this->order = $order;
        $this->customer = $customer;
        $this->clientData = $clientData;
        $this->context = $context;

        $savedId = $data['savedId'] ?? null;
        $this->cardRepository = $module->getService('tpay.repository.credit_card');
        $this->cardService = $module->getService('tpay.service.card_service');
        $this->updateLang($data);

        if ($savedId) {
            $this->processSavedCardPayment($savedId);
        }

        $this->processPaymentNewCard();
    }

    /**
     * Check is saved card not empty
     *
     * @throws TransactionException
     */
    private function processSavedCardPayment($savedCardId): void
    {
        $transaction = $this->createTransaction();
        $savedCard = $this->cardService->transactionSavedCard($savedCardId, $this->customer->id);

        if (empty($savedCard)) {
            \PrestaShopLogger::addLog('Unauthorized payment try (empty token)', 3);
            Tools::redirect($transaction['transactionPaymentUrl']);
        }

        $this->payBySavedCard($savedCard, $transaction);
    }

    /**
     * Create new card
     *
     * @throws PaymentException|TransactionException
     * @throws \Exception
     */
    private function processPaymentNewCard(): void
    {
        $transaction = $this->createTransaction();

        if (isset($transaction['transactionId'])) {
            // Try to sale with provided card data
            $response = $this->makeCardPayment($transaction);

            if ($response['result'] === 'failure') {
                Tools::redirect($response['transactionPaymentUrl']);
            }


            if (isset($response['status']) && $response['status'] === 'correct') {
                Tools::redirect(
                    $this->context->link->getModuleLink(
                        'tpay',
                        'confirmation',
                        [
                            'type' => Config::TPAY_PAYMENT_CARDS,
                            'order_id' => $this->order->id
                        ]
                    )
                );
            }

            if (isset($response['transactionPaymentUrl'])) {
                $this->initTransactionProcess($transaction, $this->module->currentOrder);

                Tools::redirect($response['transactionPaymentUrl']);
            }
        }
        \PrestaShopLogger::addLog('Unable to create new card payment. Response: ' . json_encode($transaction), 3);
        Tools::redirect($this->context->link->getModuleLink(
            'tpay',
            'ordererror'
        ));
    }

    /**
     * Process of saving the transaction
     *
     * @param $transaction
     * @param $orderId
     *
     * @throws \Exception
     */
    public function initTransactionProcess($transaction, $orderId): void
    {
        if (isset($transaction['transactionId'])) {
            $transactionService = $this->module->getService('tpay.service.transaction');
            $transactionService->transactionProcess(
                $transaction,
                self::TYPE,
                (int) $orderId,
                false
            );
        }
    }

    /**
     * @param $cardToken
     * @param $transaction
     */
    private function payBySavedCard($cardToken, $transaction): void
    {
        $request = [
            'groupId' => Config::CARD_GROUP_ID,
            'cardPaymentData' => [
                'token' => $cardToken,
            ],
            'method' => 'sale',
        ];

        $result = $this->module->api->transactions()->createPaymentByTransactionId(
            $request,
            $transaction['transactionId']
        );

        if (isset($result['result'], $result['status']) && $result['status'] === 'correct') {
            $transactionService = $this->module->getService('tpay.service.transaction');
            $transactionService->transactionProcess(
                $transaction,
                self::TYPE,
                $this->module->currentOrder,
                false
            );

            Tools::redirect(
                $this->context->link->getModuleLink(
                    'tpay',
                    'confirmation',
                    [
                        'type' => Config::TPAY_PAYMENT_CARDS,
                        'order_id' => $this->order->id
                    ]
                )
            );
        }

        Tools::redirect($transaction['transactionPaymentUrl']);
    }

    /**
     * Create api transaction
     *
     * @throws TransactionException
     */
    private function createTransaction()
    {
        $this->clientData['pay']['groupId'] = Config::CARD_GROUP_ID;
        $this->clientData['pay']['method'] = 'sale';

        $result = $this->module->api->transactions()->createTransaction(
            $this->clientData
        );

        if (!isset($result['transactionId'])) {
            throw TransactionException::unableToCreateTransaction($result);
        }

        return $result;
    }

    /**
     * @throws \Exception
     */
    private function makeCardPayment($transaction)
    {
        $cardDataInput = filter_input(INPUT_POST, 'carddata', FILTER_SANITIZE_STRING);
        $cardHashInput = filter_input(INPUT_POST, 'card_hash', FILTER_SANITIZE_STRING);
        $cartHash = $this->module->getService('tpay.util.secret_hash')->getValue();

        $cardHash = SHA1($cardHashInput . $cartHash);
        $saveCard = false;

        if (isset($_POST['card_save'])) {
            $cardVendor = (string) ($_POST['card_vendor'] ?? null);
            $cardShortCode = (string) ($_POST['card_short_code'] ?? null);
            $saveCard = $_POST['card_save'] === 'on';
            $crc = $this->clientData['hiddenDescription'];


            if ($cardVendor && $cardShortCode && $crc) {
                //  check if exists card
                if ($this->cardRepository->getCreditCardByCardHashAndCrc($cardHash, $crc)) {
                    $this->cardService->updateUserCardDetails(
                        $cardHash,
                        $crc
                    );
                } else {
                    $this->cardService->saveUserCardDetails(
                        $cardVendor,
                        $cardShortCode,
                        $cardHash,
                        $this->customer->id,
                        $crc
                    );
                }
            }
        }

        $request = [
            'groupId' => Config::CARD_GROUP_ID,
            'cardPaymentData' => [
                'card' => $cardDataInput,
                'save' => $saveCard,
            ],
            'method' => 'sale',
        ];

        return $this->module->api->transactions()->createPaymentByTransactionId(
            $request,
            $transaction['transactionId']
        );
    }

    protected function updateLang(array $data): void
    {
        $this->clientData['lang'] = in_array($data['isolang'], ['pl', 'en']) ? $data['isolang'] : 'en';
    }
}
