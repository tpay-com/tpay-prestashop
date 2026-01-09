<?php

namespace Tpay\Factory;

use AdminController;
use Cart;
use Context;
use Cookie;
use Currency;
use FrontController;
use Language;
use Link;
use Shop;
use Smarty;

class ContextFactory
{
    /** @return null|Context */
    public static function getContext()
    {
        return Context::getContext();
    }

    /** @return Cart */
    public static function getCart()
    {
        return Context::getContext()->cart;
    }

    /** @return Language|\PrestaShopBundle\Install\Language */
    public static function getLanguage()
    {
        return Context::getContext()->language;
    }

    /** @return null|Currency */
    public static function getCurrency()
    {
        return Context::getContext()->currency;
    }

    /** @return Smarty */
    public static function getSmarty()
    {
        return Context::getContext()->smarty;
    }

    /** @return Shop */
    public static function getShop()
    {
        return Context::getContext()->shop;
    }

    /** @return AdminController|FrontController */
    public static function getController()
    {
        return Context::getContext()->controller;
    }

    /** @return Cookie */
    public static function getCookie()
    {
        return Context::getContext()->cookie;
    }

    /** @return Link */
    public static function getLink()
    {
        return Context::getContext()->link;
    }
}
