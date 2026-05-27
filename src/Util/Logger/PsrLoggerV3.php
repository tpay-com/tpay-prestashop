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

namespace Tpay\Util\Logger;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class PsrLoggerV3 implements LoggerInterface
{
    public function emergency(\Stringable|string $message, array $context = []): void
    {
        \PrestaShopLogger::addLog($message, 4);
    }

    public function alert(\Stringable|string $message, array $context = []): void
    {
        \PrestaShopLogger::addLog($message, 4);
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
        \PrestaShopLogger::addLog($message, 4);
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
        \PrestaShopLogger::addLog($message, 3);
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
        \PrestaShopLogger::addLog($message, 2);
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
        if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_) {
            \PrestaShopLogger::addLog($message, 1);
        }
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
        if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_) {
            \PrestaShopLogger::addLog($message, 1);
        }
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
        if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_) {
            \PrestaShopLogger::addLog($message, 1);
        }
    }

    public function log($level, \Stringable|string $message, array $context = []): void
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
