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

use Configuration;
use Context;
use Exception;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use PrestaShopException;
use PrestaShopLogger;
use Tpay;
use Tpay\Config\Config;
use Tpay\Factory\PaymentOptionsFactory;
use Tpay\Service\ConstraintValidator;
use Tpay\Service\GenericPayments\GenericPaymentsManager;
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
     * @throws PrestaShopException
     * @throws Exception
     */
    public function __construct(Tpay $module)
    {
        $this->module = $module;
        $this->constraintValidator = new ConstraintValidator($module);
        $this->getGroup();
    }

    /** @throws PrestaShopException */
    public function getGroup(): void
    {
        try {
            $this->getPaymentGroups();
        } catch (PrestaShopException $e) {
            PrestaShopLogger::addLog('Error getGroup '.$e->getMessage(), 4);
            throw new PrestaShopException($e->getMessage());
        }
    }

    /**
     * Create all transfer group
     */
    public function createTransferPaymentChannel(): void
    {
        $payment = [
            'img' => Context::getContext()->shop->getBaseURL(true).'modules/tpay/views/img/tpay.svg',
            'gateways' => $this->getGroupTransfers(),
            'id' => Config::GATEWAY_TRANSFER,
            'mainChannel' => Config::GATEWAY_TRANSFER,
        ];

        $this->createGateway($payment);
    }

    public function getActivePayments(): array
    {
        // Adding transfer group
        $this->createTransferPaymentChannel();

        $payments = array_filter(array_map(function (array $paymentData) {
            $optionClass = PaymentOptionsFactory::getOptionById((int) $paymentData['mainChannel']);

            if (is_object($optionClass)) {
                $gateway = new PaymentType($optionClass);

                return $gateway->getPaymentOption($this->module, new PaymentOption(), $paymentData);
            }
        }, $this->channels));

        $extracted = $this->getExtractedPaymentOptions();
        $generics = $this->genericPayments();
        $payments = array_values($payments);

        array_splice($payments, count($payments) - 1, 0, $extracted);

        return array_merge($payments, $generics);
    }

    /**
     * Grouping of payments delivered from api
     */
    public function getGroupTransfers(): array
    {
        return $this->transfers ?? [];
    }

    private function getExtractedPaymentOptions(): array
    {
        $result = [];
        foreach (GenericPaymentsManager::EXTRACTED_PAYMENT_CHANNELS as $channelId => $configField) {
            if (!GenericPaymentsManager::isChannelExcluded($channelId)) {
                continue;
            }

            $channel = null;

            foreach ($this->bankChannels as $bankChannel) {
                if (isset($bankChannel['id']) && (int) $bankChannel['id'] === (int) $channelId) {
                    $channel = $bankChannel;
                    break;
                }
            }

            if (!$channel) {
                continue;
            }

            if (!empty($channel['constraints']) && !$this->constraintValidator->validate($channel['constraints'], $this->getBrowser())) {
                continue;
            }

            $gateway = new PaymentType(new Generic());
            $result[] = $gateway->getPaymentOption($this->module, new PaymentOption(), $channel);
        }

        return $result;
    }

    private function createGateway(array $array = []): void
    {
        $this->channels[] = $array;
    }

    /**
     * @throws Exception
     */
    private function getSeparatePayments(array $channels): array
    {
        $paymentsMethods = [
            Config::GATEWAY_BLIK => (bool) Helper::getMultistoreConfigurationValue('TPAY_BLIK_ACTIVE'),
        ];

        if ($this->hasActiveCard()) {
            $paymentsMethods[Config::GATEWAY_CARD] = (bool) Helper::getMultistoreConfigurationValue(
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
        return Configuration::get('TPAY_CARD_ACTIVE') || !empty(Configuration::get('TPAY_CARD_RSA'));
    }

    /**
     * Grouping of payments delivered from api
     *
     * @throws Exception
     */
    private function getPaymentGroups(): void
    {
        $channels = $this->module->api->transactions()->getChannels()['channels'] ?? [];
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
                }
                $seenNames[] = $channel['id'];

                return true;
            });
        }

        $this->transfers = $transfers;
    }

    private function buildChannelsData(array $channels): array
    {
        $bankChannels = [];
        foreach ($channels as $channel) {
            if (!empty($channel['constraints']) && !$this->constraintValidator->validate($channel['constraints'], $this->getBrowser())) {
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

    /**
     * Grouping of payments delivered from api
     */
    private function groupChannel(array $channels, array $compareArray): array
    {
        return array_filter($channels, function ($val) use ($compareArray) {
            return in_array($val['mainChannel'], $compareArray);
        });
    }

    /**
     * Downloading payment gateways to the online money transfer group
     *
     * @throws Exception
     */
    private function groupTransfer(array $channels, array $compareArray): array
    {
        $generics = Helper::getMultistoreConfigurationValue('TPAY_GENERIC_PAYMENTS') ? json_decode(Helper::getMultistoreConfigurationValue('TPAY_GENERIC_PAYMENTS')) : [];
        $compareChannels = array_merge($compareArray, $generics);

        return array_filter($channels, function ($val) use ($compareChannels) {
            return !in_array($val['mainChannel'], array_merge($compareChannels));
        });
    }

    /** @return array<PaymentOption> */
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

            if (null === $channel) {
                return;
            }
            if (!empty($channel['constraints']) && !$this->constraintValidator->validate($channel['constraints'], $this->getBrowser())) {
                return;
            }

            $gateway = new PaymentType(new Generic());

            return $gateway->getPaymentOption($this->module, new PaymentOption(), $channel);
        }, $generics));
    }

    private function getBrowser(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        if (strpos($userAgent, 'Chrome')) {
            return 'Chrome';
        }
        if (strpos($userAgent, 'Safari')) {
            return 'Safari';
        }

        return 'Other';
    }
}
