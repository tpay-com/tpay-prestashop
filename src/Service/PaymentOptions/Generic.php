<?php

declare(strict_types=1);

namespace Tpay\Service\PaymentOptions;

use Context;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Tpay\Service\GenericPayments\GenericPaymentsManager;

class Generic implements GatewayType
{
    public function getPaymentOption(\Tpay $module, PaymentOption $paymentOption, array $data = []): PaymentOption
    {
        $moduleLink = Context::getContext()->link->getModuleLink('tpay', 'payment', [], true);
        $paymentOption->setCallToActionText($data['fullName'])
            ->setAction($moduleLink)
            ->setLogo($data['image']['url'])
            ->setForm($this->generateForm($moduleLink, $data['id']));

        return $paymentOption;
    }

    private function generateForm(string $moduleLink, $channelId): string
    {
        Context::getContext()->smarty->assign([
            'action' => $moduleLink,
            'tpay' => 'true',
            'channelId' => $channelId,
            'blikBnplId' => GenericPaymentsManager::CHANNEL_BLIK_BNPL,
            'tpay_channel_id' => 0,
            'type' => 'generic',
        ]);

        return Context::getContext()->smarty->fetch('module:tpay/views/templates/hook/generic.tpl');
    }
}
