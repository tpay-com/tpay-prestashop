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
*  @copyright 2010-2022 tpay.com
*  @license   LICENSE.txt
*}
{if isset($surcharge_title)}
    <script>
        $(document).ready(function () {
            {literal}const surchargeTitle = "{/literal}{$surcharge_title}"
            {literal}const surchargeCost = "{/literal}{displayPrice price=$surcharge_cost currency=$currency->id}"

            {if $tpay_is_old_presta}
            legacyCreateSurchargeCost(
                surchargeTitle, surchargeCost
            );
            {else}
            createSurchargeCost(
                surchargeTitle, surchargeCost
            );
            {/if}

            function createSurchargeCost(surchargeTitle, surchargeCost) {
                const orderTotal = $("#orderProductsPanel").find("#orderTotal");
                if (orderTotal.length !== 0) {
                    orderTotal.parent().before('<div class="col-sm text-center">' +
                        '<p class="text-muted m-0"><strong>' + surchargeTitle + '</strong></p>' +
                        '<strong>' + surchargeCost + '</strong>' +
                        '</div>'
                    );
                }
            }

            function legacyCreateSurchargeCost(surchargeTitle, surchargeCost) {
                const orderTotal = $(".panel.panel-total tbody #total_order");
                orderTotal.before('<tr id="total_surcharge">' +
                    '<td class="text-right">' + surchargeTitle + '</td>' +
                    '<td class="amount text-right nowrap">' + surchargeCost + '</td>' +
                    '</tr>')
            }
        });
    </script>
{/if}
<style>
    .standard-refund-display, .partial-refund-display {
        display: none;
    }
</style>
