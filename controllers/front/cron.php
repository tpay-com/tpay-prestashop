<?php
/**
@author Krajowy Integrator Płatności S.A.
@copyright Krajowy Integrator Płatności S.A.
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
SOFTWARE.*/

use Configuration as Cfg;
use Tpay\Util\Container;

class TpayCronModuleFrontController extends ModuleFrontController
{
    /** @var bool If set to true, will be redirected to authentication page */
    public $auth = false;

    /** @var bool */
    public $ajax = 1;

    public function display()
    {
        if (!Cfg::get('TPAY_AUTO_CANCEL_ACTIVE') || (!Cfg::get('TPAY_AUTO_CANCEL_FRONTEND_RUN') && !Tools::isPHPCLI())) {
            $this->ajaxRender(json_encode(['error' => 'Forbidden call.']));
            exit;
        }

        if (!Tools::isPHPCLI()) {
            $tokenPart = (new DateTime())->format('Y-m-d-H');
            // set some protection to run it only once a day
            if (Tpay\Util\Cache::get('auto-cancel-' . $tokenPart)) {
                return;
            }
            Tpay\Util\Cache::set('auto-cancel-' . $tokenPart, 1, 1800);
        }

        $autoCancel = Container::getInstance()->get('tpay.services.auto_cancel');
        $autoCancel->cancelTransactions();

        $this->ajaxRender('[]');
    }
}
