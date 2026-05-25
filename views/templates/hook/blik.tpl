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
<div class="tpay-wrapper tpay-blik-wrapper" data-payment-type="blik">
    {if $blik_type === 'redirect'}
        {l s='You will be redirected to the payment gateway.' d='Modules.Tpay.Shop'}
    {else}
        <form action="{$blik_moduleLink|escape:'html'}" method="POST" id="tpay-blik-form" name="tpay-blik-form">
            <input type="hidden" name="cart_id" class="blikId" value="{$blik_order_id|intval}"/>

            <div class="tpay-radio-payments tpay-radio-payments--blik">
                {if isset($blik_saved_aliases) && !empty($blik_saved_aliases)}
                    <div class="tpay-radio-payments__item tpay-blik--use">
                        <div class="tpay-radio-payments__radio tpay-radio-payments__radio--active">

                            <div class="custom-radio">
                                <input type="radio" name="blikOption" id="blikUse" value="use" checked="checked"/>
                                <span></span>
                            </div>

                            <div class="tpay-radio-payments__content">
                                <img width="40"
                                     src="{$assets_path|escape:'htmlall':'UTF-8'}views/img/tpay--small.svg" alt="TPAY">
                                <label for="blikUse">
                                    {l s='Use saved blik code' d='Modules.Tpay.Shop'}
                                </label>
                                <p class="show-blik-info">
                                    {l s='Why don\'t I have to enter the code?' d='Modules.Tpay.Shop'}
                                    <span class="tooltip-text"> {l s='You do not need to enter the BLIK code, because you linked your account to this device during one of the previous payments. The payment still requires confirmation in the app.' d='Modules.Tpay.Shop'} </span>
                                </p>
                                <div class="tpay-blik-response"></div>
                                <div class="tpay-buttons-holder">
                                    {include file="module:tpay/views/templates/_partials/preloader.tpl"}
                                </div>
                            </div>

                        </div>
                    </div>
                {/if}

                <div class="tpay-radio-payments__item tpay-blik--new">
                    <div class="tpay-radio-payments__radio
					{if empty($blik_saved_aliases)} tpay-radio-payments__radio--active{/if}">

                        {if isset($blik_saved_aliases) && !empty($blik_saved_aliases)}
                        <div class="custom-radio">
                            <input type="radio" name="blikOption" id="blikNew" value="new"
                                    {if empty($blik_saved_aliases)} checked="checked"{/if}/>
                            <span></span>
                        </div>
                        {/if}

                        <div class="tpay-radio-payments__content">
                            {if empty($blik_saved_aliases)}
                                <img width="40"
                                     src="{$assets_path|escape:'htmlall':'UTF-8'}views/img/tpay--small.svg" alt="TPAY">
                            {/if}
                            <label for="blikNew">
                                {l s='Use BLIK code' d='Modules.Tpay.Shop'}
                            </label>


                            <div class="tpay-radio-payments__description">
                                <p>
                                    {l s='Enter BLIK code' d='Modules.Tpay.Shop'}
                                </p>
                                <div class="tpay-blik">
                                    <div class="tpay-input-blik-code">
                                        <div class="tpay-input-wrapper">
                                            <input id="blik_code"
                                                   name="blik_code"
                                                    {literal}
                                                        pattern="[0-9]{7}"
                                                    {/literal}
                                                   type="text"
                                                   autocomplete="off"
                                                   maxlength="7"
                                                   minlength="7"
                                                   placeholder="000000"
                                                   tabindex="1"
                                                   value=""
                                                   class="tpay-input-value tpay-blik-input"
                                                   oninvalid="setCustomValidity('{l s='Enter a valid blik code' d='Modules.Tpay.Shop'}');"
                                                   onchange="setCustomValidity('')"
                                            />
                                            <label id="blik-error" class="text-danger"
                                                   style="display: none; font-size: 11px; text-align: center; width: 100%;">{l s='Incorrect BLIK code' d='Modules.Tpay.Shop'}</label>
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
						{l s='Please accept the store rules' d='Modules.Tpay.Shop'}
					</span>
                </div>
                {include file="module:tpay/views/templates/hook/regulations.tpl"}
            </div>
        </form>
    {/if}

    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function (event) {
            $(document).ready(function () {

                new BlikPayment();

                $(function () {
                    $('[data-toggle="tooltip"]').tooltip()
                })
            });
        });
    </script>

</div>
