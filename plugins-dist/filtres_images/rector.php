<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\LogicalAnd\LogicalToBooleanRector;
use Rector\Config\RectorConfig;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;

return RectorConfig::configure()
	->withPaths([__DIR__])
	->withRootFiles()
	->withPhpSets(php74: true)
	->withRules([LogicalToBooleanRector::class])
	->withSkip([__DIR__ . '/lang', __DIR__ . '/vendor', __DIR__ . '/tests', NullToStrictStringFuncCallArgRector::class]);
