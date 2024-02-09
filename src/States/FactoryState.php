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

use Configuration as Cfg;
use OrderState;

class FactoryState
{
    /**
     * @var \Tpay
     */
    private $module;
    /**
     * @var OrderState
     */
    private $orderState;

    public function __construct(
        \Tpay $module,
        OrderState $orderState
    ) {
        $this->module = $module;
        $this->orderState = $orderState;
    }


    public function getStatusTypes(): array
    {
        return [
            'pending',
            'confirmed',
            'error'
        ];
    }

    /**
     * Get all states by configuration
     * @return array
     */
    public function getAllStatuses(): array
    {
        if (Cfg::get('TPAY_PENDING')) {
            $states['pending'] = Cfg::get('TPAY_PENDING');
        }
        if (Cfg::get('TPAY_CONFIRMED')) {
            $states['confirmed'] = Cfg::get('TPAY_CONFIRMED');
        }
        if (Cfg::get('TPAY_ERROR')) {
            $states['error'] = Cfg::get('TPAY_ERROR');
        }
        return $states ?? [];
    }

    /**
     * Create states
     *
     * @throws \Exception
     */
    public function execute(): void
    {
        if (!empty($this->getStatusTypes())) {
            foreach ($this->getStatusTypes() as $type) {

                $status = $this->statusBuilder(
                    $type,
                    new OrderState()
                );

                if (!$this->statusAvailable($status)) {
                    $status->create();
                }

                $this->assignConfiguration($status, $type);

            }
        }
    }


    public function assignConfiguration($state, $name)
    {
        $nameUpper = 'TPAY_' . \Tools::strtoupper($name);


        if (\Shop::isFeatureActive()) {
            $shops = \Shop::getCompleteListOfShopsID();

            foreach ($shops as $shop) {
                \Configuration::updateValue($nameUpper, $state->id, false, null, (int)$shop);
            }
        }

        \Configuration::updateGlobalValue($nameUpper, $state->id);
    }


    /**
     * @throws \Exception
     */
    public function statusBuilder(string $type, OrderState $orderState, $moduleName = 'tpay')
    {
        switch ($type) {
            case 'pending':
                $state = new PendingState($orderState, $moduleName);
                break;
            case 'error':
                $state = new ErrorState($orderState, $moduleName);
                break;
            case 'confirmed':
                $state = new ConfirmedState($orderState, $moduleName);
                break;
            default:
                throw new \Exception("Incorrect type when creating status for orders");
        }
        return $state;
    }



    /**
     * Checks if the status is available
     * @return bool
     */
    public function statusAvailable($names): bool
    {
        if (!empty($names->stateLanguage)) {
            foreach (\Language::getLanguages() as $lang) {

                return $this->existsLocalizedNameInDatabase(
                    $names->stateLanguage[$lang['iso_code']] ?? $names->stateLanguage['en'],
                    (int)$lang['id_lang'],
                    \Tools::getIsset('id_order_state') ? (int)\Tools::getValue('id_order_state') : null
                );
            }
        }

        return false;
    }


    private function existsLocalizedNameInDatabase($name, $idLang, $excludeIdOrderState): bool
    {
        if(method_exists(OrderState::class, 'existsLocalizedNameInDatabase')){
            return OrderState::existsLocalizedNameInDatabase($name, $idLang, $excludeIdOrderState);
        }

        return (bool) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT COUNT(*) AS count' .
            ' FROM ' . _DB_PREFIX_ . 'order_state_lang osl' .
            ' INNER JOIN ' . _DB_PREFIX_ . 'order_state os ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = ' . $idLang . ')' .
            ' WHERE osl.id_lang = ' . $idLang .
            ' AND osl.name =  \'' . pSQL($name) . '\'' .
            ' AND os.deleted = 0' .
            ($excludeIdOrderState ? ' AND osl.id_order_state != ' . $excludeIdOrderState : '')
        );
    }
}
