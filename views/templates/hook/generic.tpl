<div class="tpay-wrapper" style="width: 100%;" data-payment-type="transfer">
    <form action="{$action}" id="payment-form" class="transferForm" method="post">
        <input type="hidden" name="tpay" value="{$tpay}">
        <input type="hidden" name="type" value="{$type}">
        <input
                id="transfer_{$channelId}"
                type="radio" name="tpay_channel_id"
                checked value="{$channelId}" required="required" style="display: none"/>
    </form>
    {if isset($channelId) && $channelId == $blikBnplId}
        <div class="blik-info">
            <details class="tpay-collapsible-desc">
                <summary style="font-size:0.85rem;cursor: pointer">{l s='What is BLIK Pay Later?' d='Modules.Tpay.Shop'}</summary>
                <p style="font-size:0.85rem;margin-top:10px;">{l s='BLIK Pay Later lets you defer payment for online purchases for 30 days, up to 4,000 PLN.' d='Modules.Tpay.Shop'}</p>
            </details>
        </div>
    {/if}

    {include file="module:tpay/views/templates/hook/regulations.tpl"}
</div>
