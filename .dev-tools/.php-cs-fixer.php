<?php

require __DIR__.'/vendor/tpay-com/coding-standards/bootstrap.php';

return Tpay\CodingStandards\PhpCsFixerConfigFactory::createWithLegacyRules()
    ->setFinder(
        PhpCsFixer\Finder::create()->ignoreDotFiles(false)->in(__DIR__)->exclude('translations')
            ->exclude('prestashop')
            ->exclude('vendor')
    );
