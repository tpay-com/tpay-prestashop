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

namespace Tpay\Handler;

use Tpay\Exception\BaseException;

class InstallQueryHandler
{
    /**
     * Execute sql files
     *
     * @param string $path
     * @throws BaseException
     * @return bool
     */
    public function execute(string $path, bool $prefixReplace = true): bool
    {
        $db = \Db::getInstance();
        $sql = \Tools::file_get_contents($path);
        \PrestaShopLogger::addLog('exec', 3);

        if ($prefixReplace) {
            $sql = str_replace(['_DB_PREFIX_', '_MYSQL_ENGINE_'], [_DB_PREFIX_, _MYSQL_ENGINE_], $sql);
        }

        try {
            $db->execute($sql);
            return true;
        } catch (\Exception $exception) {
            \PrestaShopLogger::addLog($exception->getMessage(), 3);
            throw new BaseException($exception->getMessage());
        }
    }
}
