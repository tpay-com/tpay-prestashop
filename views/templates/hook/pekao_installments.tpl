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

<div class="tpay-wrapper" style="width: 100%;" data-payment-type="transfer">
    {if isset($available_channels) && $available_channels}

      <form action="{$action}" id="installment-payment-form" class="transferForm">
        <input type="hidden" name="tpay" value="{$tpay}">
        <input type="hidden" name="type" value="{$type}">
        <ul class="tpay-payment-gateways tpay-payment-gateways--grid">
            {foreach $available_channels as $id}
              <label for="transfer_{$id}" class="tpay-payment-gateways__item installments">
                <input
                  id="transfer_{$id}"
                  type="radio" name="tpay_channel_id"
                  value="{$id}" required="required" style="display: none"/>
                <div class="tpay-payment-gateways__item-inner">
                  <img class="img-fluid col-xs-12" src="https://secure.sandbox.tpay.com/_/banks/b{$id}e.png"/>
                </div>
              </label>
            {/foreach}
        </ul>
      </form>

      <div class="transfer-error" style="display: none">
		<span class="tpay-error">
			{l s='Please choose payment gateway' mod='tpay'}
		</span>
      </div>

        {include file="module:tpay/views/templates/hook/regulations.tpl"}
    {else}
        {l s='No active installment gateways' mod='tpay'}
    {/if}
</div>
