<?php
/**
 * @author Krajowy Integrator Płatności S.A.
 * @copyright Krajowy Integrator Płatności S.A.
 * @license MIT
 *
 * Copyright (c) 2026 Krajowy Integrator Płatności S.A.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Tpay\States;

use Language;
use OrderState;

class ConfirmedState implements StateType
{
    public $stateLanguage = [
        'pl' => 'Płatność zaakceptowana (Tpay)',
        'en' => 'Payment accepted (Tpay)',
        'ua' => 'Платіж прийнято (Tpay)',
        'de' => 'Zahlung akzeptiert (Tpay)',
        'cz' => 'Přijímání plateb (Tpay)',
        'sk' => 'Prijaté platby (Tpay)',
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
