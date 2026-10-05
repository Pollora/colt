<?php

namespace Pollora\Colt\Tests\WordPress;

use Pollora\Colt\Laravel\ColtServiceProvider;
use Pollora\Colt\Model\Post;

/**
 * Base of the integration suite: Colt and WordPress share the database
 * WordPress was installed on, read from its wp-config.php.
 */
abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * @var array<int, callable>
     */
    private array $cleanups = [];

    protected function tearDown(): void
    {
        foreach (array_reverse($this->cleanups) as $cleanup) {
            $cleanup();
        }

        $this->cleanups = [];

        parent::tearDown();
    }

    /**
     * Runs after the test, whether it passed or not.
     */
    protected function cleanup(callable $cleanup): void
    {
        $this->cleanups[] = $cleanup;
    }

    /**
     * Inserts a published post with WordPress and returns it as a Colt model.
     */
    protected function createPost(): Post
    {
        $postId = wp_insert_post(['post_title' => 'Colt', 'post_status' => 'publish']);
        $this->cleanup(fn () => wp_delete_post($postId, true));

        return Post::find($postId);
    }

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
