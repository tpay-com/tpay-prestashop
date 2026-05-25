<?php
/**
 * @author Krajowy Integrator Płatności S.A.
 * @copyright Krajowy Integrator Płatności S.A.
 * @license MIT
 *
 * Copyright (c) 2026 Krajowy Integrator Płatności S.A.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class TpaySavedCardsModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $auth = true;
    private $repositoryCreditCard;

    /** @throws PrestaShopException */
    public function initContent()
    {
        parent::initContent();
        $this->repositoryCreditCard = $this->module->getService('tpay.repository.credit_card');
        $customerId = (int) $this->context->customer->id;

        if (empty($customerId)) {
            Tools::redirect('index.php');
        }

        if ('delete' === Tools::getValue('action')) {
            $id = (int) Tools::getValue('id');
            $this->deleteCard($id);
        }

        $creditCards = $this->repositoryCreditCard->getAllCreditCardsByUserId($customerId);

        $this->context->smarty->assign(['customer_credit_cards' => $creditCards]);
        $this->setTemplate('module:tpay/views/templates/front/accountCreditCardsPage.tpl');
    }

    /** @throws PrestaShopException */
    public function displayAjaxDeleteCard()
    {
        $id = (int) Tools::getValue('id');
        $customerId = (int) $this->context->customer->id;

        if (!$id || !$customerId) {
            PrestaShopLogger::addLog('No delete card', 3);

            return;
        }

        $delete = $this->repositoryCreditCard->deleteCard(
            $id,
            (int) $this->context->customer->id
        );

        $this->ajaxRender(json_encode(['results' => $delete ? 'error' : 'success']));
    }
}
