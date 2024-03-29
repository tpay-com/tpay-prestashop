<?php

namespace Tpay\Factory;

use Context;

class ContextFactory
{
    /**
     * @return Context|null
     */
    public static function getContext()
    {
        return Context::getContext();
    }

    /**
     * @return \Cart
     */
    public static function getCart()
    {
        return Context::getContext()->cart;
    }

    /**
     * @return \Language|\PrestaShopBundle\Install\Language
     */
    public static function getLanguage()
    {
        return Context::getContext()->language;
    }

    /**
     * @return \Currency|null
     */
    public static function getCurrency()
    {
        return Context::getContext()->currency;
    }

    /**
     * @return \Smarty
     */
    public static function getSmarty()
    {
        return Context::getContext()->smarty;
    }

    /**
     * @return \Shop
     */
    public static function getShop()
    {
        return Context::getContext()->shop;
    }

    /**
     * @return \AdminController|\FrontController
     */
    public static function getController()
    {
        return Context::getContext()->controller;
    }

    /**
     * @return \Cookie
     */
    public static function getCookie()
    {
        return Context::getContext()->cookie;
    }

    /**
     * @return \Link
     */
    public static function getLink()
    {
        return Context::getContext()->link;
    }
}
