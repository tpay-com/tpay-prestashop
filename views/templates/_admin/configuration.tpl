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
        let tpayPeKaoSimulator = $('input[name=TPAY_PEKAO_INSTALLMENTS_ACTIVE]');

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

        function checkTpayPeKaoSimulator(state) {
            const tpayMerchantId = $('input[name=TPAY_MERCHANT_ID]').parents('.form-group');
            const tpayProductPageSimulator = $('input[name=TPAY_PEKAO_INSTALLMENTS_PRODUCT_PAGE]').parents('.form-group');
            const tpayCheckoutSimulator = $('input[name=TPAY_PEKAO_INSTALLMENTS_CHECKOUT]').parents('.form-group');
            const tpayShoppingCartSimulator = $('input[name=TPAY_PEKAO_INSTALLMENTS_SHOPPING_CART]').parents('.form-group');

            if (state === '1') {
                tpayMerchantId.show();
                tpayProductPageSimulator.show();
                tpayCheckoutSimulator.show();
                tpayShoppingCartSimulator.show();
            } else {
                tpayMerchantId.hide();
                tpayProductPageSimulator.hide();
                tpayCheckoutSimulator.hide();
                tpayShoppingCartSimulator.hide();
            }
        }

        checkTpaySurcharge($('input[name=TPAY_SURCHARGE_ACTIVE]:checked').val());

        tpaySurcharge.change(function () {
            checkTpaySurcharge($(this).val());
        })

        tpayPeKaoSimulator.change(function () {
            checkTpayPeKaoSimulator($(this).val());
        });
    });
</script>
<style>
    #content > .bootstrap > .alert {
        display: none
    }
</style>
