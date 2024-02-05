import { validateClause } from "./clause";
import { getBlikAddept, removeBlikAddept, setBlikAddept } from "./blikAddepts";
import {blikCheckNotification, blikFetch } from "./blikNotification";
import {handleErrors, headers } from "./helpers";
import {blikContainerResponse, BlikData, blikPreload, blikResetMessage, BlikResponseData } from "./blikHelpers";

interface blikDataForm {
    blikOption: string;
    action?: string;
    ajax: boolean;
    cartId: number;
    blikCode?: string;
}

blikResetMessage()

export default function blikWidget() {
    const form = <HTMLFormElement>document.querySelector('#tpay-blik-form');
    const cartId = <HTMLInputElement>document.querySelector('input[name=cart_id]');

    if (form) {
        const url = form.action;
        const blikParams: blikDataForm = {
            blikOption: 'new',
            ajax: true,
            cartId: parseInt(cartId.value)
        };

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            e.stopPropagation();

            const blikCode = <HTMLInputElement>
                document.querySelector('#blik_code');
            const blikOption = <HTMLInputElement>
                document.querySelector('.tpay-radio-payments__radio--active input[name=blikOption]');

            let blikAddept = getBlikAddept();

            if (blikAddept && blikAddept.attempt === 4) {
                removeBlikAddept()
            }

            let action = blikAddept && blikAddept.attempt !== 4 ? 'createTransactionById' : 'createTransaction';

            blikParams.action = action;
            blikParams.blikOption = blikOption.value;

            // ADD TRANSACTION ID
            if(blikAddept != null && blikAddept.transaction_id != null) {
                blikParams.transactionId = blikAddept.transaction_id;
            }

            if (blikOption.value === 'use') {

                if (!blikValidateForm(blikCode, blikCode.value)) {
                    return false
                }
                blikParams.blikCode = '';

            } else if (blikOption.value === 'new') {
                if (!blikValidateForm(blikCode, blikCode.value)) {
                    return false
                } else {
                    blikParams.blikCode = blikCode.value;
                }
            }

            if (blikParams) {
                // Preload
                blikPreload(blikParams.blikOption, true);

                // Start transaction
                transactionFetch(url, blikParams).then(response => {
                    if (response.transaction.result === 'success') {

                        let transactionId = blikAddept && blikAddept.attempt !== 4 ?
                            blikAddept.transaction_id :
                            response.transaction.transactionId;

                        blikCreateTransaction(
                            url,
                            transactionId,
                            response,
                            blikParams
                        );
                    }
                })
            }

        }, false);

        blikChangeRegulations();
    }
}




async function blikCreateTransaction(
    url: string,
    transactionId: string,
    response: BlikResponseData,
    blikData: BlikData
) {

    let interval: any;

    const params = {
        action: 'createTransactionById',
        ajax: true,
        transactionId: transactionId,
        blikCode: blikData.blikCode,
    };

    const blikProcessing = await blikFetch(url, params);

    const status = blikProcessing.transaction.status;
    const paymentResponse = blikProcessing.response?.payments;
    const errors = blikProcessing.response?.payments?.errors?.length || 0;

    let blikResponse = blikContainerResponse(blikData.blikOption);
    let stat: boolean;

    stat = true;



    if (status === 'failed') {
        console.log(errors)

        blikResponse.innerHTML = 'failed';
        blikResponse.classList.add('tpay-blik-response--open');
        clearInterval(interval);

        blikPreload(blikData.blikOption, false);
        stat = false;
    }


    if (status === 'pending') {
        if (errors > 0) {

            let errorMessage;

            const blikState = {
                transaction_id: transactionId,
                cart_id: blikData.cartId,
                attempt: blikProcessing.transaction.payments.attempts.length,
            }

            setBlikAddept(blikState)

            // @ts-ignore
            const translateMessages = messages ?? null;

            for (const property in paymentResponse.errors) {

                if (paymentResponse.errors[property].errorCode === 'payment_failed') {
                    errorMessage = translateMessages?.blik_error;
                } else {
                    errorMessage = translateMessages?.payment_error
                }

                blikResponse.innerHTML = errorMessage
                blikResponse.classList.add('tpay-blik-response--open');
            }

            clearInterval(interval);
            blikPreload(blikData.blikOption, false);
            stat = false;
        }
    }

    if (status === 'correct') {
        clearInterval(interval);
        blikPreload(blikData.blikOption, false);
        window.location.href = blikProcessing.backUrl
        stat = false;
    }

    if (stat) {
        blikCheckNotification(
            url,
            transactionId,
            response,
            blikData,
            1
        );
    }

}

function blikChangeRegulations() {
    const elm = <HTMLInputElement>document.querySelector('#conditions-to-approve input[type="checkbox"]');
    const input = <HTMLInputElement>document.querySelector('.tpay-blik-input');

    elm?.addEventListener('click', () => {
        blikUpdateValidateState();
    });
    input?.addEventListener('change', () => {
        blikUpdateValidateState();
    });
}


function blikUpdateValidateState() {
    const blikCodeInput = document.querySelector<HTMLInputElement>('.tpay-blik-input');
    const AllResponses = document.querySelectorAll<HTMLDivElement>('.tpay-blik-response');
    const savedBlik = document.querySelector<HTMLInputElement>('input[name=savedId]');

    if (!blikValidateForm(blikCodeInput, blikCodeInput.value)) {
        return false
    }

    AllResponses.forEach(response => {
        response.classList.remove('tpay-blik-response--open');
    });

    blikCodeInput.classList.remove('wrong');
}

function blikValidateForm(input: HTMLInputElement, blikCode: string) {
    let state;
    const response = <HTMLDivElement>document.querySelector('.tpay-blik--new .tpay-blik-response');
    const clause = document.querySelector<HTMLInputElement>('input[name="conditions_to_approve[terms-and-conditions]"]');
    const clauseParent = document.querySelector('#conditions-to-approve .custom-checkbox > span');
    const blikCouse = document.querySelector<HTMLDivElement>('#blik-rr');

    blikCouse.style.display = 'none';

    const savedBlikId = document.querySelector<HTMLInputElement>('.blikId');
    const blikUseCodeRadio = document.querySelector<HTMLInputElement>('#blikUse');



    if(validateClause(clause) === false) {
        blikCouse.style.display = 'block';
        input.classList.remove('wrong')
        response.classList.remove('tpay-blik-response--open')
        state = false
    } else if (/^[0-9]{3}[0-9]{3}$/.test(blikCode) ||
        (savedBlikId !== null && (blikUseCodeRadio && blikUseCodeRadio.checked === true))) {

        input.classList.remove('wrong');
        response.classList.remove('tpay-blik-response--open');
        state = true;
    } else {
        input.classList.add('wrong')
        response.classList.add('tpay-blik-response--open')
        state = false
    }

    return state
}




function transactionFetch(url: string, config: {}) {

    interface Transaction {
        // orderId: string,
        transaction: {
            result: string,
            status: string,
            transactionId: string,
        },
    }

    const opts: RequestInit = {
        method: 'POST',
        headers,
        body: new URLSearchParams(config)
    };

    return fetch(url, opts)
        .then(handleErrors)
        .then((response) => response.json())
        .then((data) => data as Transaction);
}
