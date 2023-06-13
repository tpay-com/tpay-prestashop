{extends file='customer/page.tpl'}

{block name='page_title'}
    {l s='My credit cards' mod='tpay'}
{/block}

{block name='page_content'}
	<section id="content" class="page-content">
		<div class="row">

            {if $customer_credit_cards}
				<div class="col-xs-12">
					<p>{l s='List of saved credit cards.' mod='tpay'}</p>
				</div>
                {foreach from=$customer_credit_cards item=card}
					<div class="col-lg-4 col-md-6 col-sm-6" data-card-id="{$card['id']}">
						<article class="tpay-account-cards">

							<div class="tpay-account-cards__body">
								<h4>**** {$card['card_shortcode']}</h4>
								<p>{$card['card_vendor']}</p>
							</div>

							<div class="tpay-account-cards__footer">
								<a href="{url entity='module' name='tpay' controller='savedCards' params=['action' => 'deleteCard','ajax' => true]}"
								   data-link-action="delete-credit-card" data-id="{$card['id']}" data-token="{Tools::getToken()}">
									<i class="material-icons"></i>
									<span>{l s='Delete' mod='tpay'}</span>
								</a>
							</div>

						</article>
					</div>
                {/foreach}

            {else}
				<div class="col-xs-12">
					<h3>
                        {l s='No stored credit cards' mod='tpay'}
					</h3>
				</div>
            {/if}

		</div>
	</section>
{/block}
