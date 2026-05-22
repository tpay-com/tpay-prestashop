<?php

use PhpCsFixer\ConfigInterface;
use PrestaShop\CodingStandards\CsFixer\Config;
class TemporaryCsConfig extends Config
{
    public function __construct($name = 'default')
    {
        parent::__construct('');
    }
    public function getRules(): array {
        $parent = parent::getRules();
        $parent['blank_line_after_opening_tag'] = false;
        $parent['linebreak_after_opening_tag'] = false;
        $parent['no_unused_imports'] = true;

        $parent['global_namespace_import'] = ['import_classes' => true];

        return $parent;
    }
}