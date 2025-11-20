<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/config',
        __DIR__ . '/database',
        __DIR__ . '/tests',
    ])
    ->withSkip([
        __DIR__ . '/vendor',
        __DIR__ . '/workbench',
    ])
    ->withPhpVersion(80400)
    ->withRules([
        ClassPropertyAssignToConstructorPromotionRector::class,
    ])
    ->withImportNames(importShortClasses: false);