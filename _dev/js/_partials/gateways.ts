import {isVisible} from "./helpers";

export default function basicTransferPayments() {
  const gateways_wrappers = document.querySelectorAll<HTMLDivElement>('.tpay-payment-gateways');

  if (gateways_wrappers.length > 0) {
    gateways_wrappers.forEach(function (gateways_wrapper) {
      const form = (gateways_wrapper.parentNode.parentNode as Element).nextElementSibling; // test
      const gateways = Array.from(
        gateways_wrapper.querySelectorAll<HTMLDivElement>('.tpay-payment-gateways__item')
      );
      if (gateways) {
        for (const gateway of gateways) {
          gateway.addEventListener('click', () => {

            validateSelectedTransfer(gateways_wrapper)

            gateways.forEach(item => {
              item.classList.remove('tpay-payment-gateways__item--active');
            })
            gateway.classList.add('tpay-payment-gateways__item--active');

          }, false)
        }
      }
    })
  }
}

export function validateSelectedTransfer(gateways_wrapper: HTMLDivElement) {
  let checked = false;
  const req = document.querySelector <HTMLInputElement>('input[name="conditions_to_approve[terms-and-conditions]"]');
  const btn = document.querySelector<HTMLButtonElement>('#payment-confirmation button');
  const paymentMethodSelected = Array.from(document.querySelectorAll('[name=payment-option]')).find((radio) => radio.checked).id
  const paymentWrapper = document.querySelector('#pay-with-' + paymentMethodSelected + '-form')
  const tpayInputsCount = paymentWrapper?.querySelectorAll('.tpay-payment-gateways__item input').length

  if (tpayInputsCount) {
    const transferId = paymentWrapper.querySelectorAll('input[name=tpay_transfer_id]');
    const channelId = paymentWrapper.querySelectorAll('input[name=tpay_channel_id]');

    checked = Array.from(transferId).find((radio: HTMLInputElement) => radio.checked) !== undefined;
    if (!checked) {
      checked = Array.from(channelId).find((radio: HTMLInputElement) => radio.checked) !== undefined;
    }
  }

  if (tpayInputsCount > 0) {
    setTimeout(() => {
      switchButton(checked, btn, req, paymentWrapper.querySelector('.transfer-error'))
    }, 50);
  }
}

function switchButton(checked: boolean, btn: HTMLButtonElement, req: HTMLInputElement, errorMsg) {
  if (!checked || !req.checked) {
    errorMsg.style.display = 'block'
    btn.disabled = true
    btn.classList.add('disabled')
  } else {
    errorMsg.style.display = 'none'
    btn.disabled = false
    btn.classList.remove('disabled')
  }
}
