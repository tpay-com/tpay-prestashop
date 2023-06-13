<?php

/**
 * NOTICE OF LICENSE.
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    tpay.com
 * @copyright 2010-2016 tpay.com
 * @license   LICENSE.txt
 */

use Tpay\Config\Config;

/**
 * Class TpayOrderErrorModuleFrontController.
 */
class TpayOrderErrorModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $this->removeProductInCart();
        $this->context->cookie->__unset('last_order');
        $this->context->cookie->last_order = false;
        $this->cartId = 0;
        $this->context->controller->addCss(_MODULE_DIR_ . 'tpay/views/css/style.css');
        $this->display_column_left = false;

        parent::initContent();

        $errorMsg = '';

        $attempts = Tools::getValue('attempts');

        if (!empty($attempts)) {
            end($attempts);
            $key = key($attempts);

            if (isset($attempts[$key]['paymentErrorCode'])) {
                $errorCode = $attempts[$key]['paymentErrorCode'];

                if ($errorCode === '104') {
                    $errorMsg = $this->module->l('Transaction was not accepted in the bank\'s application');
                } elseif ($errorCode === '101') {
                    $errorMsg = $this->module->l('Transaction rejected by payer');
                }
            }
        }


        $renewPaymentController = Tools::getValue('renewPaymentController');

        $this->context->smarty->assign(
            [
                'error' => $errorMsg,
                'renewPaymentController' => $renewPaymentController,
            ]
        );

        $this->setTemplate(Config::TPAY_PATH . '/orderError.tpl');
    }

    /**
     * Remove all products in cart
     */
    private function removeProductInCart(): void
    {
        $products = $this->context->cart->getProducts();
        foreach ($products as $product) {
            $this->context->cart->deleteProduct($product['id_product']);
        }
    }
}
