<?php
/**MIT License
@license MIT

Copyright (c) 2026 Krajowy Integrator Płatności S.A.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

@author Krajowy Integrator Płatności S.A.
@copyright Krajowy Integrator Płatności S.A.*/

declare(strict_types=1);

namespace Tpay\Handler;

use Db;
use Exception;
use PrestaShopLogger;
use Tools;
use Tpay\Exception\BaseException;

class InstallQueryHandler
{
    /**
     * Execute sql files
     *
     * @throws BaseException
     */
    public function execute(string $path): bool
    {
        $db = Db::getInstance();
        $sql = Tools::file_get_contents($path);
        $sql = str_replace(['_DB_PREFIX_', '_MYSQL_ENGINE_'], [_DB_PREFIX_, _MYSQL_ENGINE_], $sql);

        try {
            $db->execute($sql);

            return true;
        } catch (Exception $exception) {
            PrestaShopLogger::addLog($exception->getMessage(), 3);
            throw new BaseException($exception->getMessage());
        }
    }
}
