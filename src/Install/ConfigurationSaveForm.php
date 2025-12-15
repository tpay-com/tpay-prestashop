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

namespace Tpay\Install;

use Context;
use PrestaShopLogger;
use Shop;
use Tools;
use Tpay\Adapter\ConfigurationAdapter;
use Tpay\Util\Helper;

class ConfigurationSaveForm
{
    /** @var ConfigurationAdapter */
    private $configuration;

    private $shopGroupsList = [];

    public function __construct(ConfigurationAdapter $configuration)
    {
        $this->configuration = $configuration;
    }

    public function execute($save = false): bool
    {
        $res = true;
        $shops = Shop::getContextListShopID();
        $shopId = (int) Context::getContext()->shop->id;
        $this->shopGroupsList[] = Shop::getGroupFromShop($shopId);

        if ($save) {
            $defaultValue = false;
            $fields = Helper::getFields();
        } else {
            $defaultValue = true;
            $fields = Helper::getFieldsDefaultValues();
        }

        if (Shop::CONTEXT_SHOP == Shop::getContext()) {
            if (!$this->saveConfigurationShops($shops, $fields, $defaultValue)) {
                PrestaShopLogger::addLog('Error configure all shop context', 3);
                $res = false;
            }
        } elseif (Shop::CONTEXT_GROUP == Shop::getContext()) {
            if (!$this->saveConfigurationGroups($this->shopGroupsList, $fields, $defaultValue)) {
                PrestaShopLogger::addLog('Error configure shop group context', 3);
                $res = false;
            }
        } else {
            if (!$this->saveConfigurationGlobal($fields, $defaultValue)) {
                PrestaShopLogger::addLog('Error configure global context', 3);
                $res = false;
            }
        }

        return $res;
    }

    public function saveConfigurationShops($shops, $fields, $default = false): bool
    {
        $res = true;
        foreach ($shops as $shop_id) {
            $shop_group_id = (int) Shop::getGroupFromShop($shop_id, true);

            if (!in_array($shop_group_id, $this->shopGroupsList)) {
                $this->shopGroupsList[] = $shop_group_id;
            }

            foreach ($fields as $key => $value) {
                if ($default) {
                    $res &= $this->configuration->updateValue(
                        $key,
                        $value,
                        false,
                        $shop_group_id,
                        $shop_id
                    );
                } else {
                    if (strpos($value, '[]')) {
                        $value = substr($value, 0, -2);
                        $getValue = Tools::getValue($value, '[]');
                    } else {
                        $getValue = Tools::getValue(
                            $value,
                            $this->configuration->get(
                                $value,
                                null,
                                $shop_group_id,
                                $shop_id
                            )
                        );
                    }

                    $res &= $this->configuration->updateValue(
                        $value,
                        $getValue,
                        false,
                        $shop_group_id,
                        $shop_id
                    );
                }
            }
        }

        return (bool) $res;
    }

    public function saveConfigurationGroups($shop_groups_list, $fields, $default = false): bool
    {
        $res = true;
        if (count($shop_groups_list)) {
            foreach ($shop_groups_list as $shop_group_id) {
                foreach ($fields as $key => $value) {
                    if ($default) {
                        $res &= $this->configuration->updateValue(
                            $key,
                            $value,
                            false,
                            $shop_group_id
                        );
                    } else {
                        $getValue = Tools::getValue(
                            $value,
                            $this->configuration->get(
                                $value,
                                null,
                                $shop_group_id
                            )
                        );

                        $res &= $this->configuration->updateValue(
                            $value,
                            $getValue,
                            false,
                            $shop_group_id
                        );
                    }
                }
            }
        }

        return (bool) $res;
    }

    public function saveConfigurationGlobal($fields, $default = false): bool
    {
        $res = true;
        foreach ($fields as $key => $value) {
            if ($default) {
                $res &= $this->configuration->updateValue(
                    $key,
                    $value
                );
            } else {
                $getValue = Tools::getValue($value, $this->configuration->get($value));

                $res &= $this->configuration->updateValue(
                    $value,
                    $getValue
                );
            }
        }

        return (bool) $res;
    }
}
