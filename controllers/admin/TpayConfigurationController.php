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

use Configuration as Cfg;
use Tpay\Adapter\ConfigurationAdapter;
use Tpay\Install\ConfigurationSaveForm;
use Tpay\Util\AdminFormBuilder;
use Tpay\Util\Helper;

class TpayConfigurationController extends ModuleAdminController
{
    public const SEPARATE_PAYMENT_INFO = 'Show the method as a separate payment';
    public $configuration = [];
    public $channels = [];

    /** @var Tpay */
    public $module;

    public function __construct()
    {
        parent::__construct();

        $this->bootstrap = true;
        $lang = new Language((int)Cfg::get('PS_LANG_DEFAULT'));

        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }
    }

    public function getValuesFormFields(): array
    {
        $fields = [];

        foreach (Helper::getFields() as $field) {
            $value = Tools::getValue($field, Cfg::get($field));

            if ($field == "TPAY_MERCHANT_SECRET") {
                $value = html_entity_decode($value);
            }

            if ($field == 'TPAY_GENERIC_PAYMENTS[]') {
                $value = json_decode(Cfg::get('TPAY_GENERIC_PAYMENTS'), true);
            }

            if ($field == 'TPAY_CUSTOM_ORDER[]') {
                $value = json_decode(Cfg::get('TPAY_CUSTOM_ORDER'), true);
            }

            $fields[$field] = $value;
        }

        return $fields;
    }

    public function initContent()
    {
        $content = '';
        if ($this->postProcess()) {
            $this->confirmations[] = $this->module->l('Settings saved');
        }

        if ($this->contextIsGroup()) {
            $content .= $this->getWarningMultishopHtml();
        } else {
            $content .= $this->displayForm();
            $content .= $this->context->smarty->fetch('module:tpay/views/templates/_admin/configuration.tpl');
        }

        $this->context->smarty->assign(['content' => $content]);
        $this->module->clearCache();

        return $content;
    }

    protected function getWarningMultishopHtml()
    {
        if (Shop::getContext() == Shop::CONTEXT_GROUP) {
            return '<p class="alert alert-warning">' .
                $this->module->l(
                    'You cannot manage from a "Group Shop" context, select directly the shop you want to edit'
                ) .
                '</p>';
        } else {
            return '';
        }
    }

    protected function contextIsGroup(): bool
    {
        $res = false;

        if (Shop::isFeatureActive()) {
            if (Shop::getContext() == Shop::CONTEXT_GROUP) {
                $res = true;
            }
        }

        return $res;
    }

    public function createForm(): array
    {
        $this->getChannels();
        $formBuilder = new AdminFormBuilder($this->module, $this->context, $this->channels);

        $form[] = $formBuilder->formBasicOptions();
//        $form[] = $formBuilder->formPeKaoInstallments();
        $form[] = $formBuilder->formPaymentOptions();
        $form[] = $formBuilder->formGenericPaymentOptions();
        $form[] = $formBuilder->formCardOptions();
        $form[] = $formBuilder->formStatusesOptions();

        return $form;
    }

    public function validatePostProcess(): bool
    {
        $res = true;

        if (Tools::getValue('TPAY_CARD_ACTIVE') && Tools::getValue('TPAY_CARD_WIDGET')) {
            if (empty(Tools::getValue('TPAY_CARD_RSA'))) {
                $this->errors['rsa'] = $this->module->l('Invalid RSA key');
                $res = false;
            }
        }

        if (empty(Tools::getValue('TPAY_CLIENT_ID'))) {
            $this->errors['client_id'] = $this->module->l('Please fill in the client id');
            $res = false;
        }

        if (empty(Tools::getValue('TPAY_SECRET_KEY'))) {
            $this->errors['secret_key'] = $this->module->l('Please complete the secret key');
            $res = false;
        }

        if (empty(Tools::getValue('TPAY_MERCHANT_SECRET'))) {
            $this->errors['merchant_secret'] = $this->module->l('Please complete the secret merchant key');
            $res = false;
        }

        if (Tools::getValue('TPAY_NOTIFICATION_EMAILS')) {
            if (!Validate::isEmail(Tools::getValue('TPAY_NOTIFICATION_EMAILS'))) {
                $this->errors['notification_emails'] = $this->module->l('Invalid email notification');
                $res = false;
            }
        }

        if (Tools::getValue('TPAY_PEKAO_INSTALLMENTS_ACTIVE')) {
            if (empty(Tools::getValue('TPAY_MERCHANT_ID'))) {
                $this->errors['merchant_id'] = $this->module->l('When the installment simulator is enabled, the merchant ID field must be filled in');
                $res = false;
            }
        }

        return $res;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submit' . $this->module->name)) {
            try {
                if (!$this->validatePostProcess()) {
                    return false;
                }

                $output = '';

                $settings = new ConfigurationSaveForm(new ConfigurationAdapter(0));
                $settings->execute(true);
                $authorization = $this->module->authorization();

                if ($authorization && empty($this->errors)) {
                    $this->confirmations[] = $this->module->l('Credentials are correct.');
                } elseif (!$authorization) {
                    $this->warnings[] = $this->module->l('Credentials are incorrect!');
                }

                Tools::clearSmartyCache();

                if ($this->errors) {
                    echo $output;
                    return false;
                } else {
                    return true;
                }
            } catch (Exception $exception) {
                PrestaShopLogger::addLog($exception->getMessage(), 3);
                $this->errors[] = $this->module->l('Settings not saved');
            }
        }
    }

    public function displayForm(): string
    {
        $langId = (int)Cfg::get('PS_LANG_DEFAULT');
        $helper = new HelperForm();

        $fields_form = $this->createForm();
        $helper->fields_value = $this->getValuesFormFields();

        $helper->tpl_vars = [
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        $helper->module = $this->module;
        $helper->name_controller = $this->module->name;
        $helper->token = Tools::getAdminTokenLite('TpayConfiguration');
        $helper->currentIndex = AdminController::$currentIndex;

        // Language
        $helper->default_form_language = $langId;
        $helper->allow_employee_form_lang = $langId;

        $helper->title = $this->module->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->module->name;

        return $helper->generateForm($fields_form);
    }

    private function getChannels()
    {
        if ($this->module->api()) {
            try {
                $this->channels = $this->module->api()->transactions()->getChannels()['channels'] ?? [];
            } catch (Exception $exception) {
                PrestaShopLogger::addLog($exception->getMessage(), 3);
            }
        }
    }
}
