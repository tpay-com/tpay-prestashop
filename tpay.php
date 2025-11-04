<?php

/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @license LICENSE.txt
 */

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    include_once $autoloadPath;
}

use Configuration as Cfg;
use Tpay\Config\Config;
use Tpay\Exception\BaseException;
use Tpay\Handler\InstallQueryHandler;
use Tpay\HookDispatcher;
use Tpay\Install\Install;
use Tpay\Install\Reset;
use Tpay\Install\Uninstall;
use Tpay\OpenApi\Utilities\Logger;
use Tpay\States\FactoryState;
use Tpay\Util\Container;
use Tpay\Util\Helper;
use Tpay\Util\PsrCache;
use Tpay\Util\PsrLogger;
use Tpay\OpenApi\Api\TpayApi;

class Tpay extends PaymentModule
{
    const AUTH_TOKEN_CACHE_KEY = 'tpay_auth_token_%s';

    // phpcs:ignore
    public $_errors;

    /** @var string */
    public $name;

    /** @var string */
    public $tab;

    /** @var string */
    public $version;

    /** @var string */
    public $author;

    /** @var integer */
    public $need_instance;

    /** @var array */
    public $ps_versions_compliancy;

    /** @var boolean */
    public $bootstrap;

    /** @var boolean */
    public $currencies;

    /** @var string */
    public $currencies_mode;

    /** @var integer */
    public $is_eu_compatible;

    /** @var string */
    public $module_key;

    /** @var string */
    public $displayName;

    /** @var string */
    public $description;

    /** @var string */
    public $confirmUninstall;

    // phpcs:ignore
    public $_path;

    /** @var HookDispatcher */
    private $hookDispatcher;

    private $api;

    public $tabs = [
        [
            'class_name' => 'TpayConfiguration',
            'visible' => false,
            'name' => 'Tpay',
        ],
    ];

    /**
     * Basic module info.
     * @throws Exception
     */
    public function __construct()
    {
        $this->name = 'tpay';
        $this->tab = 'payments_gateways';
        $this->version = '1.12.2';
        $this->author = 'Krajowy Integrator Płatności S.A.';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->is_eu_compatible = 1;
        $this->module_key = 'f2eb0ce26233d0b517ba41e81f2e62fe';

        parent::__construct();

        $this->displayName = $this->trans('Tpay', [], 'Modules.Tpay.Admin');
        $this->description = $this->trans('Accepting online payments', [], 'Modules.Tpay.Admin');
        $this->confirmUninstall = $this->trans('Delete this module?', [], 'Modules.Tpay.Admin');
        $this->hookDispatcher = new HookDispatcher($this);
    }

    /** Boot API when it's needed */
    public function __get($name)
    {
        if ('api' === $name) {
            if (null === $this->api) {
                $this->initAPI();
            }

            return $this->api;
        }
    }

    public function api()
    {
        if (null === $this->api) {
            $this->initAPI();
        }

        return $this->api;
    }

    public function authorization(): bool
    {
        try {
            $this->api()->authorization();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function buildInfo(): string
    {
        return sprintf(
            "prestashop:%s|tpay-prestashop:%s|tpay-openapi-php:%s|PHP:%s",
            $this->getPrestaVersion(),
            $this->version,
            $this->getPackageVersion(),
            phpversion()
        );
    }

    /**
     * @return false|object|null
     * @throws Exception
     */
    public function getService(string $serviceName)
    {
        $container = Container::getInstance();
        if ($container !== null) {
            return $container->get($serviceName);
        }

        throw new \Exception('Cannot get service ' . $serviceName);
    }

    public function getPath(): string
    {
        return $this->_path;
    }

    /**
     * Module installation.
     * @throws Exception
     * @throws BaseException
     */
    public function install(): bool
    {
        if (version_compare(phpversion(), '7.1', '<')) {
            $this->_errors[] = $this->trans(
                sprintf(
                    'Your PHP version is too old, please upgrade to a newer version. Your version is %s,'
                    . ' library requires %s',
                    phpversion(),
                    '7.1'
                ),
                [],
                'Modules.Tpay.Admin'
            );
        }

        if (
            !parent::install() || false === (new Install(
                $this,
                new InstallQueryHandler()
            ))->install()
        ) {
            $this->_errors[] = $this->trans('Installation error', [], 'Modules.Tpay.Admin');
        }

        if (!$this->addOrderStates()) {
            $this->_errors[] = $this->trans('Error adding order statuses', [], 'Modules.Tpay.Admin');
        }

        if (!empty($this->_errors)) {
            return false;
        }

        $this->registerHook('displayInvoiceLegalFreeText');
        $this->registerHook('actionEmailAddAfterContent');
        $this->registerHook('actionValidateOrder');
        $this->registerHook('displayAdminOrder');
        $this->registerHook('displayOrderConfirmation');
        $this->registerHook('displayPDFInvoice');
        $this->registerHook('displayAdminOrderMainBottom');
        $this->registerHook('displayOrderDetail');
        $this->registerHook('displayProductAdditionalInfo');
        $this->registerHook('displayShoppingCart');
        $this->registerHook('displayCheckoutSummaryTop');

        $this->registerHook($this->getHookDispatcher()->getAvailableHooks());

        return true;
    }

    /**
     * Module uninstall.
     * @return boolean
     * @throws PrestaShopException
     * @throws BaseException
     */
    public function uninstall(): bool
    {
        if (
            !parent::uninstall() || false === (new Uninstall(
                $this,
                new InstallQueryHandler()
            ))->uninstallDb()
        ) {
            $this->_errors[] = $this->trans('Installation error', [], 'Modules.Tpay.Admin');
        }

        if (!empty($this->_errors)) {
            return false;
        }

        return true;
    }

    public function reset()
    {
        if (false === (new Reset())->resetDb()) {
            $this->_errors[] = $this->trans('Reset module error', [], 'Modules.Tpay.Admin');

            return false;
        }

        return true;
    }

    public function disable($force_all = false)
    {
        if (isset($_SERVER['PATH_INFO']) && false !== strpos($_SERVER['PATH_INFO'], 'reset/tpay')) {
            return $this->reset();
        }

        return parent::disable($force_all);
    }

    /**
     * Module hooks
     * @return HookDispatcher
     */
    public function getHookDispatcher(): HookDispatcher
    {
        return $this->hookDispatcher;
    }

    /**
     * Return current context
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * Dispatch hooks
     * @param string $methodName
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $methodName, array $arguments = [])
    {
        return $this->getHookDispatcher()->dispatch(
            $methodName,
            !empty($arguments[0]) ? $arguments[0] : []
        );
    }

    /** Admin config settings check an render form. */
    public function getContent(): void
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('TpayConfiguration'));
    }

    public function checkCurrency($cart): bool
    {
        $currency_order = new \Currency($cart->id_currency);
        if ($currency_order->iso_code == 'PLN') {
            return true;
        }

        return false;
    }

    public function fetch($templatePath, $cache_id = null, $compile_id = null)
    {
        global $smarty;
        $isOldPresta = false;

        if (version_compare(_PS_VERSION_, '1.7.6.0', '<')) {
            $isOldPresta = true;
            if (false === ($smarty->registered_resources['module'] instanceof \Tpay\Util\LegacySmartyResourceModule)) {
                $module_resources = array('theme' => _PS_THEME_DIR_ . 'modules/');

                if (_PS_PARENT_THEME_DIR_) {
                    $module_resources['parent'] = _PS_PARENT_THEME_DIR_ . 'modules/';
                }

                $module_resources['modules'] = _PS_MODULE_DIR_;
                $smarty->registerResource(
                    'module',
                    new \Tpay\Util\LegacySmartyResourceModule(
                        $module_resources,
                        $smarty->registered_resources['module']->isAdmin
                    )
                );
            }
        }

        $smarty->assign('tpay_is_old_presta', $isOldPresta);

        return parent::fetch($templatePath, $cache_id, $compile_id);
    }

    public function hookDisplayProductAdditionalInfo($params): string
    {
        if (Helper::getMultistoreConfigurationValue(
                'TPAY_PEKAO_INSTALLMENTS_ACTIVE'
            ) && Helper::getMultistoreConfigurationValue('TPAY_PEKAO_INSTALLMENTS_PRODUCT_PAGE')) {
            $this->context->smarty->assign(array(
                'installmentText' => $this->trans('Calculate installment!', [], 'Modules.Tpay.Admin'),
                'merchantId' => Helper::getMultistoreConfigurationValue('TPAY_MERCHANT_ID'),
                'minAmount' => Config::PEKAO_INSTALLMENT_MIN,
                'maxAmount' => Config::PEKAO_INSTALLMENT_MAX,
            ));

            return $this->fetch('module:tpay/views/templates/hook/product_installment.tpl');
        }

        return '';
    }

    public function hookDisplayOrderConfirmation($params): string
    {
        if (!$this->active) {
            return '';
        }

        $transactionRepository = $this->getService('tpay.repository.transaction');
        $transaction = $transactionRepository->getTransactionByOrderId($params['order']->id);

        if ($transaction && $transaction['status'] == 'pending' && $this->isBlikPayment($transaction)) {
            $moduleLink = Context::getContext()->link->getModuleLink('tpay', 'chargeBlik', [], true);

            $regulationUrl = "https://tpay.com/user/assets/files_for_download/payment-terms-and-conditions.pdf";
            $clauseUrl = "https://tpay.com/user/assets/files_for_download/information-clause-payer.pdf";

            if ($this->context->language->iso_code == 'pl') {
                $regulationUrl = "https://tpay.com/user/assets/files_for_download/regulamin.pdf";
                $clauseUrl = "https://tpay.com/user/assets/files_for_download/klauzula-informacyjna-platnik.pdf";
            }

            $blikData = [
                'orderId' => $params['order']->id,
                'cartId' => $params['order']->id_cart,
                'blikUrl' => $moduleLink,
                'transactionId' => $transaction['transaction_id'],
                'tpayStatus' => $transaction['status'],
                'assets_path' => $this->getPath(),
                'regulationUrl' => $regulationUrl,
                'clauseUrl' => $clauseUrl,
                'action' => Tools::getValue('action', '')
            ];
            $this->context->smarty->assign($blikData);

            return $this->fetch('module:tpay/views/templates/hook/thank_you_page.tpl');
        } elseif ($transaction && $transaction['transaction_id'] && $this->isTransferOrCardPayment($transaction)) {
            $this->initAPI();
            $result = $this->api->transactions()->getTransactionById($transaction['transaction_id']);

            $thankYouData = [
                'assets_path' => $this->getPath(),
            ];

            $this->context->smarty->assign($thankYouData);

            if (isset($result['status']) && in_array($result['status'], ['correct', 'success'])) {
                return $this->fetch('module:tpay/views/templates/hook/thank_you_page_success.tpl');
            }

            return $this->fetch('module:tpay/views/templates/hook/thank_you_page_error.tpl');
        }

        return '';
    }

    private function isBlikPayment($transaction): bool
    {
        return $transaction['payment_type'] === 'blik' || Tools::getValue('action') == 'renew-payment';
    }

    private function isTransferOrCardPayment($transaction): bool
    {
        return $transaction['payment_type'] === 'transfer' || $transaction['payment_type'] === 'cards';
    }

    /** Module call API. */
    private function initAPI()
    {
        $clientId = Cfg::get('TPAY_CLIENT_ID');
        $secretKey = Cfg::get('TPAY_SECRET_KEY');
        $isProduction = (true !== (bool)Cfg::get('TPAY_SANDBOX'));

        if ($clientId && $secretKey) {
            try {
                Logger::setLogger(new PsrLogger());
                $this->api = new TpayApi(
                    new Tpay\OpenApi\Utilities\Cache(null, new PsrCache()),
                    $clientId,
                    $secretKey,
                    $isProduction,
                    null,
                    $this->buildInfo()
                );
            } catch (\Exception $exception) {
                PrestaShopLogger::addLog($exception->getMessage(), 3);
            }
        }
    }

    /** @throws Exception */
    private function addOrderStates(): bool
    {
        $createState = new FactoryState(
            $this,
            new OrderState()
        );
        $createState->execute();

        return true;
    }

    private function getPrestaVersion(): string
    {
        $dir = realpath(__DIR__ . '/../../config/settings.inc.php');
        if ($dir && file_exists($dir)) {
            include($dir);
        }

        if (defined('_PS_VERSION_')) {
            return _PS_VERSION_;
        }

        return 'n/a';
    }

    private function getAuthTokenCacheKey()
    {
        return sprintf(
            self::AUTH_TOKEN_CACHE_KEY,
            md5(
                join(
                    '|',
                    [Cfg::get('TPAY_CLIENT_ID'), Cfg::get('TPAY_SECRET_KEY'), !Cfg::get('TPAY_SANDBOX')]
                )
            )
        );
    }

    private function getPackageVersion(): string
    {
        $dir = __DIR__ . '/composer.json';
        if (file_exists($dir)) {
            $composerJson = json_decode(file_get_contents($dir), true)['require'] ?? [];

            return $composerJson['tpay-com/tpay-openapi-php'];
        }

        return 'n/a';
    }
}
