<link rel="stylesheet" href="{$assets_path|escape:'htmlall':'UTF-8'}views/css/payment.css">

<div class="payment-confirmation-container error" style="display: block !important;">
    <div class="icon-wrapper">
        <img src="{$assets_path|escape:'htmlall':'UTF-8'}views/img/warning.svg" alt="Icon"/>

    </div>
    <div class="message">
        <p class="title">{l s='Payment error' d='Modules.Tpay.Shop'}</p>
        {if isset($errors)}
            <p>{$errors nofilter}</p>
        {/if}
    </div>
    <div class="text-center mt-3">
        <a href="{$link->getModuleLink('tpay', 'payment', ['retry_order' => $retry_order], true)}"
           class="btn btn-primary small">
            Ponów płatność
        </a>
    </div>
    <div class="underline"></div>
</div>