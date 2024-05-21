export function getAllAdditionalInformations()
{
    return document.querySelectorAll<HTMLDivElement>('.js-additional-information');
}

export function getPaymentId(elm: any)
{
    const name = elm.id;
    return name.replace(/\D/g, '');
}

export function getPaymentContainer(index: number)
{
    return document.querySelector('#payment-option-' + index + '-container');
}

export function getPaymentContent(index: number)
{
    return document.querySelector('#payment-option-' + index + '-additional-information');
}

export function getPaymentForm(index: number)
{
    return document.querySelector('#pay-with-payment-option-' + index + '-form');
}


export function isVisible(elm: Element) {
    const styles = window.getComputedStyle(elm)
    return styles.display !== 'none'
}

export function handleErrors(response:any) {
    if (!response.ok) {
        alert(response.statusText);
        // throw Error(response.statusText);
    }
    return response;
}

export const headers: HeadersInit = {
    'Content-Type': 'application/x-www-form-urlencoded',
}
