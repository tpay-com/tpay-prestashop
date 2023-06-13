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
	        {literal}const surchargeCost = "{/literal}{$surcharge_cost}"
			createSurchargeCost(
				surchargeTitle, surchargeCost
			);

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
		});
	</script>
{/if}
<style>
	.standard-refund-display, .partial-refund-display {
		display: none;
    }
</style>
