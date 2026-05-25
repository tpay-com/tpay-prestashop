{**MIT License
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
SOFTWARE.

@author Krajowy Integrator Płatności S.A.
@copyright Krajowy Integrator Płatności S.A.*}

<div class="tpay-wrapper" style="width: 100%;" data-payment-type="transfer">
    {if isset($gateways) && $gateways}
        <form action="{$action}" id="payment-form" class="transferForm" method="post">
            <input type="hidden" name="tpay" value="{$tpay}">
            <input type="hidden" name="type" value="{$type}">
            {if $transfer_type === 'widget'}
                <ul class="tpay-payment-gateways tpay-payment-gateways--grid">
                    {foreach $gateways as $p}
                        {assign var="identificator" value=$p.id}
                        {assign var="type" value="tpay_transfer_id"}

                        {if $isDirect}
                            {assign var="identificator" value=$p.mainChannel}
                            {assign var="type" value="tpay_channel_id"}
                        {/if}
                        <label for="transfer_{$identificator|escape:'htmlall':'UTF-8'}" class="tpay-payment-gateways__item">
                            <input
                                    id="transfer_{$identificator|escape:'htmlall':'UTF-8'}"
                                    type="radio" name="{$type|escape:'htmlall':'UTF-8'}"
                                    value="{$identificator|escape:'htmlall':'UTF-8'}" required="required"
                                    style="display: none"/>
                            <div class="tpay-payment-gateways__item-inner">
                                <img class="img-fluid col-xs-12" src="{$p.img|escape:'htmlall':'UTF-8'}"
                                     alt="{$p.name|escape:'htmlall':'UTF-8'}" width="80"/>
                                <span class="text-xs-center">{$p.name|escape:'htmlall':'UTF-8'}</span>
                            </div>
                        </label>
                    {/foreach}
                </ul>
            {else}
                {l s='You will be redirected to the payment gateway.' d='Modules.Tpay.Shop'}
            {/if}
        </form>
        <div class="transfer-error" style="display: none">
		<span class="tpay-error">
			{l s='Please choose payment method' d='Modules.Tpay.Shop'}
		</span>
        </div>
        {if $transfer_type === 'widget'}
            {include file="module:tpay/views/templates/hook/regulations.tpl"}
        {/if}
    {else}
        {l s='No active payment gateways' d='Modules.Tpay.Shop'}
    {/if}
</div>
