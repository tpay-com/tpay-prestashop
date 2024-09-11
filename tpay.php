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
use Tpay\Install\Uninstall;
use Tpay\OpenApi\Utilities\Logger;
use Tpay\States\FactoryState;
use Tpay\Util\Container;
use Tpay\Util\Helper;
use Tpay\Util\PsrLogger;
use tpaySDK\Api\TpayApi;

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
        $this->version = '1.9.5';
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

        $this->displayName = $this->l('Tpay');
        $this->description = $this->l('Accepting online payments');
        $this->confirmUninstall = $this->l('Delete this module?');
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
            $this->_errors[] = $this->l(
                sprintf(
                    'Your PHP version is too old, please upgrade to a newer version. Your version is %s,'
                    . ' library requires %s',
                    phpversion(),
                    '7.1'
                )
            );
        }

        if (
            !parent::install() || false === (new Install(
                $this,
                new InstallQueryHandler()
            ))->install()
        ) {
            $this->_errors[] = $this->l('Installation error');
        }

        if (!$this->addOrderStates()) {
            $this->_errors[] = $this->l('Error adding order statuses');
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
        $this->registerHook('displayProductPriceBlock');

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
            $this->_errors[] = $this->l('Installation error');
        }

        if (!empty($this->_errors)) {
            return false;
        }

        return true;
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

    public function initLanguages(): void
    {
        $this->l('Payment card');
        $this->l('Buy now, pay later');
        $this->l('Google Pay');
        $this->l('Apple Pay');
        $this->l('Pay by online transfer with Tpay');
        $this->l('Payment error');
        $this->l('The code you entered is invalid or has expired.');
        $this->l('Online payment fee');
        $this->l('The blik code has expired');
        $this->l('Online payment fee: ');
        $this->l('Unable to process refund - amount is greater than allowed %s');
        $this->l('Unable to process refund - invalid amount');
        $this->l('Refund successful. Return option is being processed please wait');
        $this->l('Refund error.
                                    Check that the refund amount is correct and does not exceed the value of the order');
        $this->l('Accept blik code on mobile app');
        $this->l('Transaction was not accepted in the bank\'s application');
        $this->l('Transaction rejected by payer');
        $this->l('Blik was not accepted in the application');

        $this->l('invalid BLIK code or alias data format');
        $this->l('error connecting BLIK system');
        $this->l('invalid BLIK six-digit code');
        $this->l('can not pay with BLIK code or alias for non BLIK transaction');
        $this->l('incorrect transaction status - should be pending');
        $this->l('BLIK POS is not available');
        $this->l('given alias is non-unique');
        $this->l('given alias has not been registered or has been deregistered');
        $this->l('given alias section is incorrect');
        $this->l('BLIK other error');
        $this->l('BLIK payment declined by user');
        $this->l('BLIK system general error');
        $this->l('BLIK insufficient funds / user authorization error');
        $this->l('BLIK user or system timeout');
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
                    ));
            }
        }

        $smarty->assign('tpay_is_old_presta', $isOldPresta);

        return parent::fetch($templatePath, $cache_id, $compile_id);
    }

    public function hookDisplayProductAdditionalInfo($params): string
    {
        if (Helper::getMultistoreConfigurationValue('TPAY_PEKAO_INSTALLMENTS_ACTIVE') && Helper::getMultistoreConfigurationValue('TPAY_PEKAO_INSTALLMENTS_PRODUCT_PAGE')) {
            $this->context->smarty->assign(array(
                'installmentText' => $this->l('Calculate installment!'),
                'merchantId' => Helper::getMultistoreConfigurationValue('TPAY_MERCHANT_ID'),
                'minAmount' => Config::PEKAO_INSTALLMENT_MIN,
                'maxAmount' => Config::PEKAO_INSTALLMENT_MAX,
            ));

            return $this->fetch('module:tpay/views/templates/hook/product_installment.tpl');
        }

        return '';
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
                $this->api = new TpayApi($clientId, $secretKey, $isProduction, 'read', null, $this->buildInfo());
                $token = \Tpay\Util\Cache::get($this->getAuthTokenCacheKey());

                if ($token) {
                    $this->api->setCustomToken(unserialize($token));
                }

                if (!$token) {
                    \Tpay\Util\Cache::set($this->getAuthTokenCacheKey(), serialize($this->api->getToken()));
                }
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
        if (file_exists($dir)) {
            include($dir);

            if (defined('_PS_VERSION_')) {
                return _PS_VERSION_;
            }
        }

        return 'n/a';
    }

    private function getAuthTokenCacheKey()
    {
        return sprintf(
            self::AUTH_TOKEN_CACHE_KEY,
            md5(join(
                '|',
                [Cfg::get('TPAY_CLIENT_ID'), Cfg::get('TPAY_SECRET_KEY'), !Cfg::get('TPAY_SANDBOX')]
            ))
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
