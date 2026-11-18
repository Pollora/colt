<?php

declare(strict_types=1);

namespace Pollora\Colt\Tests\Support;

use Illuminate\Support\Facades\DB;

/**
 * Builds a WordPress-shaped data set of a given size with bulk inserts (no model, no event).
 *
 * Every row that a lazy relation would load one by one is distinct per post
 * (own author, own thumbnail, own terms, own comments), so a hidden N+1
 * cannot be hidden by a shared row.
 */
final class ColtFixtures
{
    private const TABLES = [
        'posts', 'postmeta', 'users', 'usermeta', 'terms', 'term_taxonomy',
        'term_relationships', 'termmeta', 'comments', 'commentmeta',
    ];

    /** Empties every table, so two sizes can be built one after the other in the same test. */
    public static function reset(): void
    {
        foreach (self::TABLES as $table) {
            DB::table($table)->delete();
        }
    }

    /**
     * Creates `$count` published posts, each with: its own author, 3 meta, a featured
     * image (attachment + its alt meta), a category, a tag, 2 comments and 1 child.
     *
     * @return list<int> the ids of the posts, in creation order
     */
    public static function posts(int $count): array
    {
        self::reset();

        $now = '2026-01-01 00:00:00';
        $postRow = fn (array $override): array => $override + [
            'post_author' => 1, 'post_date' => $now, 'post_date_gmt' => $now,
            'post_content' => 'content', 'post_title' => 'title', 'post_excerpt' => 'excerpt',
            'post_status' => 'publish', 'comment_status' => 'closed', 'ping_status' => 'closed',
            'post_password' => '', 'post_name' => 'slug', 'to_ping' => '', 'pinged' => '',
            'post_modified' => $now, 'post_modified_gmt' => $now, 'post_content_filtered' => '',
            'post_parent' => 0, 'guid' => 'http://example.com/', 'menu_order' => 0,
            'post_type' => 'post', 'post_mime_type' => '', 'comment_count' => 0,
        ];

        $ids = [];
        for ($i = 1; $i <= $count; $i++) {
            DB::table('users')->insert([
                'user_login' => "user$i", 'user_pass' => '', 'user_nicename' => "user$i",
                'user_email' => "user$i@example.com", 'user_url' => '', 'user_registered' => $now,
                'user_activation_key' => '', 'user_status' => '0', 'display_name' => "User $i",
            ]);
            $userId = (int) DB::getPdo()->lastInsertId();
            DB::table('usermeta')->insert(['user_id' => $userId, 'meta_key' => 'first_name', 'meta_value' => "First $i"]);

            $attachmentId = DB::table('posts')->insertGetId($postRow([
                'post_type' => 'attachment', 'post_mime_type' => 'image/jpeg', 'post_title' => "image $i",
                'guid' => "http://example.com/image-$i.jpg",
            ]), 'ID');
            DB::table('postmeta')->insert(['post_id' => $attachmentId, 'meta_key' => '_wp_attachment_image_alt', 'meta_value' => "alt $i"]);

            $postId = DB::table('posts')->insertGetId($postRow([
                'post_author' => $userId, 'post_title' => "post $i", 'post_name' => "post-$i",
            ]), 'ID');
            $ids[] = $postId;

            DB::table('postmeta')->insert([
                ['post_id' => $postId, 'meta_key' => '_thumbnail_id', 'meta_value' => (string) $attachmentId],
                ['post_id' => $postId, 'meta_key' => 'subtitle', 'meta_value' => "subtitle $i"],
                ['post_id' => $postId, 'meta_key' => 'price', 'meta_value' => (string) $i],
            ]);

            foreach (['category', 'post_tag'] as $taxonomy) {
                $termId = DB::table('terms')->insertGetId(['name' => "$taxonomy $i", 'slug' => "$taxonomy-$i", 'term_group' => 0], 'term_id');
                DB::table('termmeta')->insert(['term_id' => $termId, 'meta_key' => 'color', 'meta_value' => 'red']);
                $taxonomyId = DB::table('term_taxonomy')->insertGetId([
                    'term_id' => $termId, 'taxonomy' => $taxonomy, 'description' => '', 'parent' => 0, 'count' => 1,
                ], 'term_taxonomy_id');
                DB::table('term_relationships')->insert(['object_id' => $postId, 'term_taxonomy_id' => $taxonomyId, 'term_order' => 0]);
            }

            for ($c = 1; $c <= 2; $c++) {
                $commentId = DB::table('comments')->insertGetId([
                    'comment_post_ID' => $postId, 'comment_author' => 'a', 'comment_author_email' => '',
                    'comment_author_url' => '', 'comment_author_IP' => '', 'comment_date' => $now,
                    'comment_date_gmt' => $now, 'comment_content' => 'c', 'comment_karma' => 0,
                    'comment_approved' => '1', 'comment_agent' => '', 'comment_type' => '',
                    'comment_parent' => 0, 'user_id' => 0,
                ], 'comment_ID');
                DB::table('commentmeta')->insert(['comment_id' => $commentId, 'meta_key' => 'rating', 'meta_value' => (string) $c]);
            }

            DB::table('posts')->insert($postRow(['post_parent' => $postId, 'post_title' => "child $i", 'post_type' => 'page']));
        }

        return $ids;
    }

    /**
     * Creates a nav menu with `$items` items; each item points to its own post
     * (object type "post") and to the previous item as its parent.
     *
     * @return int term_taxonomy_id of the menu
     */
    public static function menu(int $items): int
    {
        self::reset();

        $now = '2026-01-01 00:00:00';
        $row = fn (array $override): array => $override + [
            'post_author' => 1, 'post_date' => $now, 'post_date_gmt' => $now,
            'post_content' => '', 'post_title' => 't', 'post_excerpt' => '', 'post_status' => 'publish',
            'comment_status' => 'closed', 'ping_status' => 'closed', 'post_password' => '',
            'post_name' => 's', 'to_ping' => '', 'pinged' => '', 'post_modified' => $now,
            'post_modified_gmt' => $now, 'post_content_filtered' => '', 'post_parent' => 0,
            'guid' => '', 'menu_order' => 0, 'post_type' => 'post', 'post_mime_type' => '', 'comment_count' => 0,
        ];

        $termId = DB::table('terms')->insertGetId(['name' => 'Main', 'slug' => 'main', 'term_group' => 0], 'term_id');
        $menuId = DB::table('term_taxonomy')->insertGetId([
            'term_id' => $termId, 'taxonomy' => 'nav_menu', 'description' => '', 'parent' => 0, 'count' => $items,
        ], 'term_taxonomy_id');

        $previous = 0;
        for ($i = 1; $i <= $items; $i++) {
            $targetId = DB::table('posts')->insertGetId($row(['post_title' => "target $i"]), 'ID');
            $itemId = DB::table('posts')->insertGetId($row(['post_type' => 'nav_menu_item', 'menu_order' => $i]), 'ID');
            DB::table('postmeta')->insert([
                ['post_id' => $itemId, 'meta_key' => '_menu_item_object', 'meta_value' => 'post'],
                ['post_id' => $itemId, 'meta_key' => '_menu_item_object_id', 'meta_value' => (string) $targetId],
                ['post_id' => $itemId, 'meta_key' => '_menu_item_menu_item_parent', 'meta_value' => (string) $previous],
            ]);
            DB::table('term_relationships')->insert(['object_id' => $itemId, 'term_taxonomy_id' => $menuId, 'term_order' => 0]);
            $previous = $targetId;
        }

        return $menuId;
    }
}
