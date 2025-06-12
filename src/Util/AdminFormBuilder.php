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

    public function __construct(Tpay $module, Context $context, array $channels)
    {
        $this->module = $module;
        $this->context = $context;
        $this->channels = $channels;
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

    public function formCancelOrder(): array
    {
        $form['form'] = [
            'legend' => [
                'title' => $this->module->l('Auto cancel order settings'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Auto cancel active'),
                    'name' => 'TPAY_AUTO_CANCEL_ACTIVE',
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
                    'label' => $this->module->l('Use frontend to run CRON jobs'),
                    'name' => 'TPAY_AUTO_CANCEL_FRONTEND_RUN',
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
                    'label' => $this->module->l('Cancel orders and transactions after days'),
                    'name' => 'TPAY_AUTO_CANCEL_DAYS',
                    'required' => true,
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

    public function formGenericPaymentOptions(): array
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
