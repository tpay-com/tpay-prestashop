import {blikContainerResponse, BlikData, blikPreload, BlikResponseData } from "./blikHelpers";
import {handleErrors, headers } from "./helpers";
import {removeBlikAddept} from "./blikAddepts";

// Function waits for notification and updates transactions based on it
export function blikCheckNotification(
    url: string,
    transactionId: string,
    blikData: BlikData,
    attempt: number
) {
    let interval: any;
    let attemptLimit = attempt;

    // @ts-ignore
    const attemptErrorMsg = blik_limit_attempt_msg ?? '';
    const acceptNotifyMsg = blik_accept_msg ?? '';
    const errorMessages = blik_msg ?? '';

    // const submitBtn = document.querySelector<HTMLButtonElement>('#tpay-blik-submit');

    const params = {
        action: 'assignTransactionId',
        ajax: true,
        cartId: blikData.cartId,
        transactionId: transactionId,
    };


    blikFetch(url, params).then(fetchResponse => {
        try {
            let { status, payments, cartId } = fetchResponse;

            let blikResponse = blikContainerResponse(blikData.blikOption);
            let state: boolean;

            state = true;

            if (status === 'pending' || status === 'error') {

                // accept in bank application info
                if (payments.attempts) {
                    blikResponse.innerHTML = acceptNotifyMsg;
                    blikResponse.classList.add('tpay-blik-response--open');
                }

                if (payments.attempts && payments.attempts.length !== 0) {

                    let attemptsIndex = payments.attempts.length - 1
                    let errorCode = payments.attempts[attemptsIndex].paymentErrorCode;

                    // 63 -- blik code is verified before notification is sent
                    if (errorCode && errorMessages && errorCode !== "63") {
                        blikResponse.innerHTML = errorMessages[errorCode];
                        blikResponse.classList.add('tpay-blik-response--open');

                        state = stopFetchingProccess(interval, blikData);
                        redirectAfterFetch(fetchResponse, errorCode);
                    } else {
                        blikResponse.innerHTML = acceptNotifyMsg;
                        blikResponse.classList.add('tpay-blik-response--open');
                    }
                }

                if (attemptLimit > 40) {
                    blikResponse.innerHTML = attemptErrorMsg;
                    blikResponse.classList.add('tpay-blik-response--open');
                    // submitBtn.classList.remove('disabled')

                    state = stopFetchingProccess(interval, blikData);
                    redirectAfterFetch(fetchResponse, -1);
                }
            }

            if (status === 'success') {
                state = stopFetchingProccess(interval, blikData);
                redirectAfterFetch(fetchResponse);
            }

            return state;

        } catch(err) {
            console.log(err);
        }

    }).then((state) => {
        if(state) {
            interval = setTimeout(() => {
                blikCheckNotification(
                    url,
                    transactionId,
                    blikData,
                    attemptLimit + 1
                );
            }, 3000);
        }
    });
}






function stopFetchingProccess(interval, blikData) {
    clearInterval(interval);
    blikPreload(blikData.blikOption, false);
    return false;
}


function redirectAfterFetch(fetchResponse, errorCode: number = 0) {
    if(errorCode === 0) {
        window.location.href = fetchResponse.backUrl
    } else {
        removeBlikAddept()
    }
}

export function blikFetch(action: string, config: {}) {
    const opts: RequestInit = {
        method: 'POST',
        headers,
        body: new URLSearchParams(config)
    };
    return fetch(action, opts)
        .then(handleErrors)
        .then((response) => {
            return response.json();
        });
}
