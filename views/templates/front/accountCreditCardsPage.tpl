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
{extends file='customer/page.tpl'}

{block name='page_title'}
    {l s='My credit cards' d='Modules.Tpay.Shop'}
{/block}

{block name='page_content'}
	<section id="content" class="page-content">
		<div class="row">

            {if $customer_credit_cards}
				<div class="col-xs-12">
					<p>{l s='List of saved credit cards.' d='Modules.Tpay.Shop'}</p>
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
									<span>{l s='Delete' d='Modules.Tpay.Shop'}</span>
								</a>
							</div>

						</article>
					</div>
                {/foreach}

            {else}
				<div class="col-xs-12">
					<h3>
                        {l s='No stored credit cards' d='Modules.Tpay.Shop'}
					</h3>
				</div>
            {/if}

		</div>
	</section>
{/block}
