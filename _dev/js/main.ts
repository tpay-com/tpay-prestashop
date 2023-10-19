import {
    getPaymentId,
    getPaymentContainer,
    getPaymentContent, getPaymentForm, isVisible
} from './_partials/helpers';

import blikWidget from "./_partials/blik";
import basicTransferPayments, { validateSelectedTransfer } from "./_partials/gateways";

import * as clause from "./_partials/clause";

import {checkSurcharge} from "./_partials/surcharge";
import {removeBlikAddept} from "./_partials/blikAddepts";


/// init blik
blikWidget();
basicTransferPayments();


function radioPayments() {
    const AllPaymentOptions = Array.from(
        document.querySelectorAll<HTMLInputElement>('input[name=payment-option]')
    );

    Array.from(AllPaymentOptions).forEach(function (payment) {
        payment.addEventListener('click', () => {

            const parent = payment.closest('.payment-option');
            const isRegulation = parent.querySelector<HTMLDivElement>('.tpay-regulations') ?? false;

            resetValidationInfo();

            const paymentId = getPaymentId(parent);
            const paymentAdditionalInformation = getPaymentContent(paymentId);



            let paymentName = '';

            if(paymentAdditionalInformation) {
                const paymentElm = paymentAdditionalInformation.querySelector('.tpay-wrapper');
                paymentName = paymentElm?.getAttribute('data-payment-type') ?? null;
            }



            if (paymentName === 'blik' || paymentName === 'card') {
                document.querySelector('body').classList.add('tpay-hide-process-btn');
                removeBlikAddept()
                changingButtonBehavior(paymentId, 'hide');

            } else {
                document.querySelector('body').classList.remove('tpay-hide-process-btn');
                changingButtonBehavior(paymentId, 'show');
            }


            const fee = getPaymentForm(paymentId).querySelector('input[name=tpay]');
            if(fee) {
                checkSurcharge(true);
            } else {
                checkSurcharge(false);
            }


        })
    });
}

radioPayments();






const resetValidationInfo = () => {
    const elements = document.querySelectorAll<HTMLInputElement>('.payment-options .custom-checkbox span');
    const inputs = document.querySelectorAll<HTMLInputElement>('.payment-options input');

    elements.forEach(a => {
        a.classList.remove("wrong");
    });

    inputs.forEach(a => {
        a.classList.remove("wrong");
    });
}






function changingButtonBehavior(key: number, behavior: string)
{

    if (document.querySelector('#tc-payment-confirmation')) {
        return;
    }

    const btn = document.querySelector<HTMLDivElement>('#payment-confirmation');
    let style:string;

    if (behavior === 'show') {
        style = 'block';
    } else if (behavior === 'hide') {
        style = 'none';
    }

    setTimeout(function () {
        btn.style.display = style;
    }, 250);
}

// Blick & Card widget
function handleClickRadioPayments()
{
    const radioSubPayments = document.querySelectorAll<HTMLInputElement>('.tpay-radio-payments__radio');

    Array.from(radioSubPayments).forEach(radio => {
        radio.addEventListener('click', (e) => {
            radioSubPayments.forEach(a => {
                a.classList.remove("tpay-radio-payments__radio--active");
            });

            radio.classList.add('tpay-radio-payments__radio--active');
            radio.querySelector<HTMLInputElement>('input[type=radio]').checked = true;
        });
    });
}

handleClickRadioPayments();
bindChangeConditionsToApprove();

function bindChangeConditionsToApprove() {
    const checkbox = <HTMLInputElement>document.querySelector('#conditions-to-approve input[type="checkbox"]');
    if(checkbox) {
        checkbox.addEventListener("change", () => {
            validateSelectedTransfer(document.querySelector('.tpay-payment-gateways__item--active')?.closest('.tpay-payment-gateways'));
        });
    }
    document.querySelectorAll('[name="payment-option"]').forEach(function(element){
      element.addEventListener('change', () => {
        validateSelectedTransfer(document.querySelector('.tpay-payment-gateways__item--active')?.closest('.tpay-payment-gateways'));
      })
    });
}
