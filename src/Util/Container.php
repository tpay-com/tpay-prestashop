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

namespace Tpay\Util;

use Exception;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use Tpay\Entity\TpayRefund;

final class Container
{
    /** @var \Symfony\Component\DependencyInjection\ContainerInterface|null */
    private static $instance;

    /**
     * Get a singleton instance of SymfonyContainer
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public static function getInstance(): \Symfony\Component\DependencyInjection\ContainerInterface
    {
        if (null === self::$instance) {
            if (false === self::isContainerBuiltWithModules()) {
                $kernel = new LegacyAppKernel(_PS_MODE_DEV_ ? 'dev' : 'prod', _PS_MODE_DEV_);
                $kernel->boot();
                self::$instance = $kernel->getContainer();
            } else {
                self::$instance = SymfonyContainer::getInstance();
            }
        }

        return self::$instance;
    }

    private static function isContainerBuiltWithModules()
    {
        $container = SymfonyContainer::getInstance();
        try {
            if (null !== $container) {
                // just try to fetch random Tpay service
                // if exception occures - it means that container is built with only core presta modules (v 1.7.3 and lower)
                $container->get('tpay.service.surcharge');

                // if exception <<class>> was not found in the chain configured namespaces occur, we should instantiate legacy container
                $container->get('doctrine')->getRepository(TpayRefund::class);

                return true;
            }
        } catch (Exception $e) {
        }

        return false;
    }
}
