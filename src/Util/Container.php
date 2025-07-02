<?php

namespace Tpay\Util;

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use Tpay\Entity\TpayRefund;

final class Container
{
    /** @var self */
    private static $instance = null;

    /**
     * Get a singleton instance of SymfonyContainer
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface;
     */
    public static function getInstance()
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
                //just try to fetch random Tpay service
                //if exception occures - it means that container is built with only core presta modules (v 1.7.3 and lower)
                $container->get('tpay.service.surcharge');

                //if exception <<class>> was not found in the chain configured namespaces occur, we should instantiate legacy container
                $container->get('doctrine')->getRepository(TpayRefund::class);
                return true;
            }
        } catch (\Exception $e) {
        }

        return false;
    }
}
