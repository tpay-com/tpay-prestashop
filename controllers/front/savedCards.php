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

class TpaySavedCardsModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $auth = true;

    private $repositoryCreditCard;

    /**
     * @throws PrestaShopException
     */
    public function initContent()
    {
        parent::initContent();
        $this->repositoryCreditCard = $this->module->getService('tpay.repository.credit_card');
        $customerId = (int) $this->context->customer->id;

        if (empty($customerId)) {
            Tools::redirect('index.php');
        }

        if (Tools::getValue('action') === 'delete') {
            $id = (int) Tools::getValue('id');
            $this->deleteCard($id);
        }

        $creditCards = $this->repositoryCreditCard->getAllCreditCardsByUserId($customerId);

        $this->context->smarty->assign(['customer_credit_cards' => $creditCards]);
        $this->setTemplate('module:tpay/views/templates/front/accountCreditCardsPage.tpl');
    }

    /**
     * @throws PrestaShopException
     */
    public function displayAjaxDeleteCard()
    {
        $id = (int) Tools::getValue('id');
        $customerId = (int) $this->context->customer->id;

        if (!$id || !$customerId) {
            \PrestaShopLogger::addLog('No delete card', 3);
            return;
        }

        $delete = $this->repositoryCreditCard->deleteCard(
            $id,
            (int) $this->context->customer->id
        );

        $this->ajaxRender(json_encode(['results' => $delete ? 'error' : 'success']));
    }
}
