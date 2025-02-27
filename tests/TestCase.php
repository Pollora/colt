<?php

namespace Pollora\Colt\Tests;

use Pollora\Colt\Laravel\ColtServiceProvider;
use Pollora\Colt\Model\User;
use Pollora\Colt\Tests\Unit\Model\FakePage;
use Pollora\Colt\Tests\Unit\Model\FakePost;
use Pollora\Colt\Tests\Unit\Model\FakeShortcode;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Class TestCase
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom([
            '--database' => 'foo',
            '--realpath' => true,
            '--path' => __DIR__ . '/database/migrations',
        ]);

        $this->loadMigrationsFrom([
            '--database' => 'wp',
            '--realpath' => true,
            '--path' => __DIR__ . '/database/migrations',
        ]);

        $this->withFactories(__DIR__ . '/database/factories');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $this->configureDatabaseConfig($app);
        $this->configureAuthProvider($app);
        $this->configureCustomPostTypes($app);
        $this->configureShortcodes($app);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    private function configureDatabaseConfig($app)
    {
        $app['config']->set('database.connections.wp', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => 'wp_',
        ]);

        $app['config']->set('database.connections.foo', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => 'foo_',
        ]);

        $app['config']->set('database.default', 'wp');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    private function configureAuthProvider($app)
    {
        $app['config']->set('auth.providers.users', [
            'driver' => 'colt',
            'model' => User::class,
        ]);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    private function configureCustomPostTypes($app)
    {
        $app['config']->set('colt.post_types', [
            'fake_post' => FakePost::class,
            'fake_page' => FakePage::class,
        ]);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    public function configureShortcodes($app)
    {
        $app['config']->set('colt.shortcodes', [
            'fake' => FakeShortcode::class,
        ]);
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

    /**
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string $driver
     * @return void
     */
    public function be(Authenticatable $user, $driver = null)
    {
        // TODO: Implement be() method.
    }
}
