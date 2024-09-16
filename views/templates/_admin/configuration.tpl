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
{literal}
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            let tpaySurcharge = $('input[name=TPAY_SURCHARGE_ACTIVE]');
            let tpayPeKaoSimulator = $('input[name=TPAY_PEKAO_INSTALLMENTS_ACTIVE]');
            let tpayPeKaoMerchant = $('input[name=TPAY_MERCHANT_ID]');
            let tpayTransfer = $('input[name=TPAY_REDIRECT_TO_CHANNEL]');
            var selectElementOrder = $('#TPAY_CUSTOM_ORDER\\[\\]');
            var selectElement = $('#TPAY_GENERIC_PAYMENTS\\[\\]');

            function removeSpaces(element) {
                element.on('change', function () {
                    let value = $(this).val();
                    let newValue = value.replace(/\s+/g, '');
                    $(this).val(newValue);
                });
            }

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

            function initializeSortableOrder() {
                selectElementOrder.find('option').prop('selected', true);
                selectElementOrder.hide();

                var listElementOrder = $('<ul id="sortable-order-list" class="sortable-list"></ul>').insertAfter(selectElementOrder);

                selectElementOrder.find('option').each(function () {
                    var listItem = $('<li class="ui-state-default" id="option-' + $(this).val() + '" title="Drag to reorder">' +
                        $(this).text() + '</li>');
                    listElementOrder.append(listItem);
                });

                listElementOrder.sortable({
                    placeholder: "ui-state-highlight",
                    stop: function (event, ui) {
                        var sortedIDs = $(this).sortable('toArray', {attribute: 'id'});

                        selectElementOrder.empty();
                        $.each(sortedIDs, function (index, value) {
                            var optionValue = value.replace('option-', '');
                            var optionText = $('#sortable-order-list li#' + value).text();
                            selectElementOrder.append('<option value="' + optionValue + '" selected>' + optionText + '</option>');
                        });
                    }
                }).disableSelection();

                listElementOrder.find('li').attr('title', 'Drag to reorder');
            }

            function checkTpayTransfer(state) {
                const tpayCustomOrder = $('#sortable-order-list');

                if (state === '1') {
                    tpayCustomOrder.show();
                } else {
                    tpayCustomOrder.hide();
                }
            }

            function initializeSortableGenericPayments() {
                selectElement.hide();

                var listElement = $('<ul id="sortable-list" class="sortable-list"></ul>').insertAfter(selectElement);

                selectElement.find('option').each(function () {
                    var isChecked = $(this).is(':selected');
                    var listItem = $('<li class="ui-state-default" id="option-' + $(this).val() + '" title="Drag to reorder">' +
                        '<input type="checkbox" class="select-checkbox" value="' + $(this).val() + '"' + (isChecked ? ' checked' : '') + '>' +
                        $(this).text() + '</li>');
                    listElement.append(listItem);
                });

                listElement.sortable({
                    placeholder: "ui-state-highlight",
                    stop: function (event, ui) {
                        var sortedIDs = $(this).sortable('toArray', {attribute: 'id'});

                        selectElement.empty();
                        $.each(sortedIDs, function (index, value) {
                            var optionValue = value.replace('option-', '');
                            var optionText = $('#sortable-list li#' + value).text();
                            var isChecked = $('#sortable-list li#' + value).find('.select-checkbox').is(':checked');
                            selectElement.append('<option value="' + optionValue + '" ' + (isChecked ? 'selected' : '') + '>' + optionText + '</option>');
                        });
                    }
                }).disableSelection();

                $(document).on('change', '.select-checkbox', function () {
                    var checkboxValue = $(this).val();
                    if ($(this).is(':checked')) {
                        selectElement.find('option[value="' + checkboxValue + '"]').prop('selected', true);
                    } else {
                        selectElement.find('option[value="' + checkboxValue + '"]').prop('selected', false);
                    }
                });
            }

            checkTpaySurcharge($('input[name=TPAY_SURCHARGE_ACTIVE]:checked').val());
            checkTpayPeKaoSimulator($('input[name=TPAY_PEKAO_INSTALLMENTS_ACTIVE]:checked').val());
            initializeSortableOrder();
            checkTpayTransfer($('input[name=TPAY_REDIRECT_TO_CHANNEL]:checked').val());
            initializeSortableGenericPayments();
            removeSpaces($('input[name=TPAY_CLIENT_ID]'));
            removeSpaces($('input[name=TPAY_SECRET_KEY]'));
            removeSpaces($('input[name=TPAY_MERCHANT_SECRET]'));
            removeSpaces($('input[name=TPAY_CARD_RSA]'));
            removeSpaces(tpayPeKaoMerchant);

            tpaySurcharge.change(function () {
                checkTpaySurcharge($(this).val());
            });

            tpayPeKaoSimulator.change(function () {
                checkTpayPeKaoSimulator($(this).val());
            });

            tpayTransfer.change(function () {
                checkTpayTransfer($(this).val());
            });

            tpayPeKaoMerchant.on('keyup', function () {
                var value = this.value;
                this.value = value.replace(/\D/g, '');
            });
        });
    </script>
    <style>
        .sortable-list {
            list-style-type: none;
            margin: 0;
            border: 1px solid;
            max-width: 300px;
            padding: 10px;
            max-height: 400px;
            overflow-y: auto;
        }

        .sortable-list li {
            display: flex;
            align-items: center;
            padding: 5px;
            border-bottom: 1px solid #eee;
        }

        #sortable-list .select-checkbox {
            margin: 5px;
        }

        .sortable-list li:last-child {
            border-bottom: none;
        }
    </style>
{/literal}
