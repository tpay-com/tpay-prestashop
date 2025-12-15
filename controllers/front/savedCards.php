<?php

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
