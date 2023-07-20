<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src'
    ]);

    // register a single rule
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_80
    ]);

    $rectorConfig->skip([
        __DIR__ . '/src/base/GenericSearchInterface.php',
        __DIR__ . '/src/base/JwtIdentityInterface.php',
        __DIR__ . '/src/ngrest/base/ActiveWindowInterface.php',
        __DIR__ . '/src/ngrest/base/NgRestRelationInterface.php',
        __DIR__ . '/src/ngrest/base/Plugin.php',
    ]);
};
