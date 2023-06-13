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
<script type="text/javascript">
	document.addEventListener("DOMContentLoaded", function (event) {
		$(document).ready(function () {
            {literal}const surchargeTitle = "{/literal}{$surcharge_title}";
            {literal}const surchargeCost = "{/literal}{$surcharge_cost}";

			createBackOfficeSurchargeCost(surchargeTitle, surchargeCost);
			createFrontOfficeSurchargeCost(surchargeTitle, surchargeCost);

			function createBackOfficeSurchargeCost(surchargeTitle, surchargeCost) {
				const orderTotal = $(".order-confirmation-table").find(".total-value");
				if (orderTotal.length !== 0) {
					orderTotal.before('<tr>' +
						'<td>' + surchargeTitle + '</td>' +
						'<td>' + surchargeCost + '</td>' +
						'</tr>'
					);
				}
			}

			function createFrontOfficeSurchargeCost(surchargeTitle, surchargeCost) {
				const orderTotal = $("#order-products").find(".line-shipping");
				if (orderTotal.length !== 0) {
					orderTotal.before('<tr class="text-xs-right line-surcharge">' +
						'<td colspan="3">' + surchargeTitle + '</td>' +
						'<td>' + surchargeCost + '</td>' +
						'</tr>'
					);
				}
			}

		});
	});
</script>
