/// Adepts
export function setBlikAddept(params: any) {
    window.localStorage.setItem('tpay_blik_addepts', JSON.stringify(params));
}

export function getBlikAddept() {
    // let blikStorage = JSON.parse(window.localStorage.getItem('tpay_blik_addepts'));
    // let blikAddept = blikStorage?.addept ?? 1;
    return JSON.parse(window.localStorage.getItem('tpay_blik_addepts'));
}

export function removeBlikAddept() {
    window.localStorage.removeItem('tpay_blik_addepts');
}

/// Blik order control
export function setBlikOrder(params: any) {
    window.localStorage.setItem('tpay_blik_order', JSON.stringify(params));
}

export function getBlikOrder() {
    return JSON.parse(window.localStorage.getItem('tpay_blik_order'));
}

export function removeBlikOrder() {
    window.localStorage.removeItem('tpay_blik_order');
}
