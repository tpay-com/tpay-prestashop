<?php

namespace Tpay\Util;

use SmartyResourceModule;
use Tools;

class LegacySmartyResourceModule extends SmartyResourceModule
{
    public function __construct(array $paths, $isAdmin = false)
    {
        $this->paths = $paths;
        $this->isAdmin = $isAdmin;
    }

    protected function fetch($name, &$source, &$mtime)
    {
        foreach ($this->paths as $path) {
            if (Tools::file_exists_cache($file = $path.$name)) {
                if (_PS_MODE_DEV_) {
                    $source = implode(
                        '',
                        [
                        '<!-- begin '.$file.' -->',
                        file_get_contents($file),
                        '<!-- end '.$file.' -->',
                        ]
                    );
                } else {
                    $source = file_get_contents($file);
                }
                $mtime = filemtime($file);

                return;
            }
        }
    }
}
