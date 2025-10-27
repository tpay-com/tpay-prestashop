<?php

namespace Tpay\Service\GenericPayments;

use Configuration as Cfg;
use PrestaShopBundle\Translation\TranslatorInterface;

class GenericPaymentsManager
{
    private const CHANNEL_BLIK_BNPL = 84;
    public const EXTRACTED_PAYMENT_CHANNELS = [
        self::CHANNEL_BLIK_BNPL => 'TPAY_BLIK_BNPL_ACTIVE'
    ];

    private $activeChannels;

    /** @var TranslatorInterface$translator */
    private $translator;

    public function __construct(array $activeChannels, TranslatorInterface $translator)
    {
        $this->activeChannels = $activeChannels;
        $this->translator = $translator;
    }

    public function getForms(): array
    {
        return array_merge(
            $this->buildGenericPaymentForm(),
            $this->buildExtractedChannelsForms()
        );
    }

    private function buildExtractedChannelsForms(): array
    {
        $activeIds = array_map(static function ($channel) {
            return (int) $channel['id'];
        }, $this->activeChannels);

        $forms = [];
        $forms[] = $this->buildBlikBnplForm(in_array(self::CHANNEL_BLIK_BNPL, $activeIds, true));

        return array_filter($forms);
    }

    private function buildBlikBnplForm(bool $isActive): array
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
            . '<a style="display: block;" target="_blank" href="https://www.blik.com/place-pozniej">' . $this->translator->trans('Learn more', [], 'Modules.Tpay.Admin') . '</a>'
            . '</div></details>';

        return [
            'form' => [
                'legend' => [
                    'title' => $title,
                    'icon' => 'icon-cogs',
                ],
                'input' => [[
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
                ]],
                'submit' => [
                    'title' => $this->translator->trans('Save', [], 'Modules.Tpay.Admin'),
                ],
            ],
        ];
    }

    private function buildGenericPaymentForm(): array
    {
        $sortedPayments = $this->getSortedPayments();

        return [[
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
        ]];
    }

    private function getSortedPayments(): array
    {
        $storedOrder = json_decode((string) Cfg::get('TPAY_GENERIC_PAYMENTS'), true) ?? [];

        if (empty($storedOrder)) {
            return $this->activeChannels;
        }

        $indexedChannels = [];
        foreach ($this->activeChannels as $ch) {
            if (!$this->isChannelExcluded((int) $ch['id'])) {
                $indexedChannels[$ch['id']] = $ch;
            }
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

    private function isChannelExcluded(int $channelId): bool
    {
        return in_array($channelId, array_keys(self::EXTRACTED_PAYMENT_CHANNELS), true);
    }
}