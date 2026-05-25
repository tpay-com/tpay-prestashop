{**
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
*}
<div class="tpay-wrapper" style="width: 100%;" data-payment-type="transfer">
    <form action="{$action|escape:'html'}" id="payment-form" class="transferForm" method="post">
        <input type="hidden" name="tpay" value="{$tpay|escape:'html'}">
        <input type="hidden" name="type" value="{$type|escape:'html'}">
        <input
                id="transfer_{$channelId|intval}"
                type="radio" name="tpay_channel_id"
                checked value="{$channelId|intval}" required="required" style="display: none"/>
    </form>
    {if isset($channelId) && $channelId == $blikBnplId}
        <div class="blik-info">
            <details class="tpay-collapsible-desc">
                <summary style="font-size:0.85rem;cursor: pointer">{l s='What is BLIK Pay Later?' d='Modules.Tpay.Shop'}</summary>
                <p style="font-size:0.85rem;margin-top:10px;">{l s='BLIK Pay Later lets you defer payment for online purchases for 30 days, up to 4,000 PLN.' d='Modules.Tpay.Shop'}</p>
            </details>
        </div>
    {/if}

    {include file="module:tpay/views/templates/hook/regulations.tpl"}
</div>
