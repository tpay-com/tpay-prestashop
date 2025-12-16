<?php

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
            if (Tpay\Util\Cache::get('auto-cancel-'.$tokenPart)) {
                return;
            }
            Tpay\Util\Cache::set('auto-cancel-'.$tokenPart, 1, 1800);
        }

        $autoCancel = Container::getInstance()->get('tpay.services.auto_cancel');
        $autoCancel->cancelTransactions();

        $this->ajaxRender('[]');
    }
}
