<?php

namespace Pollora\Colt\Tests\WordPress;

use Pollora\Colt\Laravel\ColtServiceProvider;

/**
 * Base of the integration suite: Colt and WordPress share the database
 * WordPress was installed on, read from its wp-config.php.
 */
abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        [$host, $port] = array_pad(explode(':', DB_HOST, 2), 2, '3306');

        $app['config']->set('database.connections.wp', [
            'driver' => 'mysql',
            'host' => $host,
            'port' => $port,
            'database' => DB_NAME,
            'username' => DB_USER,
            'password' => DB_PASSWORD,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => $GLOBALS['wpdb']->prefix,
        ]);

        $app['config']->set('database.default', 'wp');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            ColtServiceProvider::class,
        ];
    }
}
