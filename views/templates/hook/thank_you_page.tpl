{if $blikUrl}
    <link rel="stylesheet" href="{$assets_path|escape:'htmlall':'UTF-8'}views/css/payment.css">
    <div class="payment-section">
        <div class="title-wrapper">
            <h3 class="page-title">{l s='Payment with Tpay' mod='tpay'}:</h3>
            <div class="page-title-line"></div>
        </div>
        <div class="payments-container">

            {*            BLIK*}
            <div class="blik_payment">
                <input
                        class="payment-input"
                        type="radio"
                        name="payment"
                        value="blik"
                        id="blik-radio"
                        checked
                >
                <div class="payment-option payment-option-blik">
                    <label class="payment-label" for="blik-radio">
                        <span class="radio-mark"></span>
                        <span class="payment-title">BLIK</span>
                        <img
                                src="{$assets_path|escape:'htmlall':'UTF-8'}views/img/tpay--small.svg"
                                alt="Logo Blik"
                        />
                        <img
                                src="{$assets_path|escape:'htmlall':'UTF-8'}views/img/check.svg"
                                alt="Check ico"
                                class="check-ico"
                        />
                    </label>
                    <div class="blik-code-section" style="display: none">
                        <label for="blik-code" class="blik-code-label">{l s='Enter BLIK code' mod='tpay'}</label>
                        <input type="hidden" id="transaction_counter" value="1">
                        <input
                                type="text"
                                id="blik-code"
                                class="blik-input"
                                maxlength="7"
                                placeholder="000 000"
                                pattern="\d*"
                        />
                        <p class="error-message">
                            {l s='Payment error, please try again.' mod='tpay'}
                        </p>
                    </div>
                    <div class="blik-waiting">
                        <img src="{$assets_path|escape:'htmlall':'UTF-8'}views/img/device-mobile-check.svg"
                             alt="Ikona"/>
                        {l s="Confirm the payment in your bank's mobile app." mod='tpay'}
                    </div>
                </div>
            </div>

            <p class="blik-master-error">
                {l s='BLIK payment error, try paying online.' mod='tpay'}
            </p>

            {*            Bank transfer*}
            <div class="transfer_payment" style="display: none">
                <input
                        class="payment-input"
                        type="radio"
                        name="payment"
                        value="bank_transfer"
                        id="bank-transfer-radio"
                        disabled
                >
                <div class="payment-option">
                    <label class="payment-label" for="bank-transfer-radio">
                        <span class="radio-mark"></span>
                        <span class="payment-title">Transfer</span>
                        <img
                                src="{$assets_path|escape:'htmlall':'UTF-8'}views/img/tpay--small.svg"
                                alt="Logo Tpay"
                        />
                        <img
                                src="{$assets_path|escape:'htmlall':'UTF-8'}views/img/check.svg"
                                alt="Check ico"
                                class="check-ico"
                        />
                    </label>
                </div>
            </div>
        </div>
        <button class="btn blue pay-button" id="payment-button" disabled>
            <span class="spinner"></span>
            <span class="label">{l s='Pay for your purchase with Tpay!' mod='tpay'}</span>
        </button>
        <div class="section-divider"></div>
    </div>
    <div class="payment-confirmation-container success">
        <div class="icon-wrapper">
            <img src="{$assets_path|escape:'htmlall':'UTF-8'}views/img/success.svg" alt="Icon"/>
        </div>
        <div class="message">
            <p class="title">{l s='Payment completed successfully!' mod='tpay'}</p>
            <p class="subtitle">{l s='Thank you for using Tpay.' mod='tpay'}</p>
        </div>
        <div class="underline"></div>
    </div>
{/if}

<script>
    document.addEventListener("DOMContentLoaded", function (e) {
            const blikSection = document.querySelector('.payment-option-blik');
            const paymentButton = document.getElementById('payment-button');
            const blikCodeInput = document.getElementById('blik-code');
            const paymentsInputs = document.getElementsByName('payment');
            const transactionCounter = document.getElementById('transaction_counter');

            setFormState(false);

            checkOrder()

            function checkOrder() {
                let paymentData = {
                    action: 'blik0Status',
                    cartId: "{$cartId}",
                    transactionId: "{$transactionId}"
                };
                const data = (new URLSearchParams(paymentData)).toString();

                fetch("{$blikUrl}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: data,
                })
                    .then(response => {
                        return response.json().then(data => {
                            document.querySelector('.blik-waiting').style.display = 'none';

                            if (data.status === 'correct') {
                                document.querySelector('.payment-section').style.display = 'none';
                                document.querySelector('.payment-confirmation-container').style.display = 'block';
                            } else {
                                document.querySelector('.payment-section').style.display = 'block';
                                document.querySelector('.transfer_payment').style.display = 'block';
                                document.querySelector('.pay-button').style.display = 'block';
                                document.querySelector('.blik-code-section').style.display = 'block';
                                blikSection.classList.add('with-error');
                                blikSection.classList.remove('loading');
                                setFormState(false, true);
                            }
                        });
                    })
                    .catch(function (e) {
                        blikSection.classList.add('with-error');
                        blikSection.classList.remove('loading');
                        setFormState(false);
                    })
            }

            function setFormState(isLoading, forceDisabled) {
                if (isLoading) {
                    paymentButton.classList.add('loading');
                } else {
                    paymentButton.classList.remove('loading');
                }
                if (forceDisabled) {
                    paymentButton.disabled = true;
                } else {
                    paymentButton.disabled = isLoading;
                }
                paymentsInputs.forEach(function (input) {
                    input.disabled = isLoading;
                });
            }

            function changePayButtonState() {
                paymentButton.disabled = isBlik() && getCleanBlikCode().length !== 6;
            }

            function onPaymentInputClick() {
                changePayButtonState();
            }

            function onBlikCodeKeyUp() {
                changePayButtonState();
                const valueAsArray = getCleanBlikCode().split('');
                if (valueAsArray.length > 3) {
                    valueAsArray.splice(3, 0, ' ');
                }
                blikCodeInput.value = valueAsArray.join('');
            }

            function getCleanBlikCode() {
                return (blikCodeInput.value || '').replaceAll(/[^0-9]/g, '');
            }

            function isBlik() {
                return getSelectedPayment() === 'blik';
            }

            function getSelectedPayment() {
                const elements = document.getElementsByName('payment');
                for (let i = 0, l = elements.length; i < l; i++) {
                    if (elements[i].checked) {
                        return elements[i].value;
                    }
                }

                return null;
            }

            function pay() {
                let paymentData = {
                    action: 'blik0Status',
                    cartId: "{$cartId}",
                    transactionId: "{$transactionId}"
                };

                if (isBlik()) {
                    payBlik(paymentData);
                } else {
                    payTransfer(paymentData);
                }
            }

            function payBlik(paymentData) {
                blikSection.classList.add('loading');
                blikSection.classList.remove('with-error');

                blikSection.classList.add('loading');
                blikSection.classList.remove('with-error');
                setFormState(true);

                paymentData.blikCode = getCleanBlikCode();
                paymentData.transactionCounter = parseInt(transactionCounter.value);
                paymentData.action = 'payBlikTransaction';

                const data = (new URLSearchParams(paymentData)).toString();
                transactionCounter.value = parseInt(transactionCounter.value) + 1;

                fetch("{$blikUrl}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: data,
                })
                    .then(response => {
                        return response.json().then(data => {
                            if (data.result === 'correct') {
                                document.querySelector('.payment-section').style.display = 'none';
                                document.querySelector('.payment-confirmation-container').style.display = 'block';
                            } else {
                                document.querySelector('.transfer_payment').style.display = 'block';
                                document.querySelector('.pay-button').style.display = 'block';

                                if (parseInt(transactionCounter.value) === 3) {
                                    document.querySelector('.blik_payment').style.display = 'none';
                                    document.querySelector('.blik-master-error').style.display = 'block';
                                    setFormState(false, true);
                                } else {
                                    document.querySelector('.payment-section').style.display = 'block';
                                    document.querySelector('.blik-code-section').style.display = 'block';
                                    blikSection.classList.add('with-error');
                                    blikSection.classList.remove('loading');
                                    setFormState(false);
                                }
                            }
                        });
                    })
                    .catch(function (e) {
                        document.querySelector('.transfer_payment').style.display = 'block';
                        document.querySelector('.pay-button').style.display = 'block';

                        if (parseInt(transactionCounter.value) === 3) {
                            document.querySelector('.blik_payment').style.display = 'none';
                            document.querySelector('.blik-master-error').style.display = 'block';
                        } else {
                            document.querySelector('.payment-section').style.display = 'block';
                            document.querySelector('.blik-code-section').style.display = 'block';
                            blikSection.classList.add('with-error');
                            blikSection.classList.remove('loading');
                            setFormState(false);
                        }
                    })
            }

            function payTransfer(paymentData) {
                setFormState(true);
                paymentData.action = 'payByTransfer';
                paymentData.orderIdForTransfer = "{$orderId}";

                const data = (new URLSearchParams(paymentData)).toString();

                fetch("{$blikUrl}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: data,
                })
                    .then(response => {
                        return response.json().then(data => {
                            if (data.result === 'correct') {
                                window.top.location.href = data.payment_url;
                            } else {
                                setFormState(false, true);
                            }
                        });
                    })
                    .catch(function (e) {
                        setFormState(false);
                    });
            }

            paymentButton.addEventListener('click', pay);
            blikCodeInput.addEventListener('keyup', onBlikCodeKeyUp);
            paymentsInputs.forEach(function (input) {
                input.addEventListener('click', onPaymentInputClick)
            });
        }
    )
</script>