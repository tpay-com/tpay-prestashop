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

namespace Tpay\Adapter;

use Configuration as Cfg;
use Shop;

class ConfigurationAdapter
{
    /**
     * @var Shop
     */
    private $shopId;

    public function __construct($shopId)
    {
        $this->shopId = $shopId;
    }

    public function get($key, $idLang = null, $idShopGroup = null, $idShop = null, $default = false)
    {
        if ($idShop === null) {
            $idShop = $this->shopId;
        }

        return Cfg::get($key, $idLang, $idShopGroup, $idShop, $default);
    }

    public function updateValue($key, $value, $html = false, $idShopGroup = null, $idShop = null)
    {
        if ($idShop === null) {
            $idShop = $this->shopId;
        }

        return Cfg::updateValue($key, $value, $html, $idShopGroup, $idShop);
    }

    public function deleteByName($key)
    {
        return Cfg::deleteByName($key);
    }
}
