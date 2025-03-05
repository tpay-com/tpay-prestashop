<?php

namespace Tpay\Util;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class PsrLogger implements LoggerInterface
{

    public function emergency($message, array $context = array())
    {
        \PrestaShopLogger::addLog($message, 4);
    }

    public function alert($message, array $context = array())
    {
        \PrestaShopLogger::addLog($message, 4);
    }

    public function critical($message, array $context = array())
    {
        \PrestaShopLogger::addLog($message, 4);
    }

    public function error($message, array $context = array())
    {
        \PrestaShopLogger::addLog($message, 3);
    }

    public function warning($message, array $context = array())
    {
        \PrestaShopLogger::addLog($message, 2);
    }

    public function notice($message, array $context = array())
    {
        if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_) {
            \PrestaShopLogger::addLog($message, 1);
        }
    }

    public function info($message, array $context = array())
    {
        if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_) {
            \PrestaShopLogger::addLog($message, 1);
        }
    }

    public function debug($message, array $context = array())
    {
        if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_) {
            \PrestaShopLogger::addLog($message, 1);
        }
    }

    public function log($level, $message, array $context = array())
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
