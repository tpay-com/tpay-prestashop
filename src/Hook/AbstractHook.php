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

namespace Tpay\Hook;

use Context;
use Tpay;

abstract class AbstractHook
{
    public const AVAILABLE_HOOKS = [];

    /**
     * @var Tpay 
     */
    protected $module;

    /**
     * @var Context 
     */
    protected $context;

    public function __construct(Tpay $module)
    {
        $this->module = $module;
        $this->context = $module->getContext();
    }

    public function getAvailableHooks(): array
    {
        return static::AVAILABLE_HOOKS;
    }
}
