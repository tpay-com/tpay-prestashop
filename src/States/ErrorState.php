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

class ErrorState implements StateType
{
    public $stateLanguage = [
        'pl' => 'Błąd płatności (Tpay)',
        'en' => 'Error payment (Tpay)',
        'ua' => 'Помилковий платіж (Tpay)',
        'de' => 'Fehlerhafte Zahlung (Tpay)',
        'cz' => 'Chybná platba (Tpay)',
        'sk' => 'Chybná platba (Tpay)',
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
        $this->orderState->send_email = false;
        $this->orderState->invoice = false;
        $this->orderState->color = '#b52b27';
        $this->orderState->unremovable = false;
        $this->orderState->logable = false;
        $this->orderState->module_name = $this->moduleName;
        $this->orderState->paid = false;
        $this->orderState->pdf_invoice = false;
        $this->orderState->pdf_delivery = false;
        $this->orderState->add();

        $this->id = $this->orderState->id;

        return $this->orderState;
    }
}
