<div class="tpay-wrapper" style="width: 100%;" data-payment-type="transfer">
    <form action="{$action}" id="payment-form" class="transferForm">
        <input type="hidden" name="tpay" value="{$tpay}">
        <input type="hidden" name="type" value="{$type}">
        <input
                id="transfer_{$channelId}"
                type="radio" name="tpay_channel_id"
                checked value="{$channelId}" required="required" style="display: none"/>
    </form>

    {include file="module:tpay/views/templates/hook/regulations.tpl"}
</div>
