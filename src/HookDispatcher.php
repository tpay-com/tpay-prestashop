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

namespace Tpay;

use Tpay;

class HookDispatcher
{
    public const CLASSES = [
        Hook\Design::class,
        Hook\Admin::class,
        Hook\Payment::class,
        Hook\EmailOrder::class,
        Hook\Order::class,
        Hook\Installment::class,
    ];

    /**
     * List of available hooks
     *
     * @var string[]
     */
    private $availableHooks = [];

    /**
     * Hook classes
     *
     * @var Hook\AbstractHook[]
     */
    private $hooks = [];

    /**
     * Module
     *
     * @var Tpay
     */
    private $module;

    /** Init hooks */
    public function __construct(Tpay $module)
    {
        $this->module = $module;
        foreach (self::CLASSES as $hookClass) {
            $hook = new $hookClass($this->module);
            $this->availableHooks = array_merge($this->availableHooks, $hook->getAvailableHooks());
            $this->hooks[] = $hook;
        }
    }

    /**
     * Get available hooks
     *
     * @return string[]
     */
    public function getAvailableHooks(): array
    {
        return $this->availableHooks;
    }

    /**
     * Find hook and dispatch it
     *
     * @return mixed|void
     */
    public function dispatch(string $hookName, array $params = [])
    {
        $hookName = preg_replace('~^hook~', '', $hookName);

        foreach ($this->hooks as $hook) {
            if (method_exists($hook, $hookName)) {
                return call_user_func([$hook, $hookName], $params);
            }
        }
    }
}
