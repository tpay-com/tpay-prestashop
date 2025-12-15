<?php

namespace Tpay\Adapter;

use Configuration as Cfg;
use Shop;
use Tools;

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
