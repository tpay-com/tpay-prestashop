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

namespace Tpay\Service\PaymentOptions;

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Tpay\Factory\PaymentOptionsFactory;
use Tpay\Config\Config;
use Tpay\Service\ConstraintValidator;
use Tpay\Util\Cache;
use Tpay\Util\Helper;

class PaymentOptionsService
{
    private $module;
    private $channels;
    private $transfers;
    private $bankChannels;

    /** @var ConstraintValidator */
    private $constraintValidator;

    /**
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function __construct(\Tpay $module)
    {
        $this->module = $module;
        $this->constraintValidator = new ConstraintValidator($module);
        $this->getGroup();
    }

    /**
     * @throws \PrestaShopException
     */
    public function getGroup(): void
    {
        try {
            $this->getPaymentGroups();
            $this->getInstallmentChannels();
        } catch (\PrestaShopException $e) {
            \PrestaShopLogger::addLog('Error getGroup ' . $e->getMessage(), 4);
            throw new \PrestaShopException($e->getMessage());
        }
    }

    /**
     * Create all transfer group
     * @return void
     */
    public function createTransferPaymentChannel(): void
    {
        $payment = [
            'img' => \Context::getContext()->shop->getBaseURL(true) . 'modules/tpay/views/img/tpay.svg',
            'gateways' => $this->getGroupTransfers(),
            'id' => Config::GATEWAY_TRANSFER,
            'mainChannel' => Config::GATEWAY_TRANSFER,
        ];

        $this->createGateway($payment);
    }

    /**
     * Create Apple Pay channel
     * @return void
     */
    public function createApplePayPaymentChannel(): void
    {
        $payment = [
            'img' => \Context::getContext()->shop->getBaseURL(true) . 'modules/tpay/views/img/tpay.svg',
            'id' => Config::GATEWAY_APPLE_PAY,
            'mainChannel' => Config::GATEWAY_APPLE_PAY,
        ];
        $this->createGateway($payment);
    }

    /**
     * @return array
     */
    public function getActivePayments(): array
    {
        // Adding transfer group
        $this->createTransferPaymentChannel();
        // Adding Apple pay
        $this->createApplePayPaymentChannel();

        $payments = array_filter(array_map(function (array $paymentData) {
            $optionClass = PaymentOptionsFactory::getOptionById((int)$paymentData['mainChannel']);

            if (is_object($optionClass)) {
                $gateway = new PaymentType($optionClass);

                return $gateway->getPaymentOption($this->module, new PaymentOption(), $paymentData);
            }

            return null;
        }, $this->channels));

        $generics = $this->genericPayments();

        return array_merge($payments, $generics);
    }

    /**
     * Grouping of payments delivered from api
     * @return array
     */
    public function getGroupTransfers(): array
    {
        return $this->transfers ?? [];
    }

    /**
     * @param array $array
     * @return void
     */
    private function createGateway(array $array = []): void
    {
        $this->channels[] = $array;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getSeparatePayments(array $channels): array
    {
        $paymentsMethods = [
            Config::GATEWAY_BLIK => (bool)Helper::getMultistoreConfigurationValue('TPAY_BLIK_ACTIVE'),
            Config::GATEWAY_GOOGLE_PAY => (bool)Helper::getMultistoreConfigurationValue('TPAY_GPAY_ACTIVE'),
            Config::GATEWAY_APPLE_PAY => (bool)Helper::getMultistoreConfigurationValue('TPAY_APPLEPAY_ACTIVE'),
        ];

        if (isset($channels[Config::GATEWAY_ALIOR_RATY])) {
            $paymentsMethods[Config::GATEWAY_ALIOR_RATY] = (bool)Helper::getMultistoreConfigurationValue(
                'TPAY_INSTALLMENTS_ACTIVE'
            );
        }

        if (isset($channels[Config::GATEWAY_TWISTO])) {
            $paymentsMethods[Config::GATEWAY_TWISTO] = (bool)Helper::getMultistoreConfigurationValue(
                'TPAY_TWISTO_ACTIVE'
            );
        }

        if (isset($channels[Config::GATEWAYS_PEKAO_RATY])) {
            $paymentsMethods[Config::GATEWAYS_PEKAO_RATY] =
            $paymentsMethods[Config::GATEWAYS_PEKAO_RATY_3x0] =
            $paymentsMethods[Config::GATEWAYS_PEKAO_RATY_50] =
            $paymentsMethods[Config::GATEWAYS_PEKAO_RATY_10x0] = (bool)Helper::getMultistoreConfigurationValue(
                'TPAY_PEKAO_INSTALLMENTS_ACTIVE'
            );
        }

        if ($this->hasActiveCard()) {
            $paymentsMethods[Config::GATEWAY_CARD] = (bool)Helper::getMultistoreConfigurationValue(
                'TPAY_CARD_ACTIVE'
            );
        }

        $result = [];
        foreach ($paymentsMethods as $key => $method) {
            if (true === $method) {
                $result[] = $key;
            }
        }

        return $result;
    }

    private function hasActiveCard(): bool
    {
        return \Configuration::get('TPAY_CARD_ACTIVE') || !empty(\Configuration::get('TPAY_CARD_RSA'));
    }

    /**
     * Grouping of payments delivered from api
     *
     * @return void
     * @throws \Exception
     */
    private function getPaymentGroups(): void
    {
        $channels = $this->module->api->Transactions->getChannels()['channels'] ?? [];
        $this->bankChannels = $channels;

        if (!empty($channels)) {
            $channels = $this->buildChannelsData($channels);
            $separatePayments = $this->getSeparatePayments($channels);
            $this->channels = $this->groupChannel($channels, $separatePayments);
            $this->updateTransfers($this->groupTransfer($channels, $separatePayments));
        }
    }

    private function updateTransfers(array $transfers): void
    {
        if (!Helper::getMultistoreConfigurationValue('TPAY_REDIRECT_TO_CHANNEL')) {
            $seenNames = [];
            $transfers = array_filter($transfers, function ($channel) use (&$seenNames) {
                if (in_array($channel['id'], $seenNames)) {
                    return false;
                } else {
                    $seenNames[] = $channel['id'];
                    return true;
                }
            });
        }

        $this->transfers = $transfers;
    }

    private function buildChannelsData(array $channels): array
    {
        $bankChannels = [];

        foreach ($channels as $channel) {
            if (count($channel['constraints']) >= 2 && !$this->constraintValidator->validate($channel['constraints'])) {
                continue;
            }
            $bankChannels[$channel['id']] = [
                'id' => $channel['groups'][0]['id'],
                'name' => $channel['fullName'],
                'img' => $channel['image']['url'],
                'availablePaymentChannels' => [$channel['id']],
                'mainChannel' => $channel['id'],
            ];
        }

        return $bankChannels;
    }

    private function getInstallmentChannels(): void
    {
        $installmentIds = [Config::GATEWAY_ALIOR_RATY, Config::GATEWAY_PAYPO, Config::GATEWAY_TWISTO, Config::GATEWAYS_PEKAO_RATY];

        foreach ($this->bankChannels as $channel) {
            if (in_array($channel['id'], $installmentIds)) {
                $this->installmentChannels[$channel['id']] = $channel;
            }
        }
    }

    /**
     * Grouping of payments delivered from api
     * @param array $channels
     * @param array $compareArray
     * @return array
     */
    private function groupChannel(array $channels, array $compareArray): array
    {
        if (isset($this->bankChannels[Config::GATEWAYS_PEKAO_RATY])) {
            $availableChannels = [(string)Config::GATEWAYS_PEKAO_RATY, (string)Config::GATEWAYS_PEKAO_RATY_3x0, (string)Config::GATEWAYS_PEKAO_RATY_10x0];
            $channels[Config::GATEWAYS_PEKAO_RATY]['availablePaymentChannels'] = $availableChannels;
            $channels[Config::GATEWAYS_PEKAO_RATY]['mainChannel'] = reset($availableChannels);
        }

        return array_filter($channels, function ($val) use ($compareArray) {
            return in_array($val['mainChannel'], $compareArray) && !in_array($val['mainChannel'], [(string)Config::GATEWAYS_PEKAO_RATY_3x0, (string)Config::GATEWAYS_PEKAO_RATY_10x0]);
        });
    }

    /**
     * Downloading payment gateways to the online money transfer group
     *
     * @param array $channels
     * @param array $compareArray
     *
     * @return array
     * @throws \Exception
     */
    private function groupTransfer(array $channels, array $compareArray): array
    {
        if (Helper::getMultistoreConfigurationValue('TPAY_INSTALLMENTS_ACTIVE') || !isset($channels[Config::GATEWAY_ALIOR_RATY])) {
            $compareArray[] = Config::GATEWAY_ALIOR_RATY;
        }

        if (Helper::getMultistoreConfigurationValue('TPAY_TWISTO_ACTIVE') || !isset($channels[Config::GATEWAY_TWISTO])) {
            $compareArray[] = Config::GATEWAY_TWISTO;
        }

        if (Helper::getMultistoreConfigurationValue('TPAY_PEKAO_INSTALLMENTS_ACTIVE') || !isset($channels[Config::GATEWAYS_PEKAO_RATY])) {
            $compareArray[] = Config::TPAY_GATEWAY_PEKAO_RATY;
            $compareArray[] = Config::GATEWAYS_PEKAO_RATY_3x0;
            $compareArray[] = Config::GATEWAYS_PEKAO_RATY;
            $compareArray[] = Config::GATEWAYS_PEKAO_RATY_10x0;
            $compareArray[] = Config::GATEWAYS_PEKAO_RATY_50;
        }

        if (!isset($channels[Config::GATEWAY_PAYPO])) {
            $compareArray[] = Config::GATEWAY_PAYPO;
        }

        return array_filter($channels, function ($val) use ($compareArray) {
            return !in_array($val['mainChannel'], $compareArray);
        });
    }

    /**
     * @return array<PaymentOption>
     */
    private function genericPayments(): array
    {
        $generics = Helper::getMultistoreConfigurationValue('TPAY_GENERIC_PAYMENTS') ? json_decode(Helper::getMultistoreConfigurationValue('TPAY_GENERIC_PAYMENTS')) : [];
        $channels = unserialize(Cache::get('channels', 'N;'));

        if (null === $channels) {
            $channels = array_filter($this->bankChannels, function (array $channel) {
                return true === $channel['available'];
            });

            foreach ($channels as $channel) {
                $channels[$channel['id']] = $channel;
            }

            Cache::set('channels', serialize($channels));
        }

        return array_filter(array_map(function (string $generic) use ($channels) {
            $channel = $channels[$generic] ?? null;

            if ($channel === null) {
                return null;
            }

            if (!empty($channel['constraints']) && !$this->constraintValidator->validate($channel['constraints'])) {
                return null;
            }

            $gateway = new PaymentType(new Generic());

            return $gateway->getPaymentOption($this->module, new PaymentOption(), $channel);
        }, $generics));
    }
}
