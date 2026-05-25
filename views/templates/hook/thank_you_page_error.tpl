{**MIT License

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
            {l s='Retry payment' d='Modules.Tpay.Shop'}
        </a>
    </div>
    <div class="underline"></div>
</div>