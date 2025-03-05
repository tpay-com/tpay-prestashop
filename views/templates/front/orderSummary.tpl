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
{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if isset($nbProducts) && $nbProducts <= 0}
    <p class="warning">{l s='Your shopping cart is empty.' mod='tpay'}</p>
{else}
    <div id="tpay-order-summary">
        <div id="tpay-summary-conten">
            <ul>
                <li>
                    <h4><b>{l s='Summary of ordered products:' mod='tpay'}<b></h4>
                    <table>
                        <tr>
                            <td>{l s=' Product' mod='tpay'}</td>
                            <td>{l s=" Unit price " mod='tpay'} ({$currencySign})</td>
                            <td>{l s=' Quantity' mod='tpay'}</td>
                            <td>{l s=" Total price " mod='tpay'} ({$currencySign})</td>
                        </tr>
                        {foreach from=$productsT key=name item=value}
                            <tr>
                                {foreach from=$value key=name1 item=value1}
                                    <td>
                                        {$value1}
                                    </td>
                                {/foreach}
                            </tr>
                        {/foreach}
                        <tr>
                            <td colspan="3">{l s='Shipping cost: ' mod='tpay'}</td>
                            <td>{displayPrice price=$shippingCostT}</td>
                        </tr>
                        {if isset($surcharge)}
                        <tr>
                            <td colspan="3">{l s='Payment surcharge' mod='tpay'}</td>
                            <td>{displayPrice price=$surcharge}</td>
                        </tr>
                        {/if}
                        <tr>
                            <td colspan="3">{l s='Order price ' mod='tpay'}</td>
                            <td>{displayPrice price=$orderTotal}
                                {if $use_taxes == 1}
                                {l s='(total)' mod='tpay'}
                                {/if}</td>
                        </tr>
                    </table>
                <li>
                <li>
                    <div style="display: inline-flex">

                        <table>
                            <tr>
                                <td><h4><b>{l s='Delivery address:' mod='tpay'}</b></h4></td>
                            </tr>
                            <tr>
                                <td>{l s='Alias' mod='tpay'}</td>
                                <td>{$deliveryAddressT->alias}</td>
                            </tr>
                            <tr>
                                <td>{l s='Company' mod='tpay'}</td>
                                <td>{$deliveryAddressT->company}</td>
                            </tr>
                            <tr>
                                <td>{l s='Name and surname' mod='tpay'}</td>
                                <td>{$deliveryAddressT->firstname} {$deliveryAddressT->lastname}</td>
                            </tr>
                            <tr>
                                <td>{l s='Phone' mod='tpay'}</td>
                                <td>{$deliveryAddressT->phone}</td>
                            </tr>
                            <tr>
                                <td>{l s='Address' mod='tpay'}</td>
                                <td>{$deliveryAddressT->address1} {$deliveryAddressT->address2}</td>
                            </tr>
                            <tr>
                                <td>{l s='Postal code' mod='tpay'}</td>
                                <td>{$deliveryAddressT->postcode}</td>
                            </tr>
                            <tr>
                                <td>{l s='City' mod='tpay'}</td>
                                <td>{$deliveryAddressT->city}</td>
                            </tr>
                            <tr>
                                <td>{l s='Country' mod='tpay'}</td>
                                <td>{$invAddressT->country}</td>
                            </tr>
                        </table>

                        <table>
                            <tr>
                                <td><h4><b>{l s='Invoicing address:' mod='tpay'}</b></h4></td>
                            </tr>
                            <tr>
                                <td>{l s='Alias' mod='tpay'}</td>
                                <td>{$invAddressT->alias}</td>
                            </tr>
                            <tr>
                                <td>{l s='Company' mod='tpay'}</td>
                                <td>{$invAddressT->company}</td>
                            </tr>
                            <tr>
                                <td>{l s='Name and surname' mod='tpay'}</td>
                                <td>{$invAddressT->firstname} {$invAddressT->lastname}</td>
                            </tr>
                            <tr>
                                <td>{l s='Phone' mod='tpay'}</td>
                                <td>{$invAddressT->phone}</td>
                            </tr>
                            <tr>
                                <td>{l s='Address' mod='tpay'}</td>
                                <td>{$invAddressT->address1} {$invAddressT->address2}</td>
                            </tr>
                            <tr>
                                <td>{l s='Postal code' mod='tpay'}</td>
                                <td>{$invAddressT->postcode}</td>
                            </tr>
                            <tr>
                                <td>{l s='City' mod='tpay'}</td>
                                <td>{$invAddressT->city}</td>
                            </tr>
                            <tr>
                                <td>{l s='Country' mod='tpay'}</td>
                                <td>{$invAddressT->country}</td>
                            </tr>
                        </table>
                    </div>
                </li>
            </ul>
            <h4>{l s='Tpay payment' mod='tpay'}</h4>
        </div>
    </div>
{/if}
