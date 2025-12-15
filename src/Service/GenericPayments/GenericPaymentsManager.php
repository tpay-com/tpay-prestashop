<?php

namespace Tpay\Service\GenericPayments;

use Configuration as Cfg;

class GenericPaymentsManager
{
    public const CHANNEL_BLIK_BNPL = 84;
    public const EXTRACTED_PAYMENT_CHANNELS = [
        self::CHANNEL_BLIK_BNPL => 'TPAY_BLIK_BNPL_ACTIVE'
    ];

    private $activeChannels;

    /**
     * For 1.7.X => Symfony\Component\Translation\TranslatorInterface
     * Newer versions => PrestaShopBundle\Translation\TranslatorInterface
     */
    private $translator;

    public function __construct(array $activeChannels, $translator = null)
    {
        $this->activeChannels = $activeChannels;
        $this->translator = $translator;
    }

    public function buildGenericPaymentForm(): array
    {
        $sortedPayments = $this->getSortedPayments('TPAY_GENERIC_PAYMENTS');

        return [
            'form' => [
                'legend' => [
                    'title' => $this->translator->trans('Generic payments', [], 'Modules.Tpay.Admin'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [[
                    'type' => 'select',
                    'label' => $this->translator->trans('Select payments to Easy on-site mechanism', [], 'Modules.Tpay.Admin'),
                    'name' => 'TPAY_GENERIC_PAYMENTS[]',
                    'desc' => $this->translator->trans('Custom order of displayed payment methods. Drag to change order', [], 'Modules.Tpay.Admin'),
                    'multiple' => true,
                    'size' => $sortedPayments ? 20 : 1,
                    'options' => [
                        'query' => $sortedPayments,
                        'id' => 'id',
                        'name' => 'fullName',
                    ],
                ]],
                'submit' => [
                    'title' => $this->translator->trans('Save', [], 'Modules.Tpay.Admin'),
                ],
            ],
        ];
    }

    public function customOrderForm(): array
    {
        return [
            'type' => 'select',
            'label' => $this->translator->trans('Custom order', [], 'Modules.Tpay.Admin'),
            'name' => 'TPAY_CUSTOM_ORDER[]',
            'multiple' => true,
            'class' => 'child',
            'desc' => $this->translator->trans('Custom order of displayed banks. Drag to change order. The ability to change the order of payment methods is possible when the "Direct bank redirect" option is enabled.', [], 'Modules.Tpay.Admin'),
            'size' => 20,
            'options' => [
                'query' => $this->getSortedPayments('TPAY_CUSTOM_ORDER'),
                'id' => 'id',
                'name' => 'fullName'
            ],
        ];
    }

    public function buildBlikBnplForm(): array
    {
        $activeIds = array_map(static function ($channel) {
            return (int) $channel['id'];
        }, $this->activeChannels);

        return $this->blikBnplForm(in_array(self::CHANNEL_BLIK_BNPL, $activeIds, true));
    }

    public static function isChannelExcluded(int $channelId): bool
    {
        $configField = self::EXTRACTED_PAYMENT_CHANNELS[$channelId] ?? null;

        return $configField && (int) \Configuration::get($configField) === 1;
    }

    private function blikBnplForm(bool $isActive): array
    {
        $title = $this->translator->trans('BLIK Pay Later', [], 'Modules.Tpay.Admin');
        $configField = self::EXTRACTED_PAYMENT_CHANNELS[self::CHANNEL_BLIK_BNPL];

        $disabledHelp = '';
        if (!$isActive) {
            \Configuration::updateValue($configField, 0);

            $disabledHelp = '<details class="tpay-collapsible-desc" style="margin-top:6px;">'
                . '<summary style="cursor: pointer;"><a>' . $this->translator->trans('Can\'t enable BLIK Pay Later?', [], 'Modules.Tpay.Admin') . '</a></summary>'
                . '<div style="margin-top:8px; padding:8px; border:1px solid #d6d4d4; background:#f8f8f8; border-radius:4px;">'
                . $this->translator->trans('Log in to', [], 'Modules.Tpay.Admin') . ' '
                . '<a href="https://panel.tpay.com" target="_blank">' . $this->translator->trans('Tpay Merchant Panel', [], 'Modules.Tpay.Admin') . '</a> '
                . $this->translator->trans('and check if BLIK Pay Later is active. If the payment option is not enabled, activate it and then re-enable it in your store.', [], 'Modules.Tpay.Admin')
                . '</div></details>';
        }

        $collapsible = '<details class="tpay-collapsible-desc" style="margin-top:6px;">'
            . '<summary style="cursor: pointer;"><a>' . $this->translator->trans('What is BLIK Pay Later?', [], 'Modules.Tpay.Admin') . '</a></summary>'
            . '<div style="margin-top:8px; padding:8px; border:1px solid #d6d4d4; background:#f8f8f8; border-radius:4px;">'
            . $this->translator->trans('BLIK Pay Later is a deferred payment service for transactions ranging from 30 PLN to 4,000 PLN. You will receive the money for the sold goods immediately, while the Customer will have 30 days to make the payment.', [], 'Modules.Tpay.Admin')
            . '<a style="display: block;" target="_blank" href="https://www.blik.com/place-pozniej-dla-sklepow">' . $this->translator->trans('Learn more', [], 'Modules.Tpay.Admin') . '</a>'
            . '</div></details>';

        return [
            'type' => 'switch',
            'label' => $title,
            'name' => $configField,
            'is_bool' => true,
            'disabled' => !$isActive,
            'desc' => $disabledHelp . $collapsible,
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
    }

    private function getSortedPayments($key): array
    {
        $storedOrder = json_decode((string) Cfg::get($key), true) ?? [];

        $indexedChannels = [];
        foreach ($this->activeChannels as $ch) {
            if (!isset(self::EXTRACTED_PAYMENT_CHANNELS[$ch['id']])) {
                $indexedChannels[$ch['id']] = $ch;
            }
        }

        if (empty($storedOrder)) {
            return $indexedChannels;
        }

        $ordered = [];
        foreach ($storedOrder as $id) {
            if (isset($indexedChannels[$id])) {
                $ordered[] = $indexedChannels[$id];
                unset($indexedChannels[$id]);
            }
        }

        return array_merge($ordered, array_values($indexedChannels));
    }
}