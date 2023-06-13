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
use Tpay\Util\Helper;

class PaymentOptionsService
{
    /**
     * @var false|object
     */
    private $surchargeService;
    private $module;
    private $channels;
    private $transfers;

    /**
     * @var PaymentOption
     */
    private $paymentOption;
    /**
     * @var \Cart
     */
    private $cart;

    /**
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function __construct(
        \Tpay $module,
        PaymentOption $paymentOption,
        \Cart $cart
    ) {
        $this->module = $module;
        $this->paymentOption = $paymentOption;
        $this->cart = $cart;
        $this->surchargeService = $this->module->getService('tpay.service.surcharge');

        $this->getGroup();
    }

    /**
     * @throws \PrestaShopException
     */
    public function getGroup(): void
    {
        try {
            $this->getPaymentGroups();
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
     * @param array $array
     * @return void
     */
    private function createGateway(array $array = []): void
    {
        $this->channels[] = $array;
    }

    /**
     * @return array
     */
    public function getActivePayments(): array
    {
        $paymentOptions = [];

        // Adding transfer group
        $this->createTransferPaymentChannel();
        // Adding Apple pay
        $this->createApplePayPaymentChannel();

        foreach ($this->channels as $payment_data) {
            $optionClass = PaymentOptionsFactory::getOptionById((int) $payment_data['mainChannel']);

            if (is_object($optionClass)) {
                $gateway = new PaymentType(new $optionClass());

                $paymentOptions[] = $gateway->getPaymentOption(
                    $this->module,
                    new PaymentOption(),
                    $payment_data
                );
            }
        }

        return $paymentOptions;
    }

    /**
     * @throws \Exception
     * @return array
     */
    private function getSeparatePayments(): array
    {
        $paymentsMethods = [
            Config::GATEWAY_BLIK => (bool) Helper::getMultistoreConfigurationValue('TPAY_BLIK_ACTIVE'),
            Config::GATEWAY_GOOGLE_PAY => (bool) Helper::getMultistoreConfigurationValue('TPAY_GPAY_ACTIVE'),
            Config::GATEWAY_APPLE_PAY => (bool) Helper::getMultistoreConfigurationValue('TPAY_APPLEPAY_ACTIVE'),
        ];

        if ($this->aliorBetweenPriceRange()) {
            $paymentsMethods[Config::GATEWAY_ALIOR_RATY] = (bool) Helper::getMultistoreConfigurationValue(
                'TPAY_INSTALLMENTS_ACTIVE'
            );
        }

        if ($this->twistoBetweenPriceRange()) {
            $paymentsMethods[Config::GATEWAY_TWISTO] = (bool) Helper::getMultistoreConfigurationValue(
                'TPAY_TWISTO_ACTIVE'
            );
        }

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


    /**
     * @throws \Exception
     */
    private function aliorBetweenPriceRange(): bool
    {
        $total = $this->surchargeService->getTotalOrderAndSurchargeCost();
        return $total >= Config::ALIOR_RATY_MIN && $total <= Config::ALIOR_RATY_MAX;
    }

    private function twistoBetweenPriceRange(): bool
    {
        $total = $this->surchargeService->getTotalOrderAndSurchargeCost();
        return $total >= Config::TWISTO_MIN && $total <= Config::TWISTO_MAX;
    }

    private function hasActiveCard(): bool
    {
        return \Configuration::get('TPAY_CARD_ACTIVE') || !empty(\Configuration::get('TPAY_CARD_RSA'));
    }

    /**
     * Grouping of payments delivered from api
     *
     * @throws \Exception
     * @return void
     */
    private function getPaymentGroups(): void
    {
        $bankGroups = $this->module->api->Transactions->getBankGroups();
        if ($bankGroups) {
            $this->channels = $this->groupChannel($bankGroups['groups'], $this->getSeparatePayments());
            $this->transfers = $this->groupTransfer($bankGroups['groups'], $this->getSeparatePayments());
        }
    }

    /**
     * Grouping of payments delivered from api
     * * @param array $group
     * @param array $compareArray
     * @return array
     */
    private function groupChannel(array $group, array $compareArray): array
    {
        return array_filter($group, function ($val) use ($compareArray) {
            return in_array($val['mainChannel'], $compareArray);
        });
    }

    /**
     * Downloading payment gateways to the online money transfer group
     *
     * @param array $group
     * @param array $compareArray
     *
     * @throws \Exception
     * @return array
     */
    private function groupTransfer(array $group, array $compareArray): array
    {
        // If not price range hide id gateway
        if (!$this->aliorBetweenPriceRange()) {
            $compareArray[] = Config::GATEWAY_ALIOR_RATY;
        }

        // If not price range hide id gateway
        if (!$this->twistoBetweenPriceRange()) {
            $compareArray[] = Config::GATEWAY_TWISTO;
        }

        return array_filter($group, function ($val) use ($compareArray) {
            return !in_array($val['mainChannel'], $compareArray);
        });
    }

    /**
     * Grouping of payments delivered from api
     * @return array
     */
    public function getGroupTransfers(): array
    {
        return $this->transfers ?? [];
    }
}
