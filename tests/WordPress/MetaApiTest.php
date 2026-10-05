<?php

namespace Pollora\Colt\Tests\WordPress;

use Pollora\Colt\Model\Comment;
use Pollora\Colt\Model\Option;
use Pollora\Colt\Model\Post;
use Pollora\Colt\Model\Term;
use Pollora\Colt\Model\User;

/**
 * Colt's meta writes against a real WordPress: they must behave like
 * WordPress's own meta API (cache, sanitization, hooks, slashing).
 */
class MetaApiTest extends TestCase
{
    /**
     * @var array<int, callable>
     */
    private array $cleanups = [];

    public function tearDown(): void
    {
        foreach (array_reverse($this->cleanups) as $cleanup) {
            $cleanup();
        }

        parent::tearDown();
    }

    public function test_save_meta_invalidates_the_object_cache()
    {
        $post = $this->createPost();
        get_post_meta($post->ID, 'colt_key', true);

        $post->saveMeta('colt_key', 'fresh');

        $this->assertSame('fresh', get_post_meta($post->ID, 'colt_key', true));
    }

    public function test_save_meta_keeps_backslashes()
    {
        $post = $this->createPost();

        $post->saveMeta('colt_path', 'C:\\Users\\colt');

        $this->assertSame('C:\\Users\\colt', get_post_meta($post->ID, 'colt_path', true));
    }

    public function test_save_meta_applies_the_registered_sanitize_callback()
    {
        $post = $this->createPost();
        register_post_meta('post', 'colt_count', ['type' => 'integer', 'single' => true, 'sanitize_callback' => 'absint']);
        $this->cleanups[] = fn () => unregister_post_meta('post', 'colt_count');

        $post->saveMeta('colt_count', '-12');

        $this->assertSame('12', get_post_meta($post->ID, 'colt_count', true));
    }

    public function test_save_meta_fires_the_meta_hooks()
    {
        $post = $this->createPost();
        $fired = [];
        $listener = function ($metaId, $objectId, $key) use (&$fired) {
            $fired[] = [current_action(), $key];
        };
        add_action('added_post_meta', $listener, 10, 3);
        add_action('updated_post_meta', $listener, 10, 3);
        $this->cleanups[] = function () use ($listener) {
            remove_action('added_post_meta', $listener, 10);
            remove_action('updated_post_meta', $listener, 10);
        };

        $post->saveMeta('colt_hooked', 'one');
        $post->saveMeta('colt_hooked', 'two');

        $this->assertSame([['added_post_meta', 'colt_hooked'], ['updated_post_meta', 'colt_hooked']], $fired);
    }

    public function test_saving_an_unchanged_value_succeeds()
    {
        $post = $this->createPost();
        $post->saveMeta('colt_same', 'value');

        $this->assertTrue($post->saveMeta('colt_same', 'value'));
    }

    public function test_arrays_are_serialized_by_wordpress_and_read_back_by_colt()
    {
        $post = $this->createPost();

        $post->saveMeta('colt_array', ['size' => 3, 'tags' => ['a', 'b']]);

        $this->assertSame(['size' => 3, 'tags' => ['a', 'b']], get_post_meta($post->ID, 'colt_array', true));
        $this->assertSame(
            ['size' => 3, 'tags' => ['a', 'b']],
            Post::find($post->ID)->meta->firstWhere('meta_key', 'colt_array')->value
        );
    }

    public function test_create_meta_adds_a_row_per_call()
    {
        $post = $this->createPost();

        $first = $post->createMeta('colt_multi', 'a');
        $post->createMeta('colt_multi', 'b');

        $this->assertSame(['a', 'b'], get_post_meta($post->ID, 'colt_multi', false));
        $this->assertSame('a', $first->value);
    }

    public function test_user_meta_goes_through_the_user_meta_api()
    {
        $userId = wp_insert_user(['user_login' => 'colt_' . uniqid(), 'user_pass' => wp_generate_password()]);
        $this->cleanups[] = fn () => wp_delete_user($userId);
        get_user_meta($userId, 'colt_key', true);

        User::find($userId)->saveMeta('colt_key', 'user value');

        $this->assertSame('user value', get_user_meta($userId, 'colt_key', true));
    }

    public function test_term_meta_goes_through_the_term_meta_api()
    {
        $termId = wp_insert_term('Colt ' . uniqid(), 'category')['term_id'];
        $this->cleanups[] = fn () => wp_delete_term($termId, 'category');
        get_term_meta($termId, 'colt_key', true);

        Term::find($termId)->saveMeta('colt_key', 'term value');

        $this->assertSame('term value', get_term_meta($termId, 'colt_key', true));
    }

    public function test_comment_meta_goes_through_the_comment_meta_api()
    {
        $post = $this->createPost();
        $commentId = wp_insert_comment(['comment_post_ID' => $post->ID, 'comment_content' => 'Colt']);
        get_comment_meta($commentId, 'colt_key', true);

        Comment::find($commentId)->saveMeta('colt_key', 'comment value');

        $this->assertSame('comment value', get_comment_meta($commentId, 'colt_key', true));
    }

    public function test_colt_reads_options_written_by_wordpress()
    {
        update_option('colt_option', ['enabled' => true]);
        $this->cleanups[] = fn () => delete_option('colt_option');

        $this->assertSame(['enabled' => true], Option::get('colt_option'));
    }

    private function createPost(): Post
    {
        $postId = wp_insert_post(['post_title' => 'Colt', 'post_status' => 'publish']);
        $this->cleanups[] = fn () => wp_delete_post($postId, true);

        return Post::find($postId);
    }
}
