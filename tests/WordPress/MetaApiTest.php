<?php

use Pollora\Colt\Model\Comment;
use Pollora\Colt\Model\Option;
use Pollora\Colt\Model\Post;
use Pollora\Colt\Model\Term;
use Pollora\Colt\Model\User;

/*
 * Colt's meta writes against a real WordPress: they must behave like
 * WordPress's own meta API (cache, sanitization, hooks, slashing).
 */

describe('saveMeta()', function (): void {
    it('invalidates the object cache', function (): void {
        $post = $this->createPost();
        get_post_meta($post->ID, 'colt_key', true);

        $post->saveMeta('colt_key', 'fresh');

        expect(get_post_meta($post->ID, 'colt_key', true))->toBe('fresh');
    });

    it('keeps backslashes', function (): void {
        $post = $this->createPost();

        $post->saveMeta('colt_path', 'C:\\Users\\colt');

        expect(get_post_meta($post->ID, 'colt_path', true))->toBe('C:\\Users\\colt');
    });

    it('applies the registered sanitize callback', function (): void {
        $post = $this->createPost();
        register_post_meta('post', 'colt_count', ['type' => 'integer', 'single' => true, 'sanitize_callback' => 'absint']);
        $this->cleanup(fn () => unregister_post_meta('post', 'colt_count'));

        $post->saveMeta('colt_count', '-12');

        expect(get_post_meta($post->ID, 'colt_count', true))->toBe('12');
    });

    it('fires the meta hooks', function (): void {
        $post = $this->createPost();
        $fired = [];
        $listener = function ($metaId, $objectId, $key) use (&$fired): void {
            $fired[] = [current_action(), $key];
        };
        add_action('added_post_meta', $listener, 10, 3);
        add_action('updated_post_meta', $listener, 10, 3);
        $this->cleanup(function () use ($listener): void {
            remove_action('added_post_meta', $listener, 10);
            remove_action('updated_post_meta', $listener, 10);
        });

        $post->saveMeta('colt_hooked', 'one');
        $post->saveMeta('colt_hooked', 'two');

        expect($fired)->toBe([['added_post_meta', 'colt_hooked'], ['updated_post_meta', 'colt_hooked']]);
    });

    it('succeeds when the value is unchanged', function (): void {
        $post = $this->createPost();
        $post->saveMeta('colt_same', 'value');

        expect($post->saveMeta('colt_same', 'value'))->toBeTrue();
    });

    it('lets WordPress serialize arrays, which Colt reads back', function (): void {
        $post = $this->createPost();

        $post->saveMeta('colt_array', ['size' => 3, 'tags' => ['a', 'b']]);

        expect(get_post_meta($post->ID, 'colt_array', true))->toBe(['size' => 3, 'tags' => ['a', 'b']])
            ->and(Post::find($post->ID)->meta->firstWhere('meta_key', 'colt_array')->value)->toBe(['size' => 3, 'tags' => ['a', 'b']]);
    });
});

describe('createMeta()', function (): void {
    it('adds a row per call', function (): void {
        $post = $this->createPost();

        $first = $post->createMeta('colt_multi', 'a');
        $post->createMeta('colt_multi', 'b');

        expect(get_post_meta($post->ID, 'colt_multi', false))->toBe(['a', 'b'])
            ->and($first->value)->toBe('a');
    });
});

describe('meta types', function (): void {
    it('writes user meta through the user meta API', function (): void {
        $userId = wp_insert_user(['user_login' => 'colt_' . uniqid(), 'user_pass' => wp_generate_password()]);
        $this->cleanup(fn () => wp_delete_user($userId));
        get_user_meta($userId, 'colt_key', true);

        User::find($userId)->saveMeta('colt_key', 'user value');

        expect(get_user_meta($userId, 'colt_key', true))->toBe('user value');
    });

    it('writes term meta through the term meta API', function (): void {
        $termId = wp_insert_term('Colt ' . uniqid(), 'category')['term_id'];
        $this->cleanup(fn () => wp_delete_term($termId, 'category'));
        get_term_meta($termId, 'colt_key', true);

        Term::find($termId)->saveMeta('colt_key', 'term value');

        expect(get_term_meta($termId, 'colt_key', true))->toBe('term value');
    });

    it('writes comment meta through the comment meta API', function (): void {
        $post = $this->createPost();
        $commentId = wp_insert_comment(['comment_post_ID' => $post->ID, 'comment_content' => 'Colt']);
        get_comment_meta($commentId, 'colt_key', true);

        Comment::find($commentId)->saveMeta('colt_key', 'comment value');

        expect(get_comment_meta($commentId, 'colt_key', true))->toBe('comment value');
    });
});

it('reads options written by WordPress', function (): void {
    update_option('colt_option', ['enabled' => true]);
    $this->cleanup(fn () => delete_option('colt_option'));

    expect(Option::get('colt_option'))->toBe(['enabled' => true]);
});
