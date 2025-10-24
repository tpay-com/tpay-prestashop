<div class="tpay-wrapper" style="width: 100%;" data-payment-type="transfer">
    <form action="{$action}" id="payment-form" class="transferForm" method="post">
        <input type="hidden" name="tpay" value="{$tpay}">
        <input type="hidden" name="type" value="{$type}">
        <input
                id="transfer_{$channelId}"
                type="radio" name="tpay_channel_id"
                checked value="{$channelId}" required="required" style="display: none"/>
    </form>
    {if isset($channelId) && $channelId == 84}
        <div class="blik-info">
            <details class="tpay-collapsible-desc">
                <summary style="font-size:0.85rem;cursor: pointer">{l s='What is BLIK Pay Later?' d='Modules.Tpay.Shop'}</summary>
                <p style="font-size:0.85rem;margin-top:10px;">{l s='Shop now with BLIK Pay Later and settle your payment within 30 days – all in your bank\'s app.' d='Modules.Tpay.Shop'}</p>
            </details>
        </div>
    {/if}

    {include file="module:tpay/views/templates/hook/regulations.tpl"}
</div>
