<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

/*
 * The harness's preset covers WordPress's classes and functions.
 * The suite runs on Testbench, which installs laravel/framework: that package
 * `replace`s every illuminate/* component, so the analyser reads the Illuminate
 * classes as coming from laravel/framework. The components the package really
 * needs are declared in composer.json.
 */
return (require __DIR__.'/vendor/pollora/harness/presets/composer-dependency-analyser.php')
    ->ignoreErrorsOnPackage('laravel/framework', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackages(['illuminate/contracts', 'illuminate/database', 'illuminate/support'], [ErrorType::UNUSED_DEPENDENCY]);
