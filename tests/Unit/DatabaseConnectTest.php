<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager;
use Pollora\Colt\Database;

it('connects with the given parameters merged over the WordPress defaults', function () {
    $capsule = Database::connect([
        'driver' => 'sqlite',
        'database' => ':memory:',
    ]);

    $config = $capsule->getConnection()->getConfig();

    expect($capsule)->toBeInstanceOf(Manager::class)
        ->and($config['driver'])->toBe('sqlite')
        ->and($config['prefix'])->toBe('wp_')
        ->and($config['charset'])->toBe('utf8');
});

it('lets the caller override the default table prefix', function () {
    $capsule = Database::connect([
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => 'custom_',
    ]);

    expect($capsule->getConnection()->getTablePrefix())->toBe('custom_');
});
