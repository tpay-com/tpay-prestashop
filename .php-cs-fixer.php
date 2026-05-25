<?php
define('_PS_VERSION_', '8.1.7');
require_once '_dev/TemporaryCsConfig.php';
$config = new TemporaryCsConfig();

/** @var \Symfony\Component\Finder\Finder $finder */
$finder = $config->setUsingCache(false)->getFinder();
$finder->in(__DIR__)->exclude('vendor')->exclude('_dev');

return $config;
