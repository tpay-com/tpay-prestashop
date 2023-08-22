<?php

namespace Tpay\Util;

use Symfony\Component\HttpKernel\KernelInterface;

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
                $kernel = new \Tpay\Util\LegacyAppKernel(_PS_MODE_DEV_ ? 'dev' : 'prod', _PS_MODE_DEV_);
                $kernel->boot();
            } else {
                global $kernel;
            }

            if ($kernel instanceof KernelInterface) {
                self::$instance = $kernel->getContainer();
            }
        }

        return self::$instance;
    }

    private static function isContainerBuiltWithModules()
    {
        global $kernel;
        try {
            if (null !== $kernel) {
                //just try to fetch random Tpay service
                //if exception occures - it means that container is built with only core presta modules (v 1.7.3 and lower)
                $kernel->getContainer()->get('tpay.service.surcharge');
                return true;
            }
        } catch (\Exception $e) {
        }

        return false;
    }
}
