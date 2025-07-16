<?php

namespace Tpay\Util;

use Psr\Log\LoggerInterface;
use ReflectionParameter;
use Tpay\Util\Logger\PsrLoggerV1;
use Tpay\Util\Logger\PsrLoggerV3;

$parameter = new ReflectionParameter(array(LoggerInterface::class, 'log'), 'message');
if ($parameter->hasType()) {
    class PsrLogger extends PsrLoggerV3 {}
} else {
    class PsrLogger extends PsrLoggerV1 {}
}

