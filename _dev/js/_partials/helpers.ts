/**
@author Krajowy Integrator Płatności S.A.
@copyright Krajowy Integrator Płatności S.A.
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
SOFTWARE.*/
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
