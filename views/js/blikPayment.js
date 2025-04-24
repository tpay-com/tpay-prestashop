function BlikPayment() {
    $('#payment-confirmation button').click(function (e) {
        if (isBlikContainerChosen(e)) {
            SubmitPayment(e);
        }
    });

    $('.show-blik-info').click(function (e) {
        e.preventDefault();
        $('.modal-tpay-blik-container').css('display', 'flex');
    });

    $('.modal-tpay-blik .close').click(function (e) {
        e.preventDefault();
        $('.modal-tpay-blik-container').hide();
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
        const url = form.action;

        let blikData = {
            blikOption: 'new',
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
