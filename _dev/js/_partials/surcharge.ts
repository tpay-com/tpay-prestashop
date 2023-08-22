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
