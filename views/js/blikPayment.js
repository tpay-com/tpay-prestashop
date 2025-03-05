function BlikPayment() {
    $('#payment-confirmation button').click(function (e) {
        if (isBlikContainerChosen(e)) {
            SubmitPayment(e);
        }
    });

    function SubmitPayment() {
        const cartId = document.querySelector('input[name=cart_id]');

        const blikCode = document.querySelector('#blik_code');
        const form = document.querySelector('#tpay-blik-form');
        const url = form.action;

        let blikData = {
            blikOption: 'new',
            ajax: true,
            cartId: parseInt(cartId.value),
            blikCode: blikCode.value,
            action: 'createTransaction'
        };

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