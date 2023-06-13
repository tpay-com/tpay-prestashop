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

	$(document).ready(function () {

		let tpaySurcharge = $('input[name=TPAY_SURCHARGE_ACTIVE]');

		function checkTpaySurcharge(state) {
			const tpaySurchargeType = $('input[name=TPAY_SURCHARGE_TYPE]').parents('.form-group');
			const tpaySurchargeValue = $('input[name=TPAY_SURCHARGE_VALUE]').parents('.form-group');

			if (state === '1') {
				tpaySurchargeType.show();
				tpaySurchargeValue.show();
			} else {
				tpaySurchargeType.hide();
				tpaySurchargeValue.hide();
			}
		}

		checkTpaySurcharge($('input[name=TPAY_SURCHARGE_ACTIVE]:checked').val());

		tpaySurcharge.change(function () {
			checkTpaySurcharge($(this).val());
		})

	});

</script>
<style>
    #content > .bootstrap > .alert {
	    display: none
    }
</style>
