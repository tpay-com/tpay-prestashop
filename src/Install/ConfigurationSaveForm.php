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
