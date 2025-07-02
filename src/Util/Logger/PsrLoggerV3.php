<?php

namespace Tpay\Util\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Stringable;

class PsrLoggerV3 implements LoggerInterface
{

    public function emergency(Stringable|string $message, array $context = array()): void
    {
        \PrestaShopLogger::addLog($message, 4);
    }

    public function alert(Stringable|string $message, array $context = array()): void
    {
        \PrestaShopLogger::addLog($message, 4);
    }

    public function critical(Stringable|string $message, array $context = array()): void
    {
        \PrestaShopLogger::addLog($message, 4);
    }

    public function error(Stringable|string $message, array $context = array()): void
    {
        \PrestaShopLogger::addLog($message, 3);
    }

    public function warning(Stringable|string $message, array $context = array()): void
    {
        \PrestaShopLogger::addLog($message, 2);
    }

    public function notice(Stringable|string $message, array $context = array()): void
    {
        if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_) {
            \PrestaShopLogger::addLog($message, 1);
        }
    }

    public function info(Stringable|string $message, array $context = array()): void
    {
        if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_) {
            \PrestaShopLogger::addLog($message, 1);
        }
    }

    public function debug(Stringable|string $message, array $context = array()): void
    {
        if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_) {
            \PrestaShopLogger::addLog($message, 1);
        }
    }

    public function log($level, Stringable|string $message, array $context = array()): void
    {
        switch ($level) {
            case LogLevel::EMERGENCY:
            case LogLevel::CRITICAL:
            case LogLevel::ALERT:
            case LogLevel::ERROR:
                $legacyLevel = 3;
                break;
            case LogLevel::WARNING:
                $legacyLevel = 2;
                break;
            case LogLevel::NOTICE:
            case LogLevel::INFO:
                $legacyLevel = 1;
                break;
            case LogLevel::DEBUG:
            default:
                $legacyLevel = 0;
                break;
        }

        if ((!defined('_PS_MODE_DEV_') || !_PS_MODE_DEV_) && $legacyLevel <= 1) {
            return;
        }

        \PrestaShopLogger::addLog($message, $legacyLevel);
    }
}
