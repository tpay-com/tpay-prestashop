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

class Install
{
    /**
     * @var Tpay
     */
    private $module;

    /**
     * @var InstallQueryHandler
     */
    private $installQueryHandler;

    public function __construct(
        Tpay $module,
        InstallQueryHandler $installQueryHandler
    ) {
        $this->module = $module;
        $this->installQueryHandler = $installQueryHandler;
    }

    /**
     * @throws BaseException
     */
    public function install(): bool
    {
        if (!$this->installDb()) {
            $this->module->_errors[] = $this->l('Table installation error');
        }

        if (!$this->installContext()) {
            $this->module->_errors[] = $this->l('Context installation error');
        }

        return true;
    }

    /**
     * Sql data installation
     *
     * @throws BaseException
     * @return bool
     */
    private function installDb(): bool
    {
        $status = true;
        $database = \Db::getInstance();
        $sql = \Tools::file_get_contents($this->module->getLocalPath() . 'src/Install/install.sql');
        $sql = str_replace(['_DB_PREFIX_', '_MYSQL_ENGINE_'], [_DB_PREFIX_, _MYSQL_ENGINE_], $sql);

        $sqlQuery = explode(";", $sql);
        foreach ($sqlQuery as $q) {
            $q = trim($q);
            if (!empty($q)) {
                $status = $database->execute($q);
                if (!$status) {
                    throw new BaseException();
                }
            }
        }

        return $status;
    }

    /**
     * Starts context if there is a store running multistore option
     *
     * @return bool
     */
    private function installContext(): bool
    {
        if (\Shop::isFeatureActive()) {
            \Shop::setContext(\Shop::CONTEXT_SHOP, \Context::getContext()->shop->id);
        }

        return true;
    }
}
