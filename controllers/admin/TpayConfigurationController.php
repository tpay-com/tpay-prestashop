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
use Tpay\Config\Config;
use Tpay\Install\ConfigurationSaveForm;
use Tpay\Util\Helper;

class TpayConfigurationController extends ModuleAdminController
{
    public const SEPARATE_PAYMENT_INFO = 'Show the method as a separate payment';
    public $errors = [];
    public $configuration = [];
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
            $fields[$field] = Tools::getValue($field, Cfg::get($field));
        }

        return $fields;
    }

    public function initContent()
    {
        $content = '';
        if ($this->postProcess()) {
            $content .= $this->module->displayConfirmation(
                $this->l('Settings saved'),
                [],
                'Admin.Notifications.Success'
            );
        } else {
            if ($this->errors && count($this->errors)) {
                foreach ($this->errors as $err) {
                    $content .= $this->module->displayError($err);
                }
            }
        }

        if ($this->contextIsGroup()) {
            $content .= $this->getWarningMultishopHtml();
        } else {
            $content .= $this->displayForm();
            $content .= $this->context->smarty->fetch('module:tpay/views/templates/_admin/configuration.tpl');
        }

        $this->context->smarty->assign([
            'content' => $content,
        ]);

        $this->module->clearCache();

        return $content;
    }

    public function getOrderStates(): array
    {
        return \OrderState::getOrderStates(\Context::getContext()->language->id);
    }


    protected function getWarningMultishopHtml()
    {
        if (\Shop::getContext() == \Shop::CONTEXT_GROUP) {
            return '<p class="alert alert-warning">' .
                $this->l('You cannot manage from a "Group Shop" context, select directly the shop you want to edit') .
                '</p>';
        } else {
            return '';
        }
    }


    protected function contextIsGroup(): bool
    {
        $res = false;

        if (\Shop::isFeatureActive()) {
            if (\Shop::getContext() == \Shop::CONTEXT_GROUP) {
                $res = true;
            }
        }

        return $res;
    }

    public function createForm(): array
    {
        $form[] = $this->formBasicOptions();
        $form[] = $this->formPaymentOptions();
        $form[] = $this->formCardOptions();
        $form[] = $this->formStatusesOptions();

        return $form;
    }

    public function validatePostProcess(): bool
    {
        $res = true;

        if (Tools::getValue('TPAY_CARD_ACTIVE')) {
            if (empty(Tools::getValue('TPAY_CARD_RSA'))) {
                $this->errors['rsa'] = $this->l('Invalid RSA key');
                $res = false;
            }
        }

        if (empty(Tools::getValue('TPAY_CLIENT_ID'))) {
            $this->errors['client_id'] = $this->l('Please fill in the client id');
            $res = false;
        }

        if (empty(Tools::getValue('TPAY_SECRET_KEY'))) {
            $this->errors['secret_key'] = $this->l('Please complete the secret key');
            $res = false;
        }

        if (empty(Tools::getValue('TPAY_MERCHANT_SECRET'))) {
            $this->errors['merchant_secret'] = $this->l('Please complete the secret merchant key');
            $res = false;
        }

        if (Tools::getValue('TPAY_NOTIFICATION_EMAILS')) {
            if (!Validate::isEmail(Tools::getValue('TPAY_NOTIFICATION_EMAILS'))) {
                $this->errors['notification_emails'] = $this->l('Invalid email notification');
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

                $settings = new ConfigurationSaveForm(new ConfigurationAdapter(0));
                $settings->execute(true);

                \Tools::clearSmartyCache();

                if ($this->errors) {
                    return false;
                } else {
                    return true;
                }
            } catch (\Exception $exception) {
                \PrestaShopLogger::addLog($exception, 3);
                $this->errors[] = $this->l('Settings not saved');
            }
        }
    }

    public function displayForm(): string
    {
        $langId = (int)Cfg::get('PS_LANG_DEFAULT');
        $helper = new \HelperForm();

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

    public function formBasicOptions(): array
    {
        $form['form'] = [
            'legend' => [
                'title' => $this->l('Basic settings'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Client ID'),
                    'name' => 'TPAY_CLIENT_ID',
                    'size' => 50,
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Secret key'),
                    'name' => 'TPAY_SECRET_KEY',
                    'size' => 50,
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Merchant secret key (in notifications)'),
                    'name' => 'TPAY_MERCHANT_SECRET',
                    'size' => 400,
                    'required' => true,
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Use test Account'),
                    'name' => 'TPAY_DEBUG',
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_debug_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'tpay_debug_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                    'desc' => '<b>' . $this->l('WARNING') . '</b>'
                        . $this->l(' turn off in production mode'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Notification email'),
                    'desc' => $this->l('Set your own email with notifications.  Leave blank to use the email configured in the tpay panel.'),
                    'name' => 'TPAY_NOTIFICATION_EMAILS',
                    'size' => 50,
                    'required' => false,
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Surcharge for the use of payment'),
                    'name' => 'TPAY_SURCHARGE_ACTIVE',
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_surcharge_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'tpay_surcharge_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                ],
                [
                    'type' => 'radio',
                    'label' => $this->l('Surcharge type'),
                    'name' => 'TPAY_SURCHARGE_TYPE',
                    'is_bool' => false,
                    'class' => 'child',
                    'values' => [
                        [
                            'id' => 'tpay_surcharge_type_on',
                            'value' => Config::TPAY_SURCHARGE_AMOUNT,
                            'label' => $this->l('Quota'),
                        ],
                        [
                            'id' => 'tpay_surcharge_type_off',
                            'value' => Config::TPAY_SURCHARGE_PERCENT,
                            'label' => $this->l('Percentage'),
                        ],
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Surcharge value'),
                    'name' => 'TPAY_SURCHARGE_VALUE',
                    'size' => 50,
                    'required' => false,
                ]
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        return $form;
    }

    public function formPaymentOptions(): array
    {
        $form['form'] = [
            'legend' => [
                'title' => $this->l('Settings for standard payment'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $this->l('BLIK payments active'),
                    'name' => 'TPAY_BLIK_ACTIVE',
                    'desc' => $this->l('Show the method as a separate payment'),
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                ],

                [
                    'type' => 'switch',
                    'label' => $this->l('BLIK widget'),
                    'name' => 'TPAY_BLIK_WIDGET',
                    'desc' => $this->l('Display the payment method in the widget'),
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                ],

                [
                    'type' => 'switch',
                    'label' => $this->l('GooglePay'),
                    'name' => 'TPAY_GPAY_ACTIVE',
                    'desc' => $this->l('Show the method as a separate payment'),
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                ],

                [
                    'type' => 'switch',
                    'label' => $this->l('Alior Installment (from 300 PLN to 9 259,25 PLN)'),
                    'name' => 'TPAY_INSTALLMENTS_ACTIVE',
                    'desc' => $this->l('Show the method as a separate payment'),
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_installments_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'tpay_installments_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                ],

                [
                    'type' => 'switch',
                    'label' => $this->l('Twisto Installment (from 1 PLN to 1 500 PLN)'),
                    'name' => 'TPAY_TWISTO_ACTIVE',
                    'desc' => $this->l('Show the method as a separate payment'),
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_installments_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'tpay_installments_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        if (Shop::getContext() == Shop::CONTEXT_SHOP) {
            $globalSettingsSwitcher = [
                'type' => 'switch',
                'label' => $this->l('Use global settings'),
                'name' => 'TPAY_GLOBAL_SETTINGS',
                'desc' => $this->l('Use global settings'),
                'is_bool' => true,
                'class' => 'd-none',
                'values' => [
                    [
                        'id' => 'tpay_active_on',
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ],
                    [
                        'id' => 'tpay_active_off',
                        'value' => 0,
                        'label' => $this->l('No'),
                    ],
                ],
            ];

            array_unshift($form['form']['input'], $globalSettingsSwitcher);
        }

        return $form;
    }

    public function formCardOptions(): array
    {
        $form['form'] = [
            'legend' => [
                'title' => $this->l('Credit card settings'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $this->l('Payment credit card'),
                    'name' => 'TPAY_CARD_ACTIVE',
                    'desc' => $this->l('Show the method as a separate payment'),
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                ],

                [
                    'type' => 'text',
                    'label' => $this->l('RSA key'),
                    'name' => 'TPAY_CARD_RSA',
                    'size' => 400,
                    'required' => false,
                ],

            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        return $form;
    }

    public function formStatusesOptions(): array
    {
        $form['form'] = [
            'legend' => [
                'title' => $this->l('Transaction statuses'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'select',
                    'label' => $this->l('Status of the transaction in process'),
                    'name' => 'TPAY_PENDING',
                    'options' => [
                        'query' => $this->getOrderStates(),
                        'id' => 'id_order_state',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Status of paid transaction'),
                    'name' => 'TPAY_CONFIRMED',
                    'options' => [
                        'query' => $this->getOrderStates(),
                        'id' => 'id_order_state',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Payment error status'),
                    'name' => 'TPAY_ERROR',
                    'options' => [
                        'query' => $this->getOrderStates(),
                        'id' => 'id_order_state',
                        'name' => 'name',
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];
        return $form;
    }
}
