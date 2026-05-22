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

@author Krajowy Integrator Płatności S.A.*}
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
