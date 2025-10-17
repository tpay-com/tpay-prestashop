{*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
*  @author    tpay.com
*  @copyright 2010-2020 tpay.com
*  @license   LICENSE.txt
*}
{if $showSummary}{include file="$orderSummaryPath"}{/if}
{$paymentForm nofilter}
<p class="cart_navigation clearfix" id="cart_navigation">
    <a class="button-exclusive btn btn-default"
       href="{$link->getPageLink('order', true)|escape:'htmlall':'UTF-8'}">
        <i class="icon-chevron-left"></i>{l s='Other payment methods' d='Modules.Tpay.Shop'}
    </a>
</p>
