/**MIT License
@license MIT

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
  const paymentMethodSelected = Array.from(document.querySelectorAll('[id^=payment-option]')).find((radio) => radio.checked).id
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
    if (!checked || (null !== req && !req.checked)) {
        if (!checked) {
            errorMsg.style.display = 'block'
        } else {
            errorMsg.style.display = 'none'
        }

        btn.disabled = true
        btn.classList.add('disabled')
    } else {
        errorMsg.style.display = 'none'
        btn.disabled = false
        btn.classList.remove('disabled')
    }
}

export function addTpaySupercheckoutValidator() {
  function validator() {
    const tpayTransferRadio = document
      .querySelector(".tpay-payment-gateways")
      .closest("li")
      .querySelector("input[type=radio]") as HTMLInputElement;

    if (!tpayTransferRadio.checked) {
      return true;
    }

    const tpayPaymentMethodsRadios = document.querySelectorAll(
      ".tpay-payment-gateways input[type=radio]"
    );

    const methodChecked = Array.from(tpayPaymentMethodsRadios).some(
      (method: HTMLInputElement) => method.checked
    );

    if (methodChecked) {
      return true;
    }

    window.scrollTo({top: 0, behavior: 'smooth'});
    throw new Error("Wybierz metodę płatności");
  }

  if (typeof window.addSupercheckoutOrderValidator === "function") {
    window.addSupercheckoutOrderValidator(validator);
  }
}
