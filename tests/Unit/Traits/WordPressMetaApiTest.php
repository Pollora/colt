<?php

namespace Pollora\Colt\Tests\Unit\Traits;

use Pollora\Colt\Model\Post;
use Pollora\Colt\Model\User;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Meta writes once WordPress's meta API is loaded. Each test runs in its own
 * process because the fake WordPress functions are global.
 */
#[RunTestsInSeparateProcesses]
class WordPressMetaApiTest extends \Pollora\Colt\Tests\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        require_once __DIR__ . '/../../stubs/wordpress-meta-api.php';

        $GLOBALS['colt_meta_api_calls'] = [];
    }

    public function test_save_meta_goes_through_update_metadata()
    {
        $post = factory(Post::class)->create();

        $this->assertTrue($post->saveMeta('foo', 'a\\b'));

        $this->assertSame([['update', 'post', $post->ID, 'foo', 'a\\\\b']], $GLOBALS['colt_meta_api_calls']);
        $this->assertSame(0, $post->meta()->count());
    }

    public function test_save_meta_with_an_unchanged_value_succeeds()
    {
        $post = factory(Post::class)->create();
        $GLOBALS['colt_meta_api_stored'] = 'bar';

        $this->assertTrue($post->saveMeta('foo', 'bar'));
    }

    public function test_create_meta_goes_through_add_metadata()
    {
        $user = factory(User::class)->create();

        $user->createMeta('foo', 'bar');

        $this->assertSame([['add', 'user', $user->ID, 'foo', 'bar']], $GLOBALS['colt_meta_api_calls']);
    }
}
