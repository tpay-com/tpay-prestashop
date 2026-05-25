/**MIT License

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
@copyright Krajowy Integrator Płatności S.A.*/
function BlikPayment() {
    $('#payment-confirmation button').click(function (e) {
        if (isBlikContainerChosen(e)) {
            SubmitPayment(e);
        }
    });

    var blik0CodeInput = document.getElementById('blik_code');
    blik0CodeInput.addEventListener('keyup', onBlikCodeKeyUp);
    blik0CodeInput.addEventListener('change', onBlikCodeKeyUp);

    function onBlikCodeKeyUp() {
        const valueAsArray = getCleanBlikCode(blik0CodeInput.value).split('');
        if (valueAsArray.length > 3) {
            valueAsArray.splice(3, 0, ' ');
        }
        blik0CodeInput.value = valueAsArray.join('');
    }

    function getCleanBlikCode(blikCode) {
        return (blikCode || '').replace(/[^0-9]/g, '');
    }

    function SubmitPayment() {
        const cartId = document.querySelector('input[name=cart_id]');
        const blikCode = document.querySelector('#blik_code');
        const form = document.querySelector('#tpay-blik-form');
        const optionSelected = document.querySelector('input[name=blikOption]:checked');
        let option = optionSelected ? optionSelected.value : 'new';
        const url = form.action;
        let cleanCode = getCleanBlikCode(blikCode.value);

        if ('new' === option && (cleanCode.length !== 6 || !/^\d{6}$/.test(cleanCode))) {
            const errorEl = document.getElementById('blik-error');
            blikCode.classList.add('is-invalid');
            blikCode.style.border = "1px solid red";
            errorEl.style.display = 'block';
            blikCode.focus();

            return;
        }

        let blikData = {
            blikOption: option,
            ajax: true,
            cartId: parseInt(cartId.value),
            blikCode: getCleanBlikCode(blikCode.value),
            action: 'createTransaction'
        };

        showLoading();
        const paymentData = (new URLSearchParams(blikData)).toString();

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: paymentData,
        })
            .then(response => {
                return response.json().then(data => {
                    localStorage.setItem('tpay_transaction_counter', 1);
                    window.location.href = data.backUrl
                });
            })
            .catch(function (e) {
                removeLoading()
                console.log(e)
            })
    }

    function isBlikContainerChosen(e) {
        var blikContainer = $('#tpay-blik-form').parents('div').eq(1);

        if (!blikContainer.is(':visible')) {
            return false;
        }

        e.preventDefault();
        e.stopPropagation();

        return true;
    }

    function showLoading() {
        const section = document.querySelector("#checkout-payment-step");
        section.classList.add("loading");
    }


    function removeLoading() {
        const section = document.querySelector("#checkout-payment-step");
        section.classList.remove("loading");
    }

    document.addEventListener("DOMContentLoaded", function (e) {
        const form = document.querySelector('#tpay-blik-form');

        if (form) {
            const url = form.action;

            form.addEventListener('keydown', (e) => {

                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
        }
    });
}
