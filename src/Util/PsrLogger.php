<?php

namespace Tpay\Util;

use Psr\Log\LoggerInterface;

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
        if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ && $level == 1) {
            return;
        }
        \PrestaShopLogger::addLog($message, $level);
    }
}
