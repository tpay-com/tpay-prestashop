<?php

namespace Tpay\Util;

$autoload = _PS_ROOT_DIR_ . '/app/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}
if (!class_exists('\AppKernel')) {
    require_once _PS_ROOT_DIR_ . '/app/AppKernel.php';
}

class LegacyAppKernel extends \AppKernel
{
    public function getRootDir()
    {
        return _PS_ROOT_DIR_ . '/app';
    }

    public function getCacheDir()
    {
        if (defined(_PS_CACHE_DIR_) && _PS_CACHE_DIR_) {
            return _PS_CACHE_DIR_;
        }

        return _PS_ROOT_DIR_ . '/app/cache/' . $this->getEnvironment();
    }

    public function getLogDir()
    {
        return _PS_ROOT_DIR_ . '/app/logs';
    }

    public function registerContainerConfiguration(\Symfony\Component\Config\Loader\LoaderInterface $loader)
    {
        parent::registerContainerConfiguration($loader);
        $loader->load(__DIR__ . '/../../config/common.yml');
        $loader->load(__DIR__ . '/../../config/config.yml');
    }

    protected function getContainerClass()
    {
        return 'legacy' . ucfirst($this->name) . ucfirst($this->environment) . ($this->debug ? 'Debug' : '') . 'ProjectContainer';
    }

}
