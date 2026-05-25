{**
@author Krajowy Integrator Płatności S.A.
@copyright Krajowy Integrator Płatności S.A.
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
SOFTWARE.*}
{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if isset($nbProducts) && $nbProducts <= 0}
    <p class="warning">{l s='Your shopping cart is empty.' d='Modules.Tpay.Shop'}</p>
{else}
    <div id="tpay-order-summary">
        <div id="tpay-summary-conten">
            <ul>
                <li>
                    <h4><b>{l s='Summary of ordered products:' d='Modules.Tpay.Shop'}<b></h4>
                    <table>
                        <tr>
                            <td>{l s=' Product' d='Modules.Tpay.Shop'}</td>
                            <td>{l s=" Unit price " d='Modules.Tpay.Shop'} ({$currencySign})</td>
                            <td>{l s=' Quantity' d='Modules.Tpay.Shop'}</td>
                            <td>{l s=" Total price " d='Modules.Tpay.Shop'} ({$currencySign})</td>
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
                            <td colspan="3">{l s='Shipping cost: ' d='Modules.Tpay.Shop'}</td>
                            <td>{displayPrice price=$shippingCostT}</td>
                        </tr>
                        {if isset($surcharge)}
                        <tr>
                            <td colspan="3">{l s='Payment surcharge' d='Modules.Tpay.Shop'}</td>
                            <td>{displayPrice price=$surcharge}</td>
                        </tr>
                        {/if}
                        <tr>
                            <td colspan="3">{l s='Order price ' d='Modules.Tpay.Shop'}</td>
                            <td>{displayPrice price=$orderTotal}
                                {if $use_taxes == 1}
                                {l s='(total)' d='Modules.Tpay.Shop'}
                                {/if}</td>
                        </tr>
                    </table>
                <li>
                <li>
                    <div style="display: inline-flex">

                        <table>
                            <tr>
                                <td><h4><b>{l s='Delivery address:' d='Modules.Tpay.Shop'}</b></h4></td>
                            </tr>
                            <tr>
                                <td>{l s='Alias' d='Modules.Tpay.Shop'}</td>
                                <td>{$deliveryAddressT->alias}</td>
                            </tr>
                            <tr>
                                <td>{l s='Company' d='Modules.Tpay.Shop'}</td>
                                <td>{$deliveryAddressT->company}</td>
                            </tr>
                            <tr>
                                <td>{l s='Name and surname' d='Modules.Tpay.Shop'}</td>
                                <td>{$deliveryAddressT->firstname} {$deliveryAddressT->lastname}</td>
                            </tr>
                            <tr>
                                <td>{l s='Phone' d='Modules.Tpay.Shop'}</td>
                                <td>{$deliveryAddressT->phone}</td>
                            </tr>
                            <tr>
                                <td>{l s='Address' d='Modules.Tpay.Shop'}</td>
                                <td>{$deliveryAddressT->address1} {$deliveryAddressT->address2}</td>
                            </tr>
                            <tr>
                                <td>{l s='Postal code' d='Modules.Tpay.Shop'}</td>
                                <td>{$deliveryAddressT->postcode}</td>
                            </tr>
                            <tr>
                                <td>{l s='City' d='Modules.Tpay.Shop'}</td>
                                <td>{$deliveryAddressT->city}</td>
                            </tr>
                            <tr>
                                <td>{l s='Country' d='Modules.Tpay.Shop'}</td>
                                <td>{$invAddressT->country}</td>
                            </tr>
                        </table>

                        <table>
                            <tr>
                                <td><h4><b>{l s='Invoicing address:' d='Modules.Tpay.Shop'}</b></h4></td>
                            </tr>
                            <tr>
                                <td>{l s='Alias' d='Modules.Tpay.Shop'}</td>
                                <td>{$invAddressT->alias}</td>
                            </tr>
                            <tr>
                                <td>{l s='Company' d='Modules.Tpay.Shop'}</td>
                                <td>{$invAddressT->company}</td>
                            </tr>
                            <tr>
                                <td>{l s='Name and surname' d='Modules.Tpay.Shop'}</td>
                                <td>{$invAddressT->firstname} {$invAddressT->lastname}</td>
                            </tr>
                            <tr>
                                <td>{l s='Phone' d='Modules.Tpay.Shop'}</td>
                                <td>{$invAddressT->phone}</td>
                            </tr>
                            <tr>
                                <td>{l s='Address' d='Modules.Tpay.Shop'}</td>
                                <td>{$invAddressT->address1} {$invAddressT->address2}</td>
                            </tr>
                            <tr>
                                <td>{l s='Postal code' d='Modules.Tpay.Shop'}</td>
                                <td>{$invAddressT->postcode}</td>
                            </tr>
                            <tr>
                                <td>{l s='City' d='Modules.Tpay.Shop'}</td>
                                <td>{$invAddressT->city}</td>
                            </tr>
                            <tr>
                                <td>{l s='Country' d='Modules.Tpay.Shop'}</td>
                                <td>{$invAddressT->country}</td>
                            </tr>
                        </table>
                    </div>
                </li>
            </ul>
            <h4>{l s='Tpay payment' d='Modules.Tpay.Shop'}</h4>
        </div>
    </div>
{/if}
