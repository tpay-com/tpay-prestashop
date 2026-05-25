/**
* @author Krajowy Integrator Płatności S.A.
* @copyright Krajowy Integrator Płatności S.A.
* @license MIT
* 
* Copyright (c) 2026 Krajowy Integrator Płatności S.A.
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in all
* copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
* SOFTWARE.
*/
const headers: HeadersInit = {
    'Content-Type': 'application/x-www-form-urlencoded'
}

interface SurchargeInterface {
    action: string;
    ajax: boolean;
    state: boolean;
}

export const checkSurcharge = (state: boolean) => {
    // @ts-ignore
    const surcharge = surcharge_controller ?? null;
    if (surcharge) {
        collapseSurcharge(surcharge, state);
    }
}

const collapseSurcharge = (surcharge: string, isTpay: boolean) => {
    const url = surcharge;
    const surchargeData: SurchargeInterface = {
        action: 'surcharge',
        ajax: true,
        state: isTpay
    };

    surchargeFetch(url, surchargeData).then(response => {
        // @ts-ignore
        if (response.status === true) {
            document.querySelector('.js-cart-summary-subtotals-container')
                .outerHTML = response.cart_summary_subtotals_container;
            document.querySelector('.js-cart-summary-totals')
                .outerHTML = response.cart_summary_totals;
        }
    });

}

function surchargeFetch(url: string, config: {}) {
    interface Resp {
        status: boolean
    }

    const params: RequestInit = {
        method: 'POST',
        headers,
        body: new URLSearchParams(config)
    };

    return fetch(url, params)
        .then((response) => response.json())
        .then((data) => data as Resp);
}
