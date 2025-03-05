<?php

namespace Tpay\Util;
class LegacySmartyResourceModule extends \SmartyResourceModule
{
    public function __construct(array $paths, $isAdmin = false)
    {
        $this->paths = $paths;
        $this->isAdmin = $isAdmin;
    }


    protected function fetch($name, &$source, &$mtime)
    {
        foreach ($this->paths as $path) {
            if (\Tools::file_exists_cache($file = $path . $name)) {
                if (_PS_MODE_DEV_) {
                    $source = implode('', array(
                        '<!-- begin ' . $file . ' -->',
                        file_get_contents($file),
                        '<!-- end ' . $file . ' -->',
                    ));
                } else {
                    $source = file_get_contents($file);
                }
                $mtime = filemtime($file);

                return;
            }
        }
    }
}
