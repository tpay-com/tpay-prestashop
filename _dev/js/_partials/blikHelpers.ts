export interface BlikResponseData {
    transaction: {
        status: {}
        transactionId: string,
    },
}

export interface BlikData {
    blikCode?: string,
    blikOption: string
}

export const blikPreload = (option: string, state: boolean) => {
    const elm = blikContainerPreload(option);

    if (state && elm) {
        elm.style.display = 'block';
    } else {
        elm.style.display = 'none';
    }
}

export const blikContainerPreload = (option: string) => {
    const optionType = checkBlikOption(option);
    return <HTMLDivElement>document.querySelector(optionType + ' .tpay-preload');
}

export const blikContainerResponse = (option: string) => {
    const optionType = checkBlikOption(option);
    return <HTMLDivElement>document.querySelector(optionType + ' .tpay-blik-response');
}

export const checkBlikOption = (option: string) => {
    return option === 'use' ? '.tpay-blik--use' : '.tpay-blik--new';
}

export function blikResetMessage() {
    const input = <HTMLInputElement>
        document.querySelector('#blik_code');
    const response = <HTMLDivElement>
        document.querySelector('.tpay-blik--new .tpay-blik-response');

    if(input) {
        input.addEventListener("click",function(e){
            input.classList.remove('wrong')
            response.classList.remove('tpay-blik-response--open')
        });
    }
}
