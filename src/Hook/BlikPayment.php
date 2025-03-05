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

namespace Tpay\Hook;

class BlikPayment extends AbstractHook
{
    public const AVAILABLE_HOOKS = [
        'displayOrderConfirmation',
    ];

    public function displayOrderConfirmation($params): string
    {
        if (!$this->module->active) {
            return '';
        }

        $transactionRepository = $this->module->getService('tpay.repository.transaction');
        $transaction = $transactionRepository->getTransactionByOrderId($params['order']->id);

        if ($transaction && $transaction['status'] == 'pending' && $transaction['payment_type'] === 'blik') {
            $moduleLink = $this->context->link->getModuleLink('tpay', 'chargeBlik', [], true);
            $blikData = [
                'orderId' => $params['order']->id,
                'cartId' => $params['order']->id_cart,
                'blikUrl' => $moduleLink,
                'transactionId' => $transaction['transaction_id'],
                'tpayStatus' => $transaction['status'],
                'assets_path' => $this->module->getPath(),
            ];
            $this->context->smarty->assign($blikData);

            return $this->module->fetch('module:tpay/views/templates/hook/thank_you_page.tpl');
        }

        return '';
    }
}
