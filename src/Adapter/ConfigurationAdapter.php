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

namespace Tpay\Adapter;

use Configuration as Cfg;
use Shop;
use Tools;

class ConfigurationAdapter
{
    /** @var Shop */
    private $shopId;

    public function __construct($shopId)
    {
        $this->shopId = $shopId;
    }

    public function get($key, $idLang = null, $idShopGroup = null, $idShop = null, $default = false)
    {
        if (null === $idShop) {
            $idShop = $this->shopId;
        }

        return Cfg::get($key, $idLang, $idShopGroup, $idShop, $default);
    }

    public function updateValue($key, $value, $html = false, $idShopGroup = null, $idShop = null)
    {
        if (null === $idShop) {
            $idShop = $this->shopId;
        }

        if ('TPAY_MERCHANT_SECRET' == $key) {
            $value = Tools::safeOutput($value, true);
        }

        if (is_array($value)) {
            $value = json_encode($value);
        }

        return Cfg::updateValue($key, $value, $html, $idShopGroup, $idShop);
    }

    public function deleteByName($key)
    {
        return Cfg::deleteByName($key);
    }
}
