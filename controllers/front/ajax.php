<?php

/**
 * NOTICE OF LICENSE
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Tpay
 * @copyright 2010-2022 tpay.com
 * @license   LICENSE.txt
 */

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

    /**
     * @throws PrestaShopException
     */
    public function initContent()
    {
        parent::initContent();
        $ajax = true;
        $state = Tools::getValue('state');

        if (Tools::getValue('action') === 'surcharge') {
            $this->surcharge(
                $state
            );
        }

        exit;
    }

    /**
     * @throws PrestaShopException
     * @throws Exception
     */
    private function surcharge($state)
    {
        $surchargeService = $this->module->getService("tpay.service.surcharge");
        $cart = $this->context->cart;
        $orderTotal = (float) $cart->getOrderTotal();
        $tpayPayment = $state;

        $paymentFee = $surchargeService->getSurchargeValue($orderTotal);
        $orderTotalWithFee = $orderTotal + $paymentFee;


        if ($tpayPayment === 'false' || $paymentFee <= 0) {
            $presenterCart = $this->cart_presenter->present($this->context->cart);
            $this->context->smarty->assign([
                'configuration' => $this->getTemplateVarConfiguration(),
                'cart' => $presenterCart,
            ]);

            $this->renderFragment($tpayPayment);
        }



        $orderTotalNoTax = $cart->getOrderTotal(false);
        $orderTotalNoTaxWithFee = $orderTotalNoTax + $paymentFee;

        $totalIncludingTax = $orderTotalWithFee;
        $totalExcludingTax = $orderTotalNoTaxWithFee;

        $taxConfiguration = new \TaxConfiguration();
        $presenterCart = $this->cart_presenter->present($this->context->cart);

        $presenterCart['subtotals']['payment'] = [
            'type' => 'payment',
            'label' => $this->trans('Online payment fee', [], 'Shop.Theme.Checkout'),
            'amount' => 1,
            'value' => \Tools::displayPrice($surchargeService->getSurchargeValue($orderTotal)),
        ];

        $presenterCart['totals'] = [
            'total' => [
                'type' => 'total',
                'label' => $this->trans('Total', [], 'Shop.Theme.Checkout'),
                'amount' => $taxConfiguration->includeTaxes() ? $totalIncludingTax : $totalExcludingTax,
                'value' => \Tools::displayPrice(

                    $taxConfiguration->includeTaxes() ? (float) $totalIncludingTax : (float) $totalExcludingTax
                ),
            ],
            'total_including_tax' => [
                'type' => 'total',
                'label' => $this->trans('Total (tax incl.)', [], 'Shop.Theme.Checkout'),
                'amount' => $totalIncludingTax,
                'value' => \Tools::displayPrice((float) $totalIncludingTax),
            ],
            'total_excluding_tax' => [
                'type' => 'total',
                'label' => $this->trans('Total (tax excl.)', [], 'Shop.Theme.Checkout'),
                'amount' => $totalExcludingTax,
                'value' => \Tools::displayPrice((float) $totalExcludingTax),
            ],
        ];

        $this->context->smarty->assign([
            'configuration' => $this->getTemplateVarConfiguration(),
            'cart' => $presenterCart,
        ]);

        $this->renderFragment($tpayPayment);
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
                    'state' => $tpayPayment
                ]
            )
        );
        exit();
    }
}
