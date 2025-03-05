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
<div class="tpay-wrapper tpay-blik-wrapper" data-payment-type="blik">
    {if $blik_type === 'redirect'}
        {l s='You will be redirected to the payment gateway.' mod='tpay'}
    {else}
      <form action="{$blik_moduleLink}" method="POST" id="tpay-blik-form" name="tpay-blik-form">
        <input type="hidden" name="cart_id" class="blikId" value="{$blik_order_id}"/>

        <div class="tpay-radio-payments tpay-radio-payments--blik">
            {if isset($blik_saved_aliases) && !empty($blik_saved_aliases)}
              <div class="tpay-radio-payments__item tpay-blik--use">
                <div class="tpay-radio-payments__radio tpay-radio-payments__radio--active">

                  <img width="40" class="tpay-logo--small img-fluid"
                       src="{$assets_path|escape:'htmlall':'UTF-8'}views/img/tpay--small.svg" alt="TPAY">

                  <div class="custom-radio">
                    <input type="radio" name="blikOption" id="blikUse" value="use" checked="checked"/>
                    <span></span>
                  </div>

                  <div class="tpay-radio-payments__content">
                    <label for="blikUse">
                        {l s='Use saved blik code' mod='tpay'}
                    </label>
                    <div class="tpay-radio-payments__description">
                      <p>{l s='Why don\'t I have to enter the code?' mod='tpay'}</p>
                      <div class="tpay-blik-response"></div>
                      <div class="tpay-buttons-holder">
                          {include file="module:tpay/views/templates/_partials/preloader.tpl"}
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            {/if}

          <div class="tpay-radio-payments__item tpay-blik--new">
            <div class="tpay-radio-payments__radio
					{if empty($blik_saved_aliases)} tpay-radio-payments__radio--active{/if}">

              <img width="40" class="tpay-logo--small img-fluid"
                   src="{$assets_path|escape:'htmlall':'UTF-8'}views/img/tpay--small.svg" alt="TPAY">

              <div class="custom-radio">
                <input type="radio" name="blikOption" id="blikNew" value="new"
                        {if empty($blik_saved_aliases)} checked="checked"{/if}/>
                <span></span>
              </div>

              <div class="tpay-radio-payments__content">
                <label for="blikNew">
                    {l s='Enter BLIK code' mod='tpay'}
                </label>

                <div class="tpay-radio-payments__description">

                  <div class="tpay-blik">
                    <div class="tpay-input-blik-code">
                      <div class="tpay-input-wrapper">
                        <input id="blik_code"
                               name="blik_code"
                                {literal}
                                  pattern="[0-9]{6}"
                                {/literal}
                               type="text"
                               autocomplete="off"
                               maxlength="6"
                               minlength="6"
                               placeholder="000000"
                               tabindex="1"
                               value=""
                               class="tpay-input-value tpay-blik-input"
                               oninvalid="setCustomValidity('{l s='Enter a valid blik code' mod='tpay'}');"
                               onchange="setCustomValidity('')"
                        />
                      </div>
                    </div>
                  </div>

                  <div class="tpay-blik-response"></div>

                  <div class="tpay-buttons-holder">
                      {include file="module:tpay/views/templates/_partials/preloader.tpl"}
                  </div>

                </div>
              </div>

              <div id="responseMessages"></div>
            </div>
          </div>

          <div id="blik-rr" style="display: none">
					<span class="tpay-error">
						{l s='Please accept the store rules' mod='tpay'}
					</span>
          </div>
            {include file="module:tpay/views/templates/hook/regulations.tpl"}
        </div>
      </form>
    {/if}

  <script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function (event) {
      $(document).ready(function () {
        $(function () {
          $('[data-toggle="tooltip"]').tooltip()
        })
      });
    });
  </script>

</div>
