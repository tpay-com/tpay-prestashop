<?php

/**MIT License

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

@author Krajowy Integrator Płatności S.A.*/

namespace Tpay\Util;

$autoload = _PS_ROOT_DIR_ . '/app/autoload.php';
if (file_exists($autoload)) {
    // @phpstan-ignore-next-line
    include_once $autoload;
}
if (!class_exists('\AppKernel')) {
    include_once _PS_ROOT_DIR_ . '/app/AppKernel.php';
}

use AppKernel;

class LegacyAppKernel extends AppKernel
{
    protected $name = 'tpay';

    public function getRootDir()
    {
        return _PS_ROOT_DIR_ . '/app';
    }

    public function getCacheDir(): string
    {
        // @phpstan-ignore-next-line
        return _PS_CACHE_DIR_ ?: _PS_ROOT_DIR_ . '/app/cache/' . $this->getEnvironment();
    }

    public function getLogDir(): string
    {
        return _PS_ROOT_DIR_ . '/app/logs';
    }

    public function registerContainerConfiguration(\Symfony\Component\Config\Loader\LoaderInterface $loader)
    {
        parent::registerContainerConfiguration($loader);
        $loader->load(__DIR__ . '/../../config/common.yml');
        $loader->load(__DIR__ . '/../../config/config.yml');
    }

    public function getAppId(): string
    {
        return 'front';
    }

    protected function getContainerClass(): string
    {
        return 'legacy' . ucfirst($this->name) . ucfirst($this->environment) . ($this->debug ? 'Debug' : '') . 'ProjectContainer';
    }
}
