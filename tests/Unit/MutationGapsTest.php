<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Pollora\Colt\Database;
use Pollora\Colt\Laravel\Auth\AuthUserProvider;
use Pollora\Colt\Model\Attachment;
use Pollora\Colt\Model\Comment;
use Pollora\Colt\Model\Meta\ThumbnailMeta;
use Pollora\Colt\Model\Option;
use Pollora\Colt\Model\Page;
use Pollora\Colt\Model\Post;
use Pollora\Colt\Model\Taxonomy;
use Pollora\Colt\Model\Term;
use Pollora\Colt\Model\User;
use Pollora\Colt\Services\PasswordService;

function attachTaxonomy(Post $post, string $taxonomy, string $slug): void
{
    $post->taxonomies()->attach(
        factory(Taxonomy::class)->create([
            'taxonomy' => $taxonomy,
            'term_id' => fn () => factory(Term::class)->create(['name' => ucfirst($slug), 'slug' => $slug])->term_id,
        ])->term_taxonomy_id,
        ['term_order' => 0]
    );
}

it('boots Eloquent on the capsule it returns', function () {
    $capsule = Database::connect(['driver' => 'sqlite', 'database' => ':memory:']);

    expect(EloquentModel::getConnectionResolver())->toBe($capsule->getDatabaseManager());
});

it('qualifies the foreign key of a hasOne relation with the related table', function () {
    $term = factory(Term::class)->create();
    $post = factory(Post::class)->create();

    expect($term->taxonomy()->getQualifiedForeignKeyName())->toBe('term_taxonomy.term_id')
        ->and($post->thumbnail()->getQualifiedForeignKeyName())->toBe('postmeta.post_id');
});

it('keeps an explicit relation name on belongsToMany', function () {
    $relation = (new Post)->belongsToMany(
        Taxonomy::class,
        'term_relationships',
        'object_id',
        'term_taxonomy_id',
        null,
        null,
        'custom'
    );

    expect($relation->getRelationName())->toBe('custom');
});

it('only lists published posts, including scheduled posts whose date has passed', function () {
    $published = factory(Post::class)->create(['post_status' => 'publish', 'post_date' => Carbon::now()->addDay()]);
    $past = factory(Post::class)->create(['post_status' => 'future', 'post_date' => Carbon::now()->subDay()]);
    factory(Post::class)->create(['post_status' => 'future', 'post_date' => Carbon::now()->addDay()]);
    factory(Post::class)->create(['post_status' => 'draft', 'post_date' => Carbon::now()->subDay()]);

    $ids = Post::published()->pluck('ID')->all();

    expect($ids)->toEqualCanonicalizing([$published->ID, $past->ID]);
});

it('filters by both the taxonomy and the term slug', function () {
    $match = factory(Post::class)->create();
    attachTaxonomy($match, 'foo', 'bar');
    $otherTerm = factory(Post::class)->create();
    attachTaxonomy($otherTerm, 'foo', 'baz');
    $otherTaxonomy = factory(Post::class)->create();
    attachTaxonomy($otherTaxonomy, 'fee', 'bar');
    factory(Post::class)->create();

    expect(Post::taxonomy('foo', 'bar')->pluck('ID')->all())->toBe([$match->ID])
        ->and(Post::taxonomy('foo', ['bar', 'baz'])->pluck('ID')->all())->toEqualCanonicalizing([$match->ID, $otherTerm->ID]);
});

it('strips percent signs and surrounding spaces from search terms', function () {
    $query = Post::search(['  foo  ', '100%']);

    expect($query->getBindings())->toBe(['%foo%', '%foo%', '%foo%', '%100%', '%100%', '%100%']);
});

it('does not filter when the search only holds percent signs', function () {
    $query = Post::search('%');

    expect($query->toSql())->toBe('select * from "wp_posts"')
        ->and($query->getBindings())->toBe([]);
});

it('marks posts hydrated from the database as existing and clean', function () {
    $created = factory(Post::class)->create();

    $post = Post::find($created->ID);

    expect($post->exists)->toBeTrue()
        ->and($post->isClean())->toBeTrue();
});

it('lets every retrieved listener run', function () {
    $calls = [];
    Post::retrieved(function () use (&$calls) {
        $calls[] = 'first';

        return 'not-null';
    });
    Post::retrieved(function () use (&$calls) {
        $calls[] = 'second';
    });
    $created = factory(Post::class)->create();

    Post::find($created->ID);

    expect($calls)->toBe(['first', 'second']);
});

it('renders shortcodes in the excerpt', function () {
    Post::addShortcode('foo', fn () => 'rendered');
    $post = factory(Post::class)->create(['post_excerpt' => 'before [foo] after']);

    expect($post->getExcerptAttribute())->toBe('before rendered after');
});

it('falls back to meta for unknown attributes only', function () {
    $post = factory(Post::class)->create();
    $post->createMeta('custom_field', 'bar');
    $post->createMeta('table', 'shadow');

    expect($post->custom_field)->toBe('bar')
        ->and($post->table)->toBeNull();
});

it('hashes the trimmed lower-cased email for the avatar', function () {
    $user = factory(User::class)->create(['user_email' => '  Admin@Example.COM ']);

    expect($user->avatar)->toBe('//secure.gravatar.com/avatar/'.md5('admin@example.com').'?d=mm');
});

it('returns the full url for the full size even when the metadata lists a full size', function () {
    $attachment = factory(Attachment::class)->create(['guid' => 'http://example.com/full.jpg']);
    $attachment->saveMeta('_wp_attachment_metadata', serialize([
        'sizes' => ['full' => ['file' => 'other.jpg', 'width' => 1, 'height' => 1]],
    ]));
    $meta = factory(ThumbnailMeta::class)->create(['meta_value' => $attachment->ID]);

    expect($meta->size(ThumbnailMeta::SIZE_FULL))->toBe('http://example.com/full.jpg');
});

it('casts a missing attachment guid to an empty string', function () {
    $meta = new ThumbnailMeta;
    $meta->setRelation('attachment', new Attachment);

    expect((string) $meta)->toBe('');
});

it('treats a string comment_approved of 1 as approved', function () {
    $comment = new Comment;
    $comment->setRawAttributes(['comment_approved' => '1']);

    expect($comment->isApproved())->toBeTrue();
});

it('limits the home page query to a single row', function () {
    Option::add('page_on_front', '7');

    $query = Page::home();

    expect($query->getQuery()->limit)->toBe(1);
});

it('uses 8 as the phpass cost', function () {
    expect((new PasswordService)->makeHash('secret'))->toStartWith('$P$B');
});

it('persists the user when updating the remember token', function () {
    $user = factory(User::class)->create();
    $user->display_name = 'Renamed';

    (new AuthUserProvider)->updateRememberToken($user, 'tok');

    expect($user->fresh()->display_name)->toBe('Renamed');
});

it('only matches posts whose meta key matches', function () {
    $match = factory(Post::class)->create();
    $match->saveMeta('foo', 'bar');
    $other = factory(Post::class)->create();
    $other->saveMeta('fee', 'bar');

    expect(Post::hasMeta('foo', 'bar')->pluck('ID')->all())->toBe([$match->ID])
        ->and(Post::hasMeta('foo')->pluck('ID')->all())->toBe([$match->ID]);
});

it('refreshes the loaded meta and returns true after saving several', function () {
    $post = factory(Post::class)->create();
    $post->meta;

    $result = $post->saveMeta(['foo' => 'bar', 'fee' => 'baz']);

    expect($result)->toBeTrue()
        ->and($post->relationLoaded('meta'))->toBeTrue()
        ->and($post->meta->foo)->toBe('bar')
        ->and($post->meta->fee)->toBe('baz');
});

it('returns true after saving one meta', function () {
    $post = factory(Post::class)->create();

    expect($post->saveMeta('foo', 'bar'))->toBeTrue();
});

it('forwards the connection name to every model of a collection relation', function () {
    $post = factory(Post::class)->make();
    $post->setConnection('foo');
    $post->save();
    $comment = factory(Comment::class)->make(['comment_post_ID' => $post->ID]);
    $comment->setConnection('foo');
    $comment->save();

    $comments = Post::on('foo')->find($post->ID)->comments;

    expect($comments)->toHaveCount(1)
        ->and($comments->first()->getConnectionName())->toBe('foo');
});
