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

namespace Tpay\Install;

use Tpay;
use Tpay\Exception\BaseException;
use Tpay\Handler\InstallQueryHandler;

class Uninstall
{
    /** @var Tpay */
    private $module;

    /** @var InstallQueryHandler */
    private $installQueryHandler;

    public function __construct(
        Tpay $module,
        InstallQueryHandler $installQueryHandler
    ) {
        $this->module = $module;
        $this->installQueryHandler = $installQueryHandler;
    }

    /**
     * Deleting sql data
     *
     * @throws BaseException
     */
    public function uninstallDb(): bool
    {
        return $this->installQueryHandler->execute($this->module->getLocalPath().'src/Install/uninstall.sql') && (new Reset())->resetDb();
    }
}
