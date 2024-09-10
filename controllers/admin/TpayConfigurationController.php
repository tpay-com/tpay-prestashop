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

    public function getOrderStates(): array
    {
        return OrderState::getOrderStates(Context::getContext()->language->id);
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
        $form[] = $this->formBasicOptions();
        $form[] = $this->formPeKaoInstallments();
        $form[] = $this->formPaymentOptions();
        $form[] = $this->formGenericPaymentOptions();
        $form[] = $this->formCardOptions();
        $form[] = $this->formStatusesOptions();

        return $form;
    }

    public function validatePostProcess(): bool
    {
        $res = true;

        if (Tools::getValue('TPAY_CARD_ACTIVE')) {
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

    public function formPeKaoInstallments(): array
    {
        $form['form'] = [
            'legend' => [
                'title' => $this->module->l('Pekao installments simulator settings'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Installment simulator active'),
                    'name' => 'TPAY_PEKAO_INSTALLMENTS_ACTIVE',
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->module->l('Yes'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->module->l('No'),
                        ],
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Merchant ID'),
                    'name' => 'TPAY_MERCHANT_ID',
                    'required' => true,
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('The installment simulator is available on the product page'),
                    'name' => 'TPAY_PEKAO_INSTALLMENTS_PRODUCT_PAGE',
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->module->l('Yes'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->module->l('No'),
                        ],
                    ],
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('The installment simulator is available in the shopping cart'),
                    'name' => 'TPAY_PEKAO_INSTALLMENTS_SHOPPING_CART',
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->module->l('Yes'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->module->l('No'),
                        ],
                    ],
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('The installment simulator is available in the checkout'),
                    'name' => 'TPAY_PEKAO_INSTALLMENTS_CHECKOUT',
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->module->l('Yes'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->module->l('No'),
                        ],
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Save'),
            ],
        ];

        return $form;
    }

    public function formBasicOptions(): array
    {
        $form['form'] = [
            'legend' => [
                'title' => $this->module->l('Basic settings'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->module->l('API Client ID'),
                    'name' => 'TPAY_CLIENT_ID',
                    'size' => 50,
                    'desc' => $this->module->l('Find in Merchant’s panel: Integration -> API -> Open API Keys'),
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('API Secret'),
                    'name' => 'TPAY_SECRET_KEY',
                    'size' => 50,
                    'desc' => $this->module->l('Find in Merchant’s panel: Integration -> API -> Open API Keys'),
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Merchant secret key (in notifications)'),
                    'name' => 'TPAY_MERCHANT_SECRET',
                    'size' => 400,
                    'desc' => $this->module->l('Find in Merchant’s panel: Settings -> Notifications'),
                    'required' => true,
                ],
                [
                    'type' => 'select',
                    'label' => $this->module->l('CRC field form'),
                    'name' => 'TPAY_CRC_FORM',
                    'options' => [
                        'query' => [
                            ['id' => 'md5_all', 'name' => 'md5($order->id . $customer->secure_key . time())'],
                            [
                                'id' => 'order_id_and_rest',
                                'name' => '$order->id . "-" . md5($customer->secure_key . time())'
                            ],
                            ['id' => 'order_id', 'name' => '$order->id'],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                    'class' => 't'
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Use Sandbox Account'),
                    'name' => 'TPAY_SANDBOX',
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_sandbox_on',
                            'value' => 1,
                            'label' => $this->module->l('Yes'),
                        ],
                        [
                            'id' => 'tpay_sandbox_off',
                            'value' => 0,
                            'label' => $this->module->l('No'),
                        ],
                    ],
                    'desc' => '<b>' . $this->module->l('WARNING') . '</b>'
                        . $this->module->l(
                            ' you will use sandbox mode - it is a different environment with mocked payment gateways - don\'t use it in production!'
                        ),
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Redirect directly to bank'),
                    'name' => 'TPAY_REDIRECT_TO_CHANNEL',
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_redirect_to_channel_on',
                            'value' => 1,
                            'label' => $this->module->l('Yes'),
                        ],
                        [
                            'id' => 'tpay_redirect_to_channel_off',
                            'value' => 0,
                            'label' => $this->module->l('No'),
                        ],
                    ]
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Notification email'),
                    'desc' => $this->module->l(
                        'Set your own email with notifications.  Leave blank to use the email configured in the tpay panel.'
                    ),
                    'name' => 'TPAY_NOTIFICATION_EMAILS',
                    'size' => 50,
                    'required' => false,
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Surcharge for the use of payment'),
                    'name' => 'TPAY_SURCHARGE_ACTIVE',
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_surcharge_on',
                            'value' => 1,
                            'label' => $this->module->l('Yes'),
                        ],
                        [
                            'id' => 'tpay_surcharge_off',
                            'value' => 0,
                            'label' => $this->module->l('No'),
                        ],
                    ],
                ],
                [
                    'type' => 'radio',
                    'label' => $this->module->l('Surcharge type'),
                    'name' => 'TPAY_SURCHARGE_TYPE',
                    'is_bool' => false,
                    'class' => 'child',
                    'values' => [
                        [
                            'id' => 'tpay_surcharge_type_on',
                            'value' => Config::TPAY_SURCHARGE_AMOUNT,
                            'label' => $this->module->l('Quota'),
                        ],
                        [
                            'id' => 'tpay_surcharge_type_off',
                            'value' => Config::TPAY_SURCHARGE_PERCENT,
                            'label' => $this->module->l('Percentage'),
                        ],
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Surcharge value'),
                    'name' => 'TPAY_SURCHARGE_VALUE',
                    'size' => 50,
                    'required' => false,
                ],
                [
                    'type' => '',
                    'name' => 'TPAY_NOTIFICATION_ADDRESS',
                    'label' => $this->module->l('Your address for notifications'),
                    'desc' => $this->context->link->getModuleLink('tpay', 'notifications'),
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Save'),
            ],
        ];

        return $form;
    }

    public function formPaymentOptions(): array
    {
        $form['form'] = [
            'legend' => [
                'title' => $this->module->l('Settings for standard payment'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $this->module->l('BLIK payments active'),
                    'name' => 'TPAY_BLIK_ACTIVE',
                    'desc' => $this->module->l('Show the method as a separate payment'),
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->module->l('Yes'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->module->l('No'),
                        ],
                    ],
                ],

                [
                    'type' => 'switch',
                    'label' => $this->module->l('BLIK widget'),
                    'name' => 'TPAY_BLIK_WIDGET',
                    'desc' => $this->module->l('Display the payment method in the widget. If you have other plugins that modify the shopping cart configuration, you should disable this option.'),
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->module->l('Yes'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->module->l('No'),
                        ],
                    ],
                ],

                [
                    'type' => 'switch',
                    'label' => $this->module->l('Transfer widget'),
                    'name' => 'TPAY_TRANSFER_WIDGET',
                    'desc' => $this->module->l('Display the payment method in the widget. If you have other plugins that modify the shopping cart configuration, you should disable this option.'),
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->module->l('Yes'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->module->l('No'),
                        ],
                    ],
                ],

                [
                    'type' => 'select',
                    'label' => $this->module->l('Custom order'),
                    'name' => 'TPAY_CUSTOM_ORDER[]',
                    'multiple' => true,
                    'class' => 'child',
                    'desc' => $this->module->l('Custom order of displayed banks. Drag to change order. The ability to change the order of payment methods is possible when the "Direct bank redirect" option is enabled.'),
                    'size' => 20,
                    'options' => [
                        'query' => $this->sortPayment('TPAY_CUSTOM_ORDER'),
                        'id' => 'id',
                        'name' => 'fullName'
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Save'),
            ],
        ];

        if (Shop::getContext() == Shop::CONTEXT_SHOP) {
            $globalSettingsSwitcher = [
                'type' => 'switch',
                'label' => $this->module->l('Use global settings'),
                'name' => 'TPAY_GLOBAL_SETTINGS',
                'desc' => $this->module->l('Use global settings'),
                'is_bool' => true,
                'class' => 'd-none',
                'values' => [
                    [
                        'id' => 'tpay_active_on',
                        'value' => 1,
                        'label' => $this->module->l('Yes'),
                    ],
                    [
                        'id' => 'tpay_active_off',
                        'value' => 0,
                        'label' => $this->module->l('No'),
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
                'title' => $this->module->l('Credit card settings'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Payment credit card'),
                    'name' => 'TPAY_CARD_ACTIVE',
                    'desc' => $this->module->l('Show the method as a separate payment'),
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->module->l('Yes'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->module->l('No'),
                        ],
                    ],
                ],

                [
                    'type' => 'switch',
                    'label' => $this->module->l('Card widget'),
                    'name' => 'TPAY_CARD_WIDGET',
                    'desc' => $this->module->l('Display the payment method in the widget. If you have other plugins that modify the shopping cart configuration, you should disable this option.'),
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->module->l('Yes'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->module->l('No'),
                        ],
                    ],
                ],

                [
                    'type' => 'text',
                    'label' => $this->module->l('RSA key'),
                    'name' => 'TPAY_CARD_RSA',
                    'desc' => $this->module->l('Find in Merchant’s panel: Credit cards payment -> API'),
                    'size' => 400,
                    'required' => false,
                ],

            ],
            'submit' => [
                'title' => $this->module->l('Save'),
            ],
        ];

        return $form;
    }

    public function formStatusesOptions(): array
    {
        $form['form'] = [
            'legend' => [
                'title' => $this->module->l('Transaction statuses'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'select',
                    'label' => $this->module->l('Status of the transaction in process'),
                    'name' => 'TPAY_PENDING',
                    'options' => [
                        'query' => $this->getOrderStates(),
                        'id' => 'id_order_state',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->module->l('Status of paid transaction'),
                    'name' => 'TPAY_CONFIRMED',
                    'options' => [
                        'query' => $this->getOrderStates(),
                        'id' => 'id_order_state',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->module->l('Payment error status'),
                    'name' => 'TPAY_ERROR',
                    'options' => [
                        'query' => $this->getOrderStates(),
                        'id' => 'id_order_state',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->module->l('Status of a paid transaction with virtual products only'),
                    'name' => 'TPAY_VIRTUAL_CONFIRMED',
                    'options' => [
                        'query' => $this->getOrderStates(),
                        'id' => 'id_order_state',
                        'name' => 'name',
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Save'),
            ],
        ];
        return $form;
    }

    private function formGenericPaymentOptions(): array
    {
        $result = $this->sortPayment('TPAY_GENERIC_PAYMENTS');

        return [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Generic payments'),
                    'icon' => 'icon-cogs'
                ],
                'input' => [
                    [
                        'type' => 'select',
                        'label' => $this->module->l('Select payments to Easy on-site mechanism to'),
                        'name' => 'TPAY_GENERIC_PAYMENTS[]',
                        'desc' => $this->module->l('Custom order of displayed payment methods. Drag to change order'),
                        'multiple' => true,
                        'size' => $result ? 20 : 1,
                        'options' => [
                            'query' => $result,
                            'id' => 'id',
                            'name' => 'fullName'
                        ],
                    ]
                ],
                'submit' => ['title' => $this->module->l('Save')],
            ]
        ];
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

    private function sortPayment(string $field): array
    {
        $channels = $this->channels;
        $chosenPayments = json_decode(Cfg::get($field), true) ?? [];

        if (count($chosenPayments) > 0) {
            $orderedList = [];

            foreach ($chosenPayments as $chosenPayment) {
                foreach ($channels as $key => $channel) {
                    if ($channel['id'] == $chosenPayment) {
                        $orderedList[$key] = $channel;
                        unset($channels[$key]);
                    }
                }
            }

            return array_merge($orderedList, $channels);
        }

        return $channels;
    }
}
