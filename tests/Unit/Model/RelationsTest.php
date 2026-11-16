<?php

declare(strict_types=1);

use Pollora\Colt\Concerns\MetaFields;
use Pollora\Colt\Laravel\Auth\AuthUserProvider;
use Pollora\Colt\Model;
use Pollora\Colt\Model\Attachment;
use Pollora\Colt\Model\Builder\TaxonomyBuilder;
use Pollora\Colt\Model\Comment;
use Pollora\Colt\Model\CustomLink;
use Pollora\Colt\Model\MenuItem;
use Pollora\Colt\Model\Meta\ThumbnailMeta;
use Pollora\Colt\Model\Option;
use Pollora\Colt\Model\Page;
use Pollora\Colt\Model\Post;
use Pollora\Colt\Model\Taxonomy;
use Pollora\Colt\Model\Term;
use Pollora\Colt\Model\TermRelationship;
use Pollora\Colt\Model\User;

it('orders posts from the oldest and from the newest', function () {
    $old = factory(Post::class)->create(['post_date' => '2020-01-01 00:00:00']);
    $new = factory(Post::class)->create(['post_date' => '2024-01-01 00:00:00']);

    expect(Post::oldest()->first()->ID)->toBe($old->ID)
        ->and(Post::newest()->first()->ID)->toBe($new->ID);
});

it('finds the home page from the page_on_front option', function () {
    $home = factory(Post::class)->create(['post_type' => 'page']);
    factory(Post::class)->create(['post_type' => 'page']);
    Option::add('page_on_front', (string) $home->ID);

    $found = Page::home()->get();

    expect($found)->toHaveCount(1)
        ->and($found->first()->ID)->toBe($home->ID);
});

it('links a term to its taxonomy', function () {
    $taxonomy = factory(Taxonomy::class)->create(['taxonomy' => 'category']);

    expect($taxonomy->term->taxonomy)->toBeInstanceOf(Taxonomy::class)
        ->and($taxonomy->term->taxonomy->term_taxonomy_id)->toBe($taxonomy->term_taxonomy_id);
});

it('links a term relationship to its post and its taxonomy', function () {
    $post = factory(Post::class)->create();
    $taxonomy = factory(Taxonomy::class)->create(['taxonomy' => 'category']);
    $relationship = (new TermRelationship)->forceFill([
        'object_id' => $post->ID,
        'term_taxonomy_id' => $taxonomy->term_taxonomy_id,
    ]);

    expect($relationship->post->ID)->toBe($post->ID)
        ->and($relationship->taxonomy->term_taxonomy_id)->toBe($taxonomy->term_taxonomy_id);
});

it('filters taxonomies by name, category and menu', function () {
    factory(Taxonomy::class)->create(['taxonomy' => 'category']);
    factory(Taxonomy::class)->create(['taxonomy' => 'nav_menu']);
    factory(Taxonomy::class)->create(['taxonomy' => 'genre']);

    expect(Taxonomy::query())->toBeInstanceOf(TaxonomyBuilder::class)
        ->and(Taxonomy::query()->category()->count())->toBe(1)
        ->and(Taxonomy::query()->menu()->count())->toBe(1)
        ->and(Taxonomy::query()->name('genre')->count())->toBe(1);
});

it('filters taxonomies by term slug through slug and term', function () {
    $term = factory(Term::class)->create(['name' => 'Wanted', 'slug' => 'wanted']);
    factory(Taxonomy::class)->create(['taxonomy' => 'genre', 'term_id' => $term->term_id]);
    factory(Taxonomy::class)->create(['taxonomy' => 'genre']);

    expect(Taxonomy::query()->slug('wanted')->count())->toBe(1)
        ->and(Taxonomy::query()->term('wanted')->count())->toBe(1)
        ->and(Taxonomy::query()->slug()->count())->toBe(2)
        ->and(Taxonomy::query()->slug('')->count())->toBe(2);
});

it('filters posts by parent', function () {
    $parent = factory(Post::class)->create();
    $child = factory(Post::class)->create(['post_parent' => $parent->ID]);

    expect(Post::query()->parent($parent->ID)->pluck('ID')->all())->toBe([$child->ID]);
});

it('ignores a search made only of percent signs and spaces', function () {
    factory(Post::class, 2)->create();

    expect(Post::query()->search(' % % ')->count())->toBe(2);
});

it('reports replies and approval of a comment', function () {
    $parent = factory(Comment::class)->create(['comment_approved' => 1, 'comment_parent' => 0]);
    $reply = factory(Comment::class)->create(['comment_approved' => 0, 'comment_parent' => $parent->comment_ID]);

    expect($parent->isApproved())->toBeTrue()
        ->and($parent->isReply())->toBeFalse()
        ->and($parent->hasReplies())->toBeTrue()
        ->and($reply->isApproved())->toBeFalse()
        ->and($reply->isReply())->toBeTrue()
        ->and($reply->hasReplies())->toBeFalse()
        ->and($reply->parent->comment_ID)->toBe($parent->comment_ID)
        ->and($reply->original->comment_ID)->toBe($parent->comment_ID);
});

it('does not write an updated at column on comments', function () {
    $comment = factory(Comment::class)->create();

    expect($comment->setUpdatedAt('2024-01-01'))->toBeNull()
        ->and($comment->getAttributes())->not->toHaveKey('updated_at');
});

it('returns nothing for a menu item whose object type is unknown', function () {
    $item = factory(MenuItem::class)->create();
    $item->saveMeta('_menu_item_object', 'unknown');
    $item->load('meta');

    expect($item->instance())->toBeNull()
        ->and($item->parent())->toBeNull();
});

it('resolves the object of a custom link menu item', function () {
    $link = factory(CustomLink::class)->create();
    $item = factory(MenuItem::class)->create();
    $item->saveMeta('_menu_item_object', 'custom');
    $item->saveMeta('_menu_item_object_id', $link->ID);
    $item->load('meta');

    expect($item->instance())->toBeInstanceOf(CustomLink::class)
        ->and($item->instance()->ID)->toBe($link->ID);
});

it('serializes an array option on add and reads it back', function () {
    Option::add('palette', ['red', 'blue']);

    expect(Option::get('palette'))->toBe(['red', 'blue'])
        ->and(Option::get('missing'))->toBeNull()
        ->and(Option::getAll())->toHaveKey('palette');
});

it('refuses meta on a model that is not one of the built-in ones', function () {
    $model = new class extends Model
    {
        use MetaFields;
    };

    expect(fn () => $model->meta())->toThrow(UnexpectedValueException::class, 'must extends one of Colt built-in models');
});

it('accepts a rehash request without touching the stored password', function () {
    $user = factory(User::class)->create();
    $provider = new AuthUserProvider;

    expect($provider->rehashPasswordIfRequired($user, ['password' => 'x'], true))->toBeNull()
        ->and($user->fresh()->user_pass)->toBe($user->user_pass);
});

it('casts a thumbnail to the guid of its attachment', function () {
    $attachment = factory(Attachment::class)->create(['guid' => 'http://example.test/a.jpg']);
    $meta = factory(ThumbnailMeta::class)->create(['meta_value' => $attachment->ID]);

    expect((string) $meta)->toBe('http://example.test/a.jpg');
});

it('orders by the model creation column when the oldest scope is applied explicitly', function () {
    $old = factory(Post::class)->create(['post_date' => '2020-01-01 00:00:00']);
    factory(Post::class)->create(['post_date' => '2024-01-01 00:00:00']);

    $sql = Post::query()->scopes(['oldest'])->toSql();

    expect(Post::query()->scopes(['oldest'])->first()->ID)->toBe($old->ID)
        ->and($sql)->toContain('order by "post_date" asc');
});
