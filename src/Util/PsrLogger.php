<?php

namespace Tpay\Util;

use Psr\Log\LoggerInterface;
use ReflectionParameter;
use Tpay\Util\Logger\PsrLoggerV1;
use Tpay\Util\Logger\PsrLoggerV3;

$parameter = new ReflectionParameter([LoggerInterface::class, 'log'], 'message');
if ($parameter->hasType()) {
    /* @phpstan-ignore-next-line */
    class_alias(PsrLoggerV3::class, PsrLogger::class);
} else {
    /* @phpstan-ignore-next-line */
    class_alias(PsrLoggerV1::class, PsrLogger::class);
}
