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
					<p>
						{l s='Please select the card you wish to use for payment' d='Modules.Tpay.Shop'}
					</p>
					<hr class="separator-line">

					{foreach from=$saved_cards item=card}
						<div class="tpay-cards__item" data-card-id="{$card['id']}">
							<div class="tpay-cards__item-inner">

								<div class="custom-radio">
									<input type="radio" name="savedId" id="cardN{$card['id']}" value="{$card['id']}"/>
									<span></span>
								</div>

								<label class="added-card" for="cardN{$card['id']}" style="text-align:left; margin: 0;">
									<img src="{$assets_path|escape:'htmlall':'UTF-8'}views/img/{$card.card_vendor|lower}-card-icon.svg"/>
									**** {$card['card_shortcode']}
								</label>

								<div class="delete-card" style="margin-left: auto;">
									<a href="{url entity='module' name='tpay' controller='savedCards' params=['action' => 'deleteCard','ajax' => true]}"
									   data-link-action="delete-credit-card" data-id="{$card['id']}"
									   data-token="{Tools::getToken()}">
										<img src="{$assets_path|escape:'htmlall':'UTF-8'}views/img/trash.svg"/>
									</a>
								</div>
							</div>
							<hr class="separator-line">
						</div>
					{/foreach}

					<div class="tpay-cards__item tpay-card__new-card">
						<div class="tpay-cards__item-inner">
							<div class="custom-radio">
								<input type="radio" name="savedId" id="newCard" value="0"/>
								<span></span>
							</div>

							<label for="newCard">
								{l s='Pay with a different card' d='Modules.Tpay.Shop'}
							</label>
						</div>
					</div>
					<hr class="separator-line" style="display: block">
				</div>
			{/if}
			<div class="tpay-card-new {if isset($saved_cards) && !empty($saved_cards)}tpay-animated tpay-fadeOut other-cards{/if}">
				<p>
					{l s='Please enter your payment card details below' d='Modules.Tpay.Shop'}
				</p>
				<hr class="separator-line">
				<div class="tpay-card {if isset($saved_cards) && !empty($saved_cards)}other-cards{/if}">
					<input type="hidden" name="carddata" id="carddata" value=""/>
					<input type="hidden" name="card_vendor" id="card_vendor" value=""/>
					<input type="hidden" name="card_hash" id="card_hash" value=""/>
					<input type="hidden" name="card_short_code" id="card_short_code" value=""/>

					<div class="tpay-input-credit-card-number">
						<label for="card_number" class="tpay-label-info">{l s='Card number' d='Modules.Tpay.Shop'}</label>
						<input
							id="card_number"
							class="tpay-input-value"
							type="text"
							tabindex="1"
							inputmode="numeric"
							autocomplete="cc-number"
							maxlength="19"
							placeholder="0000 0000 0000 0000"
						/>
						<div class="tpay-card-icon "></div>
					</div>

					<div class="tpay-card__wrap">
						<div id="exp-container">
							<label for="expiry_date" class="tpay-label-info">{l s='Expiration date' d='Modules.Tpay.Shop'}</label>
							<input
									id="expiry_date"
									type="text"
									placeholder="MM/RR"
									maxlength="9"
									tabindex="2"
									inputmode="numeric"
									oninput="
                                        let v = this.value.replace(/\D/g, '').slice(0,4);
                                        if (v.length >= 3) {
                                          v = v.slice(0,2) + '/' + v.slice(2);
                                        }
                                        this.value = v;
                                    "
							/>
						</div>

						<div id="exp-container">
							<label for="cvc" class="tpay-label-info">
								{l s='CVV2/CVC2' d='Modules.Tpay.Shop'}
							</label>
							<div class="tooltip-container">
								<input
									id="cvc"
									maxlength="3"
									type="text"
									inputmode="numeric"
									pattern="[0-9]*"
									autocomplete="cc-csc"
									placeholder="123"
									tabindex="3"
									class="tpay-input-value tpay-card__input-small"
									oninput="this.value = this.value.replace(/\D/g, '')"
								/>
								<p class="show-tooltip">
									<img src="{$tpay_path|escape:'htmlall':'UTF-8'}/img/info.svg"
										 alt="{l s='Preload' d='Modules.Tpay.Shop'}"/>
									<span class="tooltip-text"> {l s='The CVV2/CVC2 code is a 3-digit number located on the back of Mastercard and Visa cards.' d='Modules.Tpay.Shop'} </span>
								</p>
							</div>
						</div>
					</div>
				</div>
				{if !$customer.is_guest}
					<div class="row">
						<div class="tpay-card__save">
							<div class="custom-checkbox">
								<input type="checkbox" id="card_save" name="card_save"/>
								<span><i class="material-icons rtl-no-flip checkbox-checked"></i></span>
							</div>
							<div class="condition-label" style="margin-top: 1.325rem;">
								<label for="card_save" class="tpay-info-label" title="{l s='Save my card' d='Modules.Tpay.Shop'}">
									{l s='Save my card' d='Modules.Tpay.Shop'}
								</label>
							</div>
						</div>
					</div>
				{/if}
				<hr class="separator-line">
			</div>
		{else}
			{l s='You will be redirected to the payment gateway.' d='Modules.Tpay.Shop'}
		{/if}

		<div style="max-width:390px;">
			<div id="info_msg_not_supported" style="display: none;">
					<span class="tpay-error">
	                    {l s='No support for cards' d='Modules.Tpay.Shop'}
					</span>
			</div>
			<div id="info_msg_not_valid" style="display: none">
					<span class="tpay-error">
						{l s='Incorrect card data' d='Modules.Tpay.Shop'}
					</span>
			</div>
			<div id="info_msg_cause" style="display: none">
					<span class="tpay-error">
						{l s='Please accept the store rules' d='Modules.Tpay.Shop'}
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
