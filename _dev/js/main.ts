import {
    getPaymentId,
    getPaymentContainer,
    getPaymentContent, getPaymentForm, isVisible
} from './_partials/helpers';

import basicTransferPayments, { addTpaySupercheckoutValidator, validateSelectedTransfer } from "./_partials/gateways";

import * as clause from "./_partials/clause";

import {checkSurcharge} from "./_partials/surcharge";
import { elementReady } from './utils/elementReady';
import { isInSupercheckout } from './_partials/supercheckout';

elementReady(".tpay-payment-gateways").then(() => {
  basicTransferPayments();

  if (isInSupercheckout) {
    addTpaySupercheckoutValidator();
  }
});


function radioPayments() {
    const AllPaymentOptions = Array.from(
        document.querySelectorAll<HTMLInputElement>('input[id^=payment-option]')
    );

    Array.from(AllPaymentOptions).forEach(function (payment) {
        payment.addEventListener('click', () => {
            // Commented out since only causing problems and not rly doing anything
            // const parent = payment.closest('.payment-option');
            // const isRegulation = parent.querySelector<HTMLDivElement>('.tpay-regulations') ?? false;
            //
            // resetValidationInfo();
            //
            // const paymentId = getPaymentId(parent);
            //
            //
            //     document.querySelector('body').classList.remove('tpay-hide-process-btn');
            //     changingButtonBehavior(paymentId, 'show');
            //
            //
            // const fee = getPaymentForm(paymentId).querySelector('input[name=tpay]');
            // if(fee) {
            //     checkSurcharge(true);
            // } else {
            //     checkSurcharge(false);
            // }


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
