<?php

require __DIR__.'/vendor/tpay-com/coding-standards/bootstrap.php';

$config = Tpay\CodingStandards\PhpCsFixerConfigFactory::createWithLegacyRules();

return $config
    ->setRules(
        [
            'multiline_string_to_heredoc' => false,
            'single_line_comment_style' => ['comment_types' => ['hash']],
        ] + $config->getRules()
    )
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->ignoreDotFiles(false)
            ->in(__DIR__)
            ->exclude('translations')
            ->exclude('prestashop')
            ->exclude('vendor')
    );
