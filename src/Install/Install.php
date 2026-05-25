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

namespace Tpay\Install;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Context;
use Db;
use PrestaShopBundle\Translation\TranslatorComponent;
use Shop;
use Tools;
use Tpay;
use Tpay\Exception\BaseException;
use Tpay\Handler\InstallQueryHandler;

class Install
{
    /** @var Tpay */
    private $module;

    /** @phpstan-ignore-next-line */
    private $installQueryHandler;

    /** @var TranslatorComponent */
    private $translator;

    public function __construct(
        Tpay $module,
        InstallQueryHandler $installQueryHandler
    ) {
        $this->module = $module;
        $this->installQueryHandler = $installQueryHandler;
        $this->translator = $module->getTranslator();
    }

    /** @throws BaseException */
    public function install(): bool
    {
        if (!$this->installDb()) {
            $this->module->_errors[] = $this->translator->trans('Table installation error', [], 'Modules.Tpay.Admin');
        }

        if (!$this->installContext()) {
            $this->module->_errors[] = $this->translator->trans('Context installation error', [], 'Modules.Tpay.Admin');
        }

        return true;
    }

    /**
     * Sql data installation
     *
     * @throws BaseException
     */
    private function installDb(): bool
    {
        $status = true;
        $database = Db::getInstance();
        $sql = Tools::file_get_contents($this->module->getLocalPath() . 'src/Install/install.sql');
        $sql = str_replace(['_DB_PREFIX_', '_MYSQL_ENGINE_'], [_DB_PREFIX_, _MYSQL_ENGINE_], $sql);

        $sqlQuery = explode(';', $sql);
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

    /** Starts context if there is a store running multistore option */
    private function installContext(): bool
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_SHOP, Context::getContext()->shop->id);
        }

        return true;
    }
}
