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
<div class="tpay-wrapper tpay-widget-wrap" data-payment-type="applePay">

	<script src="https://applepay.cdn-apple.com/jsapi/v1/apple-pay-sdk.js"></script>

	<style>
        apple-pay-button {
            --apple-pay-button-width: 260px;
            --apple-pay-button-height: 38px;
            --apple-pay-button-border-radius: 3px;
            --apple-pay-button-padding: 0px 0px;
            --apple-pay-button-box-sizing: border-box;
        }
	</style>
	<apple-pay-button buttonstyle="black" type="buy" locale="pl-PL"></apple-pay-button>

    {include file="module:tpay/views/templates/hook/regulations.tpl"}
</div>
