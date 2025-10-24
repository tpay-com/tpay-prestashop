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

declare(strict_types=1);

namespace Tpay\Util;

use Configuration as Cfg;
use Context;
use OrderState;
use PrestaShopBundle\Translation\TranslatorComponent;
use Shop;
use Tpay;
use Tpay\Config\Config;

class AdminFormBuilder
{
    public $channels = [];

    /** @var Tpay */
    public $module;

    /** @var Context */
    public $context;

    /** @var TranslatorComponent|null  */
    private $translator;

    public function __construct(Tpay $module, Context $context, array $channels)
    {
        $this->module = $module;
        $this->context = $context;
        $this->channels = $channels;
        $this->translator = $this->module->getTranslator();
    }

    public function formBasicOptions(): array
    {
        $form['form'] = [
            'legend' => [
                'title' => $this->translator->trans('Basic settings', [], 'Modules.Tpay.Admin'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->translator->trans('API Client ID', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_CLIENT_ID',
                    'size' => 50,
                    'desc' => $this->translator->trans('Find in Merchant’s panel: Integration -> API -> Open API Keys', [], 'Modules.Tpay.Admin'),
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->translator->trans('API Secret', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_SECRET_KEY',
                    'size' => 50,
                    'desc' => $this->translator->trans('Find in Merchant’s panel: Integration -> API -> Open API Keys', [], 'Modules.Tpay.Admin'),
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->translator->trans('Merchant secret key (in notifications)', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_MERCHANT_SECRET',
                    'size' => 400,
                    'desc' => $this->translator->trans('Find in Merchant’s panel: Settings -> Notifications', [], 'Modules.Tpay.Admin'),
                    'required' => true,
                ],
                [
                    'type' => 'select',
                    'label' => $this->translator->trans('CRC field form', [], 'Modules.Tpay.Admin'),
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
                    'label' => $this->translator->trans('Use Sandbox Account', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_SANDBOX',
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_sandbox_on',
                            'value' => 1,
                            'label' => $this->translator->trans('Yes', [], 'Modules.Tpay.Admin'),
                        ],
                        [
                            'id' => 'tpay_sandbox_off',
                            'value' => 0,
                            'label' => $this->translator->trans('No', [], 'Modules.Tpay.Admin'),
                        ],
                    ],
                    'desc' => '<b>' . $this->translator->trans('WARNING', [], 'Modules.Tpay.Admin') . '</b> '
                        . $this->translator->trans(
                            ' you will use sandbox mode - it is a different environment with mocked payment gateways - don\'t use it in production!',
                            [],
                            'Modules.Tpay.Admin'
                        ),
                ],
                [
                    'type' => 'switch',
                    'label' => $this->translator->trans('Redirect directly to bank', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_REDIRECT_TO_CHANNEL',
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_redirect_to_channel_on',
                            'value' => 1,
                            'label' => $this->translator->trans('Yes', [], 'Modules.Tpay.Admin'),
                        ],
                        [
                            'id' => 'tpay_redirect_to_channel_off',
                            'value' => 0,
                            'label' => $this->translator->trans('No', [], 'Modules.Tpay.Admin'),
                        ],
                    ]
                ],
                [
                    'type' => 'text',
                    'label' => $this->translator->trans('Notification email', [], 'Modules.Tpay.Admin'),
                    'desc' => $this->translator->trans(
                        'Set your own email with notifications.  Leave blank to use the email configured in the tpay panel.',
                        [],
                        'Modules.Tpay.Admin'
                    ),
                    'name' => 'TPAY_NOTIFICATION_EMAILS',
                    'size' => 50,
                    'required' => false,
                ],
                [
                    'type' => 'switch',
                    'label' => $this->translator->trans('Surcharge for the use of payment', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_SURCHARGE_ACTIVE',
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_surcharge_on',
                            'value' => 1,
                            'label' => $this->translator->trans('Yes', [], 'Modules.Tpay.Admin'),
                        ],
                        [
                            'id' => 'tpay_surcharge_off',
                            'value' => 0,
                            'label' => $this->translator->trans('No', [], 'Modules.Tpay.Admin'),
                        ],
                    ],
                ],
                [
                    'type' => 'radio',
                    'label' => $this->translator->trans('Surcharge type', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_SURCHARGE_TYPE',
                    'is_bool' => false,
                    'class' => 'child',
                    'values' => [
                        [
                            'id' => 'tpay_surcharge_type_on',
                            'value' => Config::TPAY_SURCHARGE_AMOUNT,
                            'label' => $this->translator->trans('Quota', [], 'Modules.Tpay.Admin'),
                        ],
                        [
                            'id' => 'tpay_surcharge_type_off',
                            'value' => Config::TPAY_SURCHARGE_PERCENT,
                            'label' => $this->translator->trans('Percentage', [], 'Modules.Tpay.Admin'),
                        ],
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => $this->translator->trans('Surcharge value', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_SURCHARGE_VALUE',
                    'size' => 50,
                    'required' => false,
                ],
                [
                    'type' => '',
                    'name' => 'TPAY_NOTIFICATION_ADDRESS',
                    'label' => $this->translator->trans('Your address for notifications', [], 'Modules.Tpay.Admin'),
                    'desc' => $this->context->link->getModuleLink('tpay', 'notifications'),
                ],
            ],
            'submit' => [
                'title' => $this->translator->trans('Save', [], 'Modules.Tpay.Admin'),
            ],
        ];

        return $form;
    }

    public function formPeKaoInstallments(): array
    {
        $form['form'] = [
            'legend' => [
                'title' => $this->translator->trans('Pekao installments simulator settings', [], 'Modules.Tpay.Admin'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $this->translator->trans('Installment simulator active', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_PEKAO_INSTALLMENTS_ACTIVE',
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->translator->trans('Yes', [], 'Modules.Tpay.Admin'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->translator->trans('No', [], 'Modules.Tpay.Admin'),
                        ],
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => $this->translator->trans('Merchant ID', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_MERCHANT_ID',
                    'required' => true,
                ],
                [
                    'type' => 'switch',
                    'label' => $this->translator->trans('The installment simulator is available on the product page', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_PEKAO_INSTALLMENTS_PRODUCT_PAGE',
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->translator->trans('Yes', [], 'Modules.Tpay.Admin'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->translator->trans('No', [], 'Modules.Tpay.Admin'),
                        ],
                    ],
                ],
                [
                    'type' => 'switch',
                    'label' => $this->translator->trans('The installment simulator is available in the shopping cart', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_PEKAO_INSTALLMENTS_SHOPPING_CART',
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->translator->trans('Yes', [], 'Modules.Tpay.Admin'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->translator->trans('No', [], 'Modules.Tpay.Admin'),
                        ],
                    ],
                ],
                [
                    'type' => 'switch',
                    'label' => $this->translator->trans('The installment simulator is available in the checkout', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_PEKAO_INSTALLMENTS_CHECKOUT',
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->translator->trans('Yes', [], 'Modules.Tpay.Admin'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->translator->trans('No', [], 'Modules.Tpay.Admin'),
                        ],
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->translator->trans('Save', [], 'Modules.Tpay.Admin'),
            ],
        ];

        return $form;
    }

    public function formCancelOrder(): array
    {
        $form['form'] = [
            'legend' => [
                'title' => $this->translator->trans('Auto cancel orders and transactions settings', [], 'Modules.Tpay.Admin'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $this->translator->trans('Auto cancel active', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_AUTO_CANCEL_ACTIVE',
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->translator->trans('Yes', [], 'Modules.Tpay.Admin'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->translator->trans('No', [], 'Modules.Tpay.Admin'),
                        ],
                    ],
                ],
                [
                    'type' => 'switch',
                    'label' => $this->translator->trans('Use frontend to run CRON jobs', [], 'Modules.Tpay.Admin'),
                    'desc' => '<b>' . $this->translator->trans('WARNING', [], 'Modules.Tpay.Admin') . '</b> '.$this->translator->trans('May cause some performance issues. Use this method if you cannot set cronjob to run CLI task once a day: `php modules/tpay/cron.php`', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_AUTO_CANCEL_FRONTEND_RUN',
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->translator->trans('Yes', [], 'Modules.Tpay.Admin'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->translator->trans('No', [], 'Modules.Tpay.Admin'),
                        ],
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => $this->translator->trans('Cancel orders and transactions after days', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_AUTO_CANCEL_DAYS',
                    'required' => true,
                ],
            ],
            'submit' => [
                'title' => $this->translator->trans('Save', [], 'Modules.Tpay.Admin'),
            ],
        ];

        return $form;
    }


    public function formPaymentOptions(): array
    {
        $form['form'] = [
            'legend' => [
                'title' => $this->translator->trans('Settings for standard payment', [], 'Modules.Tpay.Admin'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $this->translator->trans('BLIK payments active', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_BLIK_ACTIVE',
                    'desc' => $this->translator->trans('Show the method as a separate payment', [], 'Modules.Tpay.Admin'),
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->translator->trans('Yes', [], 'Modules.Tpay.Admin'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->translator->trans('No', [], 'Modules.Tpay.Admin'),
                        ],
                    ],
                ],

                [
                    'type' => 'switch',
                    'label' => $this->translator->trans('BLIK widget', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_BLIK_WIDGET',
                    'desc' => $this->translator->trans('Display the payment method in the widget. If you have other plugins that modify the shopping cart configuration, you should disable this option.', [], 'Modules.Tpay.Admin'),
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->translator->trans('Yes', [], 'Modules.Tpay.Admin'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->translator->trans('No', [], 'Modules.Tpay.Admin'),
                        ],
                    ],
                ],

                [
                    'type' => 'switch',
                    'label' => $this->translator->trans('Transfer widget', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_TRANSFER_WIDGET',
                    'desc' => $this->translator->trans('Display the payment method in the widget. If you have other plugins that modify the shopping cart configuration, you should disable this option.', [], 'Modules.Tpay.Admin'),
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->translator->trans('Yes', [], 'Modules.Tpay.Admin'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->translator->trans('No', [], 'Modules.Tpay.Admin'),
                        ],
                    ],
                ],

                [
                    'type' => 'select',
                    'label' => $this->translator->trans('Custom order', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_CUSTOM_ORDER[]',
                    'multiple' => true,
                    'class' => 'child',
                    'desc' => $this->translator->trans('Custom order of displayed banks. Drag to change order. The ability to change the order of payment methods is possible when the "Direct bank redirect" option is enabled.', [], 'Modules.Tpay.Admin'),
                    'size' => 20,
                    'options' => [
                        'query' => $this->sortPayment('TPAY_CUSTOM_ORDER'),
                        'id' => 'id',
                        'name' => 'fullName'
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->translator->trans('Save', [], 'Modules.Tpay.Admin'),
            ],
        ];

        if (Shop::getContext() == Shop::CONTEXT_SHOP) {
            $globalSettingsSwitcher = [
                'type' => 'switch',
                'label' => $this->translator->trans('Use global settings', [], 'Modules.Tpay.Admin'),
                'name' => 'TPAY_GLOBAL_SETTINGS',
                'desc' => $this->translator->trans('Use global settings', [], 'Modules.Tpay.Admin'),
                'is_bool' => true,
                'class' => 'd-none',
                'values' => [
                    [
                        'id' => 'tpay_active_on',
                        'value' => 1,
                        'label' => $this->translator->trans('Yes', [], 'Modules.Tpay.Admin'),
                    ],
                    [
                        'id' => 'tpay_active_off',
                        'value' => 0,
                        'label' => $this->translator->trans('No', [], 'Modules.Tpay.Admin'),
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
                'title' => $this->translator->trans('Credit card settings', [], 'Modules.Tpay.Admin'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $this->translator->trans('Payment credit card', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_CARD_ACTIVE',
                    'desc' => $this->translator->trans('Show the method as a separate payment', [], 'Modules.Tpay.Admin'),
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->translator->trans('Yes', [], 'Modules.Tpay.Admin'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->translator->trans('No', [], 'Modules.Tpay.Admin'),
                        ],
                    ],
                ],

                [
                    'type' => 'switch',
                    'label' => $this->translator->trans('Card widget', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_CARD_WIDGET',
                    'desc' => $this->translator->trans('Display the payment method in the widget. If you have other plugins that modify the shopping cart configuration, you should disable this option.', [], 'Modules.Tpay.Admin'),
                    'is_bool' => true,
                    'class' => 't',
                    'values' => [
                        [
                            'id' => 'tpay_active_on',
                            'value' => 1,
                            'label' => $this->translator->trans('Yes', [], 'Modules.Tpay.Admin'),
                        ],
                        [
                            'id' => 'tpay_active_off',
                            'value' => 0,
                            'label' => $this->translator->trans('No', [], 'Modules.Tpay.Admin'),
                        ],
                    ],
                ],

                [
                    'type' => 'text',
                    'label' => $this->translator->trans('RSA key', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_CARD_RSA',
                    'desc' => $this->translator->trans('Find in Merchant’s panel: Credit cards payment -> API', [], 'Modules.Tpay.Admin'),
                    'size' => 400,
                    'required' => false,
                ],

            ],
            'submit' => [
                'title' => $this->translator->trans('Save', [], 'Modules.Tpay.Admin'),
            ],
        ];

        return $form;
    }

    public function formStatusesOptions(): array
    {
        $form['form'] = [
            'legend' => [
                'title' => $this->translator->trans('Transaction statuses', [], 'Modules.Tpay.Admin'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'select',
                    'label' => $this->translator->trans('Status of the transaction in process', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_PENDING',
                    'options' => [
                        'query' => $this->getOrderStates(),
                        'id' => 'id_order_state',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->translator->trans('Status of paid transaction', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_CONFIRMED',
                    'options' => [
                        'query' => $this->getOrderStates(),
                        'id' => 'id_order_state',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->translator->trans('Payment error status', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_ERROR',
                    'options' => [
                        'query' => $this->getOrderStates(),
                        'id' => 'id_order_state',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->translator->trans('Status of a paid transaction with virtual products only', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_VIRTUAL_CONFIRMED',
                    'options' => [
                        'query' => $this->getOrderStates(),
                        'id' => 'id_order_state',
                        'name' => 'name',
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->translator->trans('Save', [], 'Modules.Tpay.Admin'),
            ],
        ];
        return $form;
    }

    public function getOrderStates(): array
    {
        return OrderState::getOrderStates(Context::getContext()->language->id);
    }

    private function sortPayment(string $field): array
    {
        $channels = $this->channels;

        if (!Cfg::get($field)) {
            return $channels;
        }

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
