<?php
/**MIT License
@license MIT

Copyright (c) 2026 Krajowy Integrator Płatności S.A.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

@author Krajowy Integrator Płatności S.A.
@copyright Krajowy Integrator Płatności S.A.*/

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
