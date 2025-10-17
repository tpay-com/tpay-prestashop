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
<style>
    .loader {
        width: 210px;
        height: 246px;
        animation: spin 2s linear infinite;
        margin-bottom: 25px;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
</style>
{*<img src="{$tpay_path|escape:'htmlall':'UTF-8'}/img/loading.png" alt="{l s='Redirecting' d='Modules.Tpay.Shop'}" class="loader"/>*}
<h4>{l s='Please wait... In a moment you will be redirected to secure transaction panel.' d='Modules.Tpay.Shop'}</h4>
<h5>{l s='If your browser does not redirect you automatically press the button below.' d='Modules.Tpay.Shop'}</h5>
{*{$tpay_form}*}
