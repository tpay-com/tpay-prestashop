import {isVisible} from "./helpers";

export default function basicTransferPayments()
{
    const gateways_wrapper = document.querySelector<HTMLDivElement>('.tpay-payment-gateways');

    if(gateways_wrapper) {
        const form = (gateways_wrapper.parentNode.parentNode as Element).nextElementSibling; // test
        const gateways = Array.from(
            document.querySelectorAll<HTMLDivElement>('.tpay-payment-gateways__item')
        );

        if (gateways) {
            for (const gateway of gateways) {
                gateway.addEventListener('click', () => {

                    validateSelectedTransfer()

                    gateways.forEach(item => {
                        item.classList.remove('tpay-payment-gateways__item--active');
                    })
                    gateway.classList.add('tpay-payment-gateways__item--active');

                }, false)
            }
        }
    }
}

export function validateSelectedTransfer() {

    const paymentForm = document.querySelector('.transferForm');
    const transferId = paymentForm.querySelectorAll('input[name=tpay_transfer_id]');
    const errorMsg = document.querySelector<HTMLDivElement>('#transfer-error');
    const btn = document.querySelector<HTMLButtonElement>('#payment-confirmation button');
    const req = document.querySelector <HTMLInputElement>('input[name="conditions_to_approve[terms-and-conditions]"]');


    if (isVisible(paymentForm.closest('.js-payment-option-form'))) {
        let checked = Array.from(transferId).find((radio: HTMLInputElement) => radio.checked);

        setTimeout(() => {
            if (checked) {
                errorMsg.style.display = 'none'
                btn.disabled = false
                btn.classList.remove('disabled')
            } else if (req.checked) {
                errorMsg.style.display = 'block'
                btn.disabled = true
                btn.classList.add('disabled')
            }
        }, 50);

    }
}
