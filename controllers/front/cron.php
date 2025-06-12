<?php

use Configuration as Cfg;

class TpayCronModuleFrontController extends ModuleFrontController
{
    /** @var bool If set to true, will be redirected to authentication page */
    public $auth = false;

    /** @var bool */
    public $ajax = 1;

    public function display()
    {
        if (!Cfg::get('TPAY_AUTO_CANCEL_ACTIVE') || (!Cfg::get('TPAY_AUTO_CANCEL_FRONTEND_RUN') && !Tools::isPHPCLI())) {
            $this->ajaxRender('Forbidden call.');
            die;
        }

        echo Cfg::get('PS_OS_CANCELED');

        echo "OK";

        $this->ajaxRender("\n");
    }
}
