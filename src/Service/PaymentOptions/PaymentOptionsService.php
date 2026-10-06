<?php
/**
 * @author Krajowy Integrator Płatności S.A.
 * @copyright Krajowy Integrator Płatności S.A.
 * @license MIT
 *
 * Copyright (c) 2026 Krajowy Integrator Płatności S.A.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Tpay\Service\PaymentOptions;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Tpay\Config\Config;
use Tpay\Factory\PaymentOptionsFactory;
use Tpay\Service\ConstraintValidator;
use Tpay\Service\GenericPayments\GenericPaymentsManager;
use Tpay\Util\Cache;
use Tpay\Util\Helper;

class PaymentOptionsService
{
    /** Channels list cache */
    private const CHANNELS_CACHE_KEY = 'channels_list';
    private const CHANNELS_CACHE_TTL = 900;
    private const CHANNELS_STALE_TTL = 86400;

    private $module;
    private $channels;
    private $transfers;
    private $bankChannels;

    /** @var \Context */
    private $context;

    /** @var ConstraintValidator */
    private $constraintValidator;

    /**
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function __construct(\Tpay $module, \Context $context)
    {
        $this->module = $module;
        $this->context = $context;
        $this->constraintValidator = new ConstraintValidator($module);
        $this->getGroup();
    }

    /** @throws \PrestaShopException */
    public function getGroup(): void
    {
        try {
            $this->getPaymentGroups();
        } catch (\PrestaShopException $e) {
            \PrestaShopLogger::addLog('Error getGroup ' . $e->getMessage(), 4);
            throw new \PrestaShopException($e->getMessage());
        }
    }

    /** Create all transfer group */
    public function createTransferPaymentChannel(): void
    {
        $payment = [
            // PrestaShop 1.7 incorrectly documents this boolean flag as a string.
            // @phpstan-ignore argument.type
            'img' => $this->context->shop->getBaseURL(true) . 'modules/tpay/views/img/tpay.svg',
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

        $payments = array_filter(
            array_map(
                function (array $paymentData) {
                    $optionClass = PaymentOptionsFactory::getOptionById((int) $paymentData['mainChannel'], $this->context);

                    if (is_object($optionClass)) {
                        $gateway = new PaymentType($optionClass);

                        return $gateway->getPaymentOption($this->module, new PaymentOption(), $paymentData);
                    }
                },
                $this->channels
            )
        );

        $extracted = $this->getExtractedPaymentOptions();
        $generics = $this->genericPayments();
        $payments = array_values($payments);

        array_splice($payments, count($payments) - 1, 0, $extracted);

        return array_merge($payments, $generics);
    }

    /** Grouping of payments delivered from api */
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

            $gateway = new PaymentType(new Generic($this->context));
            $result[] = $gateway->getPaymentOption($this->module, new PaymentOption(), $channel);
        }

        return $result;
    }

    private function createGateway(array $array = []): void
    {
        $this->channels[] = $array;
    }

    /** @throws \Exception */
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
        return \Configuration::get('TPAY_CARD_ACTIVE') || !empty(\Configuration::get('TPAY_CARD_RSA'));
    }

    /**
     * Grouping of payments delivered from api
     *
     * @throws \Exception
     */
    private function getPaymentGroups(): void
    {
        $channels = $this->getChannelsList();
        $this->bankChannels = $channels;

        if (!empty($channels)) {
            $channels = $this->buildChannelsData($channels);
            $separatePayments = $this->getSeparatePayments($channels);
            $this->channels = $this->groupChannel($channels, $separatePayments);
            $this->updateTransfers($this->groupTransfer($channels, $separatePayments));
        }
    }

    /**
     * Channels list from the API, cached for CHANNELS_CACHE_TTL seconds
     *
     * @throws \Throwable when the API fails and no cached list exists
     */
    private function getChannelsList(): array
    {
        $cacheKey = self::getChannelsCacheKey();
        $cached = Cache::get($cacheKey);

        if (is_string($cached)) {
            $decoded = json_decode($cached, true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        try {
            $channels = $this->module->api->transactions()->getChannels()['channels'] ?? [];
        } catch (\Throwable $exception) {
            $stale = Cache::get($cacheKey . '_stale');
            $decoded = is_string($stale) ? json_decode($stale, true) : null;

            if (is_array($decoded)) {
                \PrestaShopLogger::addLog(
                    sprintf('Tpay: channels request failed (%s), using the last cached list', $exception->getMessage()),
                    2
                );

                return $decoded;
            }

            throw $exception;
        }

        if (!empty($channels)) {
            $encoded = json_encode($channels);
            Cache::set($cacheKey, $encoded, self::CHANNELS_CACHE_TTL);
            Cache::set($cacheKey . '_stale', $encoded, self::CHANNELS_STALE_TTL);
        }

        return $channels;
    }

    /** One entry per environment and merchant account */
    private static function getChannelsCacheKey(?bool $sandbox = null): string
    {
        if (null === $sandbox) {
            $sandbox = (bool) Helper::getMultistoreConfigurationValue('TPAY_SANDBOX');
        }

        return sprintf(
            '%s_%s_%s',
            self::CHANNELS_CACHE_KEY,
            $sandbox ? 'sandbox' : 'production',
            md5((string) Helper::getMultistoreConfigurationValue('TPAY_CLIENT_ID'))
        );
    }

    public static function clearChannelsCache(): void
    {
        foreach ([true, false] as $sandbox) {
            $key = self::getChannelsCacheKey($sandbox);
            Cache::delete($key);
            Cache::delete($key . '_stale');
        }

        Cache::delete('channels');
    }

    private function updateTransfers(array $transfers): void
    {
        if (!Helper::getMultistoreConfigurationValue('TPAY_REDIRECT_TO_CHANNEL')) {
            $seenNames = [];
            $transfers = array_filter(
                $transfers,
                function ($channel) use (&$seenNames) {
                    if (in_array($channel['id'], $seenNames)) {
                        return false;
                    }
                    $seenNames[] = $channel['id'];

                    return true;
                }
            );
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

    /** Grouping of payments delivered from api */
    private function groupChannel(array $channels, array $compareArray): array
    {
        return array_filter(
            $channels,
            function ($val) use ($compareArray) {
                return in_array($val['mainChannel'], $compareArray);
            }
        );
    }

    /**
     * Downloading payment gateways to the online money transfer group
     *
     * @throws \Exception
     */
    private function groupTransfer(array $channels, array $compareArray): array
    {
        $generics = Helper::getMultistoreConfigurationValue('TPAY_GENERIC_PAYMENTS') ? json_decode(Helper::getMultistoreConfigurationValue('TPAY_GENERIC_PAYMENTS')) : [];
        $compareChannels = array_merge($compareArray, $generics);

        return array_filter(
            $channels,
            function ($val) use ($compareChannels) {
                return !in_array($val['mainChannel'], array_merge($compareChannels));
            }
        );
    }

    /** @return array<PaymentOption> */
    private function genericPayments(): array
    {
        $generics = Helper::getMultistoreConfigurationValue('TPAY_GENERIC_PAYMENTS') ? json_decode(Helper::getMultistoreConfigurationValue('TPAY_GENERIC_PAYMENTS')) : [];
        $channels = json_decode(Cache::get('channels', 'null'), true);

        if (null === $channels) {
            $channels = array_filter(
                $this->bankChannels,
                function (array $channel) {
                    return true === $channel['available'];
                }
            );
            foreach ($channels as $channel) {
                $channels[$channel['id']] = $channel;
            }

            Cache::set('channels', json_encode($channels));
        }

        return array_filter(
            array_map(
                function (string $generic) use ($channels) {
                    $channel = $channels[$generic] ?? null;

                    if (null === $channel) {
                        return;
                    }
                    if (!empty($channel['constraints']) && !$this->constraintValidator->validate($channel['constraints'], $this->getBrowser())) {
                        return;
                    }

                    $gateway = new PaymentType(new Generic($this->context));

                    return $gateway->getPaymentOption($this->module, new PaymentOption(), $channel);
                },
                $generics
            )
        );
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
