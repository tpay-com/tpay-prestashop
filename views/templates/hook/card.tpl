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
*  @copyright 2010-2022 tpay.com
*  @license   LICENSE.txt
*}
<div class="tpay-wrapper tpay-card-wrapper" data-payment-type="card">
	<form method="post" id="card_payment_form" name="card_payment_form">
		{if $card_type === 'widget'}
			{if isset($saved_cards) && !empty($saved_cards)}
				<div class="row tpay-cards">
					<div class="col-xs-12">
						<h6 style="margin-bottom: 26px; font-size: .8375rem;">
							{l s='List of saved credit cards.' mod='tpay'}
						</h6>
					</div>

					{foreach from=$saved_cards item=card}
						<div class="tpay-cards__item" data-card-id="{$card['id']}">
							<div class="tpay-cards__item-inner">

								<div class="custom-radio">
									<input type="radio" name="savedId" id="cardN{$card['id']}" value="{$card['id']}"/>
									<span></span>
								</div>

								<label for="cardN{$card['id']}" style="text-align:left; margin: 0;">
									**** {$card['card_shortcode']}
									<p>{$card['card_vendor']}</p>
								</label>

								<div class="" style="margin-left: auto;">
									<a href="{url entity='module' name='tpay' controller='savedCards' params=['action' => 'deleteCard','ajax' => true]}"
									   data-link-action="delete-credit-card" data-id="{$card['id']}"
									   data-token="{Tools::getToken()}">
										<i class="material-icons"></i>
									</a>
								</div>

							</div>
						</div>
					{/foreach}

					<div class="tpay-cards__item tpay-card__new-card">
						<div class="tpay-cards__item-inner">
							<div class="custom-radio">
								<input type="radio" name="savedId" id="newCard" value="0"/>
								<span></span>
							</div>

							<label for="newCard">
								{l s='Add new card' mod='tpay'}
							</label>
						</div>
					</div>

				</div>
			{/if}
			<div class="tpay-card-new {if isset($saved_cards) && !empty($saved_cards)}tpay-animated tpay-fadeOut{/if}">
				<div class="tpay-card">
					<img width="40" class="tpay-logo--small img-fluid"
						 src="{$assets_path|escape:'htmlall':'UTF-8'}views/img/tpay--small.svg" alt="TPAY">

					<input type="hidden" name="carddata" id="carddata" value=""/>
					<input type="hidden" name="card_vendor" id="card_vendor" value=""/>
					<input type="hidden" name="card_hash" id="card_hash" value=""/>
					<input type="hidden" name="card_short_code" id="card_short_code" value=""/>

					<div class="tpay-input-credit-card-number">
						<label for="card-number" class="tpay-label-info">{l s='Card number' mod='tpay'}</label>
						<input id="card_number" pattern="[0-9\s]*" autocompletetype="cc-number" size="30"
							   type="tel" autocomplete="off" maxlength="23"
							   placeholder="0000 0000 0000 0000" tabindex="1"
							   class="tpay-input-value"/>
						<div class="tpay-card-icon "></div>
					</div>

					<div class="tpay-card__wrap">
						<div id="exp-container">
							<label for="card-exp" class="tpay-label-info">{l s='Expiration date' mod='tpay'}</label>
							<input id="expiry_date" maxlength="9" type="tel" placeholder="00 / 0000"
								   autocomplete="off" autocompletetype="cc-exp" tabindex="2" value=""
								   class="tpay-input-value tpay-card__input-small"/>
						</div>

						<div id="exp-container">
							<label for="card-cvc" class="tpay-label-info">
								{l s='CVC' mod='tpay'}
								<img src="{$tpay_path|escape:'htmlall':'UTF-8'}/img/info.svg"
									 alt="{l s='Preload' mod='tpay'}"
									 data-toggle="tooltip" data-placement="bottom"
									 title="{l s='For MasterCard, Visa or Discover, it\'s the last three digits in the signature area on the back of your card.' mod='tpay'}"/>
							</label>
							<input id="cvc" maxlength="3" type="tel" autocomplete="off" autocompletetype="cc-cvc"
								   placeholder="000" tabindex="3" value=""
								   class="tpay-input-value tpay-card__input-small"/>
						</div>
					</div>
				</div>

				{if !$customer.is_guest}
					<div class="row">
						<div class="tpay-card__save col-xs-12">
							<div class="custom-checkbox">
								<input type="checkbox" id="card_save" name="card_save"/>
								<span><i class="material-icons rtl-no-flip checkbox-checked"></i></span>
							</div>
							<div class="condition-label" style="margin-top: 1.325rem;">
								<label for="card_save" class="tpay-info-label" title="{l s='Save my card' mod='tpay'}">
									{l s='Save my card' mod='tpay'}
								</label>
							</div>
						</div>
					</div>
				{/if}


			</div>
		{else}
			{l s='You will be redirected to the payment gateway.' mod='tpay'}
		{/if}

		<div style="max-width:390px;">
			<div id="info_msg_not_supported" style="display: none;">
					<span class="tpay-error">
	                    {l s='No support for cards' mod='tpay'}
					</span>
			</div>
			<div id="info_msg_not_valid" style="display: none">
					<span class="tpay-error">
						{l s='Incorrect card data' mod='tpay'}
					</span>
			</div>
			<div id="info_msg_cause" style="display: none">
					<span class="tpay-error">
						{l s='Please accept the store rules' mod='tpay'}
					</span>
			</div>
			<input type="hidden" name="redirect_type" id="redirect_type" value="{$card_type}"/>
		</div>

		{if $card_type === 'widget'}
			{include file="module:tpay/views/templates/hook/regulations.tpl"}
		{/if}
	</form>

	<script type="text/javascript">
		document.addEventListener("DOMContentLoaded", function (event) {
			$(document).ready(function () {
				new CardPayment(redirect_path, rsa_key);


				$(function () {
					$('[data-toggle="tooltip"]').tooltip()
				})
			});
		});
	</script>


</div>
