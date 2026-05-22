<?php

/**MIT License

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

@author Krajowy Integrator Płatności S.A.*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class TpayAjaxModuleFrontController extends ModuleFrontController
{
    public $ajax;

    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        parent::init();
    }

    /** @throws PrestaShopException */
    public function initContent()
    {
        parent::initContent();
        $ajax = true;
        $state = Tools::getValue('state');

        if ('surcharge' === Tools::getValue('action')) {
            $this->surcharge(
                $state
            );
        }

        exit;
    }

    /**
     * @throws PrestaShopException
     */
    public function renderFragment($tpayPayment): void
    {
        ob_end_clean();
        header('Content-Type: application/json');
        $this->ajaxRender(
            json_encode(
                [
                    'cart_summary_subtotals_container' => $this->render('checkout/_partials/cart-summary-subtotals'),
                    'cart_summary_totals' => $this->render('checkout/_partials/cart-summary-totals'),
                    'status' => true,
                    'state' => $tpayPayment,
                ]
            )
        );
        exit;
    }

    /**
     * @throws PrestaShopException
     * @throws Exception
     */
    private function surcharge($state)
    {
        $surchargeService = $this->module->getService('tpay.service.surcharge');
        $cart = $this->context->cart;
        $orderTotal = (float) $cart->getOrderTotal();
        $tpayPayment = $state;

        $paymentFee = $surchargeService->getSurchargeValue($orderTotal);
        $orderTotalWithFee = $orderTotal + $paymentFee;

        if ('false' === $tpayPayment || $paymentFee <= 0) {
            $presenterCart = $this->cart_presenter->present($this->context->cart);
            $this->context->smarty->assign(
                [
                    'configuration' => $this->getTemplateVarConfiguration(),
                    'cart' => $presenterCart,
                ]
            );

            $this->renderFragment($tpayPayment);
        }

        $orderTotalNoTax = $cart->getOrderTotal(false);
        $orderTotalNoTaxWithFee = $orderTotalNoTax + $paymentFee;

        $totalIncludingTax = $orderTotalWithFee;
        $totalExcludingTax = $orderTotalNoTaxWithFee;

        $taxConfiguration = new TaxConfiguration();
        $presenterCart = $this->cart_presenter->present($this->context->cart);

        $presenterCart['subtotals']['payment'] = [
            'type' => 'payment',
            'label' => $this->trans('Online payment fee', [], 'Shop.Theme.Checkout'),
            'amount' => 1,
            'value' => $this->context->getCurrentLocale()->formatPrice($surchargeService->getSurchargeValue($orderTotal), $this->context->currency->iso_code),
        ];

        $presenterCart['totals'] = [
            'total' => [
                'type' => 'total',
                'label' => $this->trans('Total', [], 'Shop.Theme.Checkout'),
                'amount' => $taxConfiguration->includeTaxes() ? $totalIncludingTax : $totalExcludingTax,
                'value' => $this->context->getCurrentLocale()->formatPrice(
                    $taxConfiguration->includeTaxes() ? (float) $totalIncludingTax : (float) $totalExcludingTax,
                    $this->context->currency->iso_code
                ),
            ],
            'total_including_tax' => [
                'type' => 'total',
                'label' => $this->trans('Total (tax incl.)', [], 'Shop.Theme.Checkout'),
                'amount' => $totalIncludingTax,
                'value' => $this->context->getCurrentLocale()->formatPrice((float) $totalIncludingTax, $this->context->currency->iso_code),
            ],
            'total_excluding_tax' => [
                'type' => 'total',
                'label' => $this->trans('Total (tax excl.)', [], 'Shop.Theme.Checkout'),
                'amount' => $totalExcludingTax,
                'value' => $this->context->getCurrentLocale()->formatPrice((float) $totalExcludingTax, $this->context->currency->iso_code),
            ],
        ];

        $this->context->smarty->assign(
            [
                'configuration' => $this->getTemplateVarConfiguration(),
                'cart' => $presenterCart,
            ]
        );

        $this->renderFragment($tpayPayment);
    }
}
