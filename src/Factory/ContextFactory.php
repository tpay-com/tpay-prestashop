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

namespace Tpay\Factory;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ContextFactory
{
    /** @return \Context|null */
    public static function getContext()
    {
        return \Context::getContext();
    }

    /** @return \Cart */
    public static function getCart()
    {
        return \Context::getContext()->cart;
    }

    /** @return \Language|\PrestaShopBundle\Install\Language */
    public static function getLanguage()
    {
        return \Context::getContext()->language;
    }

    /** @return \Currency|null */
    public static function getCurrency()
    {
        return \Context::getContext()->currency;
    }

    /** @return \Smarty */
    public static function getSmarty()
    {
        return \Context::getContext()->smarty;
    }

    /** @return \Shop */
    public static function getShop()
    {
        return \Context::getContext()->shop;
    }

    /** @return \AdminController|\FrontController */
    public static function getController()
    {
        return \Context::getContext()->controller;
    }

    /** @return \Cookie */
    public static function getCookie()
    {
        return \Context::getContext()->cookie;
    }

    /** @return \Link */
    public static function getLink()
    {
        return \Context::getContext()->link;
    }
}
