<?php

use Carbon\Carbon;
use Pollora\Colt\Model\Collection\MetaCollection;
use Pollora\Colt\Model\Page;
use Pollora\Colt\Model\Post;
use Pollora\Colt\Model\Taxonomy;
use Pollora\Colt\Model\Term;
use Pollora\Colt\Model\User;
use Pollora\Colt\Shortcode;
use Illuminate\Support\Arr;
use Illuminate\Pagination\Paginator;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

test('it has the correct class name', function () {
    $post = factory(Post::class)->create();

    expect($post)->toBeInstanceOf(Post::class);
});

test('it has an integer id', function () {
    $post = factory(Post::class)->create();

    expect($post->ID)->toBeInt();
    expect($post->ID)->toBeGreaterThan(0);
});

test('it has status scope', function () {
    factory(Post::class)->create(['post_status' => 'foo']);

    $posts = Post::status('foo')->get();

    expect($posts)->not->toBeNull();
    expect($posts)->toHaveCount(1);
});

test('it has has meta scope', function () {
    $post = factory(Post::class)->create();
    $post->saveMeta('foo', 'bar');

    $posts = Post::hasMeta('foo')->get();

    expect($posts)->toHaveCount(1);
    expect($posts->first())->toBeInstanceOf(Post::class);

    $newPost = Post::hasMeta('foo', 'bar')->first();
    expect($newPost->title)->toEqual($post->title);
    expect($newPost->ID)->toEqual($post->ID);
});

test('it has published scope', function () {
    factory(Post::class)->create(['post_status' => 'publish']);

    $posts = Post::published()->get();

    expect($posts)->not->toBeNull();
    expect($posts->count())->toBeGreaterThan(0);
});

test('it has type scope', function () {
    factory(Post::class)->create(['post_type' => 'foo']);

    $posts = Post::type('foo')->get();

    expect($posts)->not->toBeNull();
    expect($posts)->toHaveCount(1);
});

test('it has type in scope', function () {
    factory(Post::class)->create(['post_type' => 'blue']);
    factory(Post::class)->create(['post_type' => 'red']);
    factory(Post::class)->create(['post_type' => 'yellow']);

    $posts = Post::typeIn(['blue', 'yellow'])->get();

    expect($posts)->not->toBeNull();
    expect($posts)->toHaveCount(2);
});

test('it has slug scope', function () {
    factory(Post::class)->create(['post_name' => 'my-fake-post-slug']);

    $posts = Post::slug('my-fake-post-slug')->get();

    expect($posts)->not->toBeNull();
    expect($posts)->toHaveCount(1);
});

test('it has taxonomy scope', function () {
    createPostWithTaxonomiesAndTerms();

    $posts = Post::taxonomy('foo', 'bar')->get();
    expect($posts)->not->toBeNull();
    expect($posts->count())->toBeGreaterThan(0);

    $posts = Post::taxonomy('foo', ['bar'])->get();
    expect($posts)->not->toBeNull();
    expect($posts->count())->toBeGreaterThan(0);
});

test('it has children relation', function () {
    $post = factory(Post::class)->create();
    factory(Post::class)->create(['post_parent' => $post->ID]);
    factory(Post::class)->create(['post_parent' => $post->ID]);
    factory(Post::class)->create(['post_parent' => $post->ID]);

    $children = $post->children;

    expect($children)->toHaveCount(3);
    expect($children->first())->toBeInstanceOf(Post::class);
    expect($children->first()->post_parent)->toEqual($post->ID);
});

test('it can be ordered', function () {
    $older = Carbon::now()->subYears(10);

    $firstPost = factory(Post::class)->create(['post_date' => $older]);
    factory(Post::class)->create(['post_date' => $older->addMonths(1)]);
    $lastPost = factory(Post::class)->create(['post_date' => $older->addMonths(2)]);

    $newest = Post::newest()->first();
    $oldest = Post::oldest()->first();

    expect($oldest->post_name)->toEqual($firstPost->post_name);
    expect($oldest->post_title)->toEqual($firstPost->post_title);
    expect($newest->post_name)->toEqual($lastPost->post_name);
    expect($newest->post_title)->toEqual($lastPost->post_title);
});

test('it can have different post type', function () {
    $page = factory(Post::class)->create(['post_type' => 'page']);

    expect('page')->toEqual($page->post_type);
});

test('it has aliases', function () {
    $post = factory(Post::class)->create();

    expect($post->title)->toEqual($post->post_title);
    expect($post->slug)->toEqual($post->post_name);
    expect($post->content)->toEqual($post->post_content);
    expect($post->type)->toEqual($post->post_type);
    expect($post->mime_type)->toEqual($post->post_mime_type);
    expect($post->url)->toEqual($post->guid);
    expect($post->author_id)->toEqual($post->post_author);
    expect($post->parent_id)->toEqual($post->post_parent);
    expect($post->created_at)->toEqual($post->post_date);
    expect($post->updated_at)->toEqual($post->post_modified);
    expect($post->excerpt)->toEqual($post->post_excerpt);
    expect($post->status)->toEqual($post->post_status);
});

test('it has isset method working', function () {
    $post = factory(Post::class)->create();
    $post->createMeta('foo', 'bar');

    expect(isset($post->meta->foo))->toBeTrue();
});

test('it can add alias in runtime', function () {
    $post = factory(Post::class)->create();
    $post->saveMeta('foo', 'bar');

    $post->addAlias('baz', ['meta' => 'foo']);
    expect($post->baz)->toEqual('bar');
    expect($post->meta->foo)->toEqual($post->baz);

    Post::addAlias('fee', ['meta' => 'foo']);
    expect($post->fee)->toEqual('bar');
    expect($post->meta->foo)->toEqual($post->fee);
});

test('it can accept unicode chars', function () {
    $post = factory(Post::class)->create([
        'post_content' => 'test utf8 é à',
        'post_excerpt' => 'test chinese characters お問い合わせ',
    ]);

    expect($post->post_content)->toEqual('test utf8 é à');
    expect($post->post_excerpt)->toEqual('test chinese characters お問い合わせ');
});

test('it can have custom fields', function () {
    $post = factory(Post::class)->create();

    $post->meta()->create([
        'meta_key' => 'foo',
        'meta_value' => 'bar',
    ]);

    expect($post->meta)->not->toBeEmpty();
    expect($post->fields)->not->toBeEmpty();
    expect($post->meta)->toBeInstanceOf(MetaCollection::class);
});

test('it can add custom fields', function () {
    $post = factory(Post::class)->create();

    $post->saveMeta('foo', 'bar');
    $meta = $post->meta()->orderBy('meta_id', 'desc')->first();

    expect($meta->meta_key)->toEqual('foo');
    expect($meta->meta_value)->toEqual('bar');
});

test('it can add custom fields using save field method', function () {
    $post = factory(Post::class)->create();

    $post->saveField('foo', 'bar');
    $meta = $post->meta->first();

    expect($meta->meta_key)->toEqual('foo');
    expect($meta->meta_value)->toEqual('bar');
});

test('it can save multiple meta at the same time', function () {
    $post = factory(Post::class)->create();

    $post->saveMeta([
        'foo' => 'bar',
        'fee' => 'baz',
    ]);

    expect($post->meta)->toHaveCount(2);
    expect($post->meta->foo)->toEqual('bar');
    expect($post->meta->fee)->toEqual('baz');
});

test('they can be ordered ascending', function () {
    factory(Post::class, 2)->create();

    $posts = Post::query()->orderBy('post_date', 'asc')->get();
    $first = $posts->first();
    $last = $posts->last();

    expect($first->post_date->lessThanOrEqualTo($last->post_date))->toBeTrue();
    expect($last->post_date->greaterThanOrEqualTo($first->post_date))->toBeTrue();
});

test('they can be ordered descending', function () {
    factory(Post::class, 2)->create();

    $posts = Post::orderBy('post_date', 'desc')->get();
    $last = $posts->first();
    $first = $posts->last();

    expect($first->post_date->lessThanOrEqualTo($last->post_date))->toBeTrue();
    expect($last->post_date->greaterThanOrEqualTo($first->post_date))->toBeTrue();
});

test('it can be paginated', function () {
    Paginator::useBootstrap();
    $post = factory(Post::class)->create();
    factory(Post::class)->create();
    factory(Post::class)->create();

    /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
    $paginator = Post::paginate(2);
    $firstPost = Arr::first($paginator->items());

    expect($paginator->perPage())->toEqual(2);
    expect($paginator->count())->toEqual(2);
    expect($paginator->total())->toEqual(3);
    expect($firstPost)->toBeInstanceOf(Post::class);
    expect($firstPost->post_title)->toEqual($post->post_title);
    expect($paginator->toHtml())->toMatch('/\<nav\>\s*\<ul class="pagination/');
});

test('it can have taxonomy', function () {
    $post = createPostWithTaxonomiesAndTerms();

    expect($post->taxonomies->count())->toEqual(1);
    expect($post->taxonomies->first()->taxonomy)->toEqual('foo');
});

test('it can have taxonomy and terms', function () {
    $createdPost = createPostWithTaxonomiesAndTerms();

    $post = Post::orderBy('ID', 'desc')
        ->taxonomy('foo', ['bar'])->first();

    expect($post)->not->toBeNull();
    expect($post->ID)->toEqual($createdPost->ID);

    $post = Post::orderBy('ID', 'desc')
        ->taxonomy('foo', 'bar')->first();

    expect($post)->not->toBeNull();
    expect($post->ID)->toEqual($createdPost->ID);
});

test('it can have term', function () {
    $post = createPostWithTaxonomiesAndTerms();

    expect($post->hasTerm('foo', 'bar'))->toEqual(true);
    expect($post->hasTerm('foo', 'baz'))->toEqual(false);
    expect($post->hasTerm('fee', 'bar'))->toEqual(false);
    expect($post->hasTerm('fee', 'baz'))->toEqual(false);
    expect($post->main_category)->toEqual('Bar');
    expect($post->keywords)->toEqual(['Bar']);
    expect($post->keywords_str)->toEqual('Bar');
});

test('it can have author relation', function () {
    $post = createPostWithAuthor();

    expect($post->author->display_name)->toEqual('Administrator');
    expect($post->author->user_email)->toEqual('admin@example.com');
});

test('it has the correct instance name if it is a custom post type', function () {
    factory(Post::class)->create(['post_type' => 'page']);
    Post::registerPostType('page', Page::class);

    $page = Post::orderBy('ID', 'desc')->first();

    expect($page)->toBeInstanceOf(Page::class);
});

test('it has its instance name back to post after clearing post types', function () {
    factory(Post::class)->create([
        'post_type' => 'page',
        'post_name' => 'foo2',
    ]);
    Post::registerPostType('page', Page::class);
    Post::clearRegisteredPostTypes();

    $page = Post::where('post_name', 'foo2')->first();

    expect($page)->toBeInstanceOf(Post::class);
});

test('its relation can have different database connection', function () {
    $post = factory(Post::class)->make();
    $post->setConnection('foo');
    $post->author()->associate(factory(User::class)->create());
    $post->save();

    expect($post->author->getConnectionName())->toEqual('foo');
});

test('its type is fillable', function () {
    $post = factory(Post::class)->create(['post_type' => 'video']);

    expect($post->post_type)->toEqual('video');
});

test('its parent does not return null when it is zero', function () {
    $post = factory(Post::class)->create(['post_parent' => 0]);

    expect($post->post_parent)->not->toBeNull();
    expect($post->post_parent)->toEqual(0);
});

test('parent relation', function () {
    $parent = factory(Post::class)->create();
    $post = factory(Post::class)->create(['post_parent' => $parent->ID]);

    expect($post->parent)->toEqual($parent->fresh());
});

test('attachment relation', function () {
    $parent = factory(Post::class)->create();
    $attachment = factory(Post::class)->create([
        'post_parent' => $parent->ID,
        'post_type' => 'attachment',
    ]);

    expect($parent->attachment->first())->toEqual($attachment->fresh());
});

test('revision relation', function () {
    $parent = factory(Post::class)->create();
    $revision = factory(Post::class)->create([
        'post_parent' => $parent->ID,
        'post_type' => 'revision',
    ]);

    $revisions = $parent->revision;
    expect($revisions->first())->toEqual($revision->fresh());
});

test('it can have shortcode', function () {
    registerFooShortcode();

    $post = factory(Post::class)->create([
        'post_content' => 'test [foo a="bar" b="baz"]',
    ]);

    expect('test foo.bar.baz')->toEqual($post->content);
});

test('it can have shortcode from config file', function () {
    $post = factory(Post::class)->create([
        'post_content' => 'foo [fake one="two"]',
    ]);

    expect($post->content)->toEqual('foo html-for-shortcode-fake-two');
});

test('its content can have multiple shortcodes', function () {
    registerFooShortcode();

    $post = factory(Post::class)->create([
        'post_content' => '1~[foo a="bar" b="baz"] 2~[foo a="baz" b="bar"]',
    ]);

    expect('1~foo.bar.baz 2~foo.baz.bar')->toEqual($post->content);
});

test('its shortcode can be removed', function () {
    registerFooShortcode();
    Post::removeShortcode('foo');

    $post = factory(Post::class)->create([
        'post_content' => 'test [foo a="bar" b="baz"]',
    ]);

    expect('test [foo a="bar" b="baz"]')->toEqual($post->content);
});

test('it can have post format', function () {
    $post = createPostWithPostFormatTaxonomy();

    expect($post->getFormat())->toEqual('foo');
});

test('it can have false post format', function () {
    $post = factory(Post::class)->create();

    expect($post->getFormat())->toBeFalse();
});

test('it has correct post type with callback in where', function () {
    $query = Page::where(function ($q) {
        $q->where('foo', 'bar');
    });

    $expectedQuery = 'select * from "wp_posts" where "post_type" = ? and ("foo" = ?)';
    $expectedBindings = ['page', 'bar'];

    expect($query->toSql())->toEqual($expectedQuery);
    expect($query->getBindings())->toBe($expectedBindings);
});

test('its search has correct empty word query', function () {
    $emptyWord = Post::search();

    $expectedEmptyWordQuery = 'select * from "wp_posts"';
    $expectedEmptyWordBindings = [];

    expect($emptyWord->toSql())->toEqual($expectedEmptyWordQuery);
    expect($emptyWord->getBindings())->toBe($expectedEmptyWordBindings);
});

test('its search has correct single word query', function () {
    $singleWord = Post::search('foo');

    $expectedSingleWordQuery = 'select * from "wp_posts" where ("post_title" like ? or "post_excerpt" like ? or "post_content" like ?)';
    $expectedSingleWordBindings = ['%foo%', '%foo%', '%foo%'];

    expect($singleWord->toSql())->toEqual($expectedSingleWordQuery);
    expect($singleWord->getBindings())->toBe($expectedSingleWordBindings);
});

test('its search has correct multiple word query', function () {
    $multipleWord = Post::search('foo bar');

    $expectedMultipleWordQuery = 'select * from "wp_posts" where ("post_title" like ? or "post_excerpt" like ? or "post_content" like ? or "post_title" like ? or "post_excerpt" like ? or "post_content" like ?)';
    $expectedMultipleWordBindings = ['%foo%', '%foo%', '%foo%', '%bar%', '%bar%', '%bar%'];

    expect($multipleWord->toSql())->toEqual($expectedMultipleWordQuery);
    expect($multipleWord->getBindings())->toBe($expectedMultipleWordBindings);
});

test('its search has correct multiple word in array query', function () {
    $multipleWordArray = Post::search(['foo', 'bar']);

    $expectedMultipleWordQuery = 'select * from "wp_posts" where ("post_title" like ? or "post_excerpt" like ? or "post_content" like ? or "post_title" like ? or "post_excerpt" like ? or "post_content" like ?)';
    $expectedMultipleWordBindings = ['%foo%', '%foo%', '%foo%', '%bar%', '%bar%', '%bar%'];

    expect($multipleWordArray->toSql())->toEqual($expectedMultipleWordQuery);
    expect($multipleWordArray->getBindings())->toBe($expectedMultipleWordBindings);
});

test('its search for different post types is correct', function () {
    $singleWord = Page::search('foo');
    $multipleWord = Page::search('foo bar');

    $expectedSingleWordQuery = 'select * from "wp_posts" where "post_type" = ? and ("post_title" like ? or "post_excerpt" like ? or "post_content" like ?)';
    $expectedMultipleWordQuery = 'select * from "wp_posts" where "post_type" = ? and ("post_title" like ? or "post_excerpt" like ? or "post_content" like ? or "post_title" like ? or "post_excerpt" like ? or "post_content" like ?)';

    expect($singleWord->toSql())->toEqual($expectedSingleWordQuery);
    expect($multipleWord->toSql())->toEqual($expectedMultipleWordQuery);
});

function createPostWithTaxonomiesAndTerms(): Post
{
    $post = factory(Post::class)->create();

    $post->taxonomies()->attach(
        factory(Taxonomy::class)->create([
            'taxonomy' => 'foo',
        ])->term_taxonomy_id,
        [
            'term_order' => 0,
        ]
    );

    return $post;
}

function createPostWithAuthor(): Post
{
    $post = factory(Post::class)->create();

    $post->author()->associate(
        factory(User::class)->create()
    );

    return $post;
}

function registerFooShortcode(): void
{
    Post::addShortcode('foo', function (ShortcodeInterface $shortcode) {
        return sprintf(
            '%s.%s.%s',
            $shortcode->getName(),
            $shortcode->getParameter('a'),
            $shortcode->getParameter('b')
        );
    });
}

function createPostWithPostFormatTaxonomy(): Post
{
    $post = factory(Post::class)->create();

    $post->taxonomies()->attach(
        factory(Taxonomy::class)->create([
            'taxonomy' => 'post_format',
            'term_id' => function () {
                return factory(Term::class)->create([
                    'name' => $name = 'post-format-foo',
                    'slug' => $name,
                ])->term_id;
            },
        ])->term_taxonomy_id,
        [
            'term_order' => 0,
        ]
    );

    return $post;
}
