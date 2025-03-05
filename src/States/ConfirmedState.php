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

namespace Tpay\States;

use OrderState;

class ConfirmedState implements StateType
{
    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var OrderState
     */
    private $orderState;

    public $stateLanguage = [
        'pl' => 'Płatność zaakceptowana (Tpay)',
        'en' => 'Payment accepted (Tpay)',
        'ua' => 'Платіж прийнято (Tpay)',
        'de' => 'Zahlung akzeptiert (Tpay)',
        'cz' => 'Přijímání plateb (Tpay)',
        'sk' => 'Prijaté platby (Tpay)',
    ];

    public $id;

    public function __construct(
        OrderState $orderState,
        string $moduleName
    ) {
        $this->moduleName = $moduleName;
        $this->orderState = $orderState;
    }

    /**
     * @return OrderState
     */
    public function create(): OrderState
    {
        $name = [];

        foreach (\Language::getLanguages() as $lang) {
            $name[$lang['id_lang']] = $this->stateLanguage[$lang['iso_code']]
                ?? $this->stateLanguage['pl'];
        }

        $this->orderState->name = $name;
        $this->orderState->send_email = true;
        $this->orderState->invoice = true;
        $this->orderState->color = '#00DE69';
        $this->orderState->template = 'payment';
        $this->orderState->unremovable = false;
        $this->orderState->logable = true;
        $this->orderState->module_name = $this->moduleName;
        $this->orderState->paid = true;
        $this->orderState->pdf_invoice = true;
        $this->orderState->pdf_delivery = true;
        $this->orderState->add();

        $this->id = $this->orderState->id;

        return $this->orderState;
    }
}
