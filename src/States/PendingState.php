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

use Language;
use OrderState;

class PendingState implements StateType
{
    public $stateLanguage = [
        'pl' => 'Oczekiwanie na płatność (Tpay)',
        'en' => 'Waiting for payment (Tpay)',
        'ua' => 'Очікування платежу (Tpay)',
        'de' => 'Warten auf Zahlung (Tpay)',
        'cz' => 'Čekání na platbu (Tpay)',
        'sk' => 'Čakanie na platbu (Tpay)',
    ];
    public $id;

    /** @var string */
    private $moduleName;

    /** @var OrderState */
    private $orderState;

    public function __construct(
        OrderState $orderState,
        string $moduleName
    ) {
        $this->moduleName = $moduleName;
        $this->orderState = $orderState;
    }

    public function create(): OrderState
    {
        $name = [];

        foreach (Language::getLanguages() as $lang) {
            $name[$lang['id_lang']] = $this->stateLanguage[$lang['iso_code']]
                ?? $this->stateLanguage['pl'];
        }

        $this->orderState->name = $name;
        $this->orderState->color = '#5bc0de';
        $this->orderState->send_email = false;
        $this->orderState->invoice = false;
        $this->orderState->logable = false;
        $this->orderState->unremovable = false;
        $this->orderState->module_name = $this->moduleName;
        $this->orderState->add();

        $this->id = $this->orderState->id;

        return $this->orderState;
    }
}
