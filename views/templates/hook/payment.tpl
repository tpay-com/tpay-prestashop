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
{if isset($gateways) && $gateways}

	<form action="{$action}" id="payment-form" class="transferForm">
		<input type="hidden" name="tpay" value="{$tpay}">
		<input type="hidden" name="type" value="{$type}">
		<ul class="tpay-payment-gateways tpay-payment-gateways--grid">
            {foreach $gateways as $p}
				<label for="transfer_{$p.id|escape:'htmlall':'UTF-8'}" class="tpay-payment-gateways__item">
					<input
							id="transfer_{$p.id|escape:'htmlall':'UTF-8'}"
							type="radio" name="tpay_transfer_id"
							value="{$p.id|escape:'htmlall':'UTF-8'}" required="required" style="display: none"/>
					<div class="tpay-payment-gateways__item-inner">
						<img class="img-fluid col-xs-12" src="{$p.img|escape:'htmlall':'UTF-8'}"
						     alt="{$p.name|escape:'htmlall':'UTF-8'}" width="80"/>
						<span class="text-xs-center">{$p.name|escape:'htmlall':'UTF-8'}</span>
					</div>
				</label>
            {/foreach}
		</ul>
	</form>

	<div id="transfer-error" style="display: none">
		<span class="tpay-error">
			{l s='Please choose payment gateway' mod='tpay'}
		</span>
	</div>

    {include file="module:tpay/views/templates/hook/regulations.tpl"}
{else}
    {l s='No active payment gateways' mod='tpay'}
{/if}
</div>
