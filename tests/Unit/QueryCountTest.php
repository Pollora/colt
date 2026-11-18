<?php

declare(strict_types=1);

/*
 * PERF-03: no N+1 query pattern.
 *
 * Each operation runs on a data set of 10 and of 100 posts (every post has its
 * own author, thumbnail, terms and comments) and the number of SQL queries
 * must not depend on the size. The "budget" is the number of queries measured
 * once the code was fixed: a regression that adds a query fails even if it is
 * constant. The last tests prove that the counter does see an N+1.
 *
 * The reusable part is Pollora\Colt\Tests\Support\QueryCounter.
 */

use Pollora\Colt\Model\Collection\MenuItemCollection;
use Pollora\Colt\Model\Collection\PostCollection;
use Pollora\Colt\Model\Comment;
use Pollora\Colt\Model\Menu;
use Pollora\Colt\Model\MenuItem;
use Pollora\Colt\Model\Post;
use Pollora\Colt\Model\Taxonomy;
use Pollora\Colt\Model\Term;
use Pollora\Colt\Model\User;
use Pollora\Colt\Tests\Support\ColtFixtures;
use Pollora\Colt\Tests\Support\QueryCounter;

/**
 * Query counts of an operation at 10 and 100 posts.
 *
 * @param  Closure(int):void  $seed  builds the data set of the given size
 * @param  Closure():mixed  $operation
 * @return array<int,int>
 */
function colt_counts(Closure $seed, Closure $operation): array
{
    return QueryCounter::countsBySize(function (int $size) use ($seed, $operation): Closure {
        $seed($size);

        return $operation;
    });
}

$posts = fn (int $n) => ColtFixtures::posts($n);
$menu = fn (int $n) => ColtFixtures::menu($n);

dataset('post operations', [
    'hydrate' => [
        fn () => Post::where('post_type', 'post')->get(), 2,
    ],
    'meta by property, meta collection and getMeta' => [
        fn () => Post::where('post_type', 'post')->get()
            ->map(fn (Post $p): array => [$p->subtitle, $p->meta->price, $p->getMeta('price')]), 2,
    ],
    'toArray' => [
        fn () => Post::where('post_type', 'post')->get()->toArray(), 7,
    ],
    'toJson' => [
        fn () => Post::where('post_type', 'post')->get()->toJson(), 7,
    ],
    'terms attribute' => [
        fn () => Post::where('post_type', 'post')->get()->map(fn (Post $p) => $p->terms), 4,
    ],
    'main category, keywords' => [
        fn () => Post::where('post_type', 'post')->get()->map(fn (Post $p): array => [$p->main_category, $p->keywords, $p->keywords_str]), 4,
    ],
    'featured image attribute' => [
        fn () => Post::where('post_type', 'post')->get()->map(fn (Post $p) => $p->image), 5,
    ],
    'relations eager loaded with with()' => [
        fn () => Post::where('post_type', 'post')->with('author', 'taxonomies', 'comments', 'thumbnail', 'children')->get()
            ->map(fn (Post $p): array => [
                $p->author->email, $p->taxonomies->count(), $p->comments->count(),
                $p->thumbnail?->attachment?->guid, $p->children->count(),
            ]), 12,
    ],
    'attachments alt text (meta alias)' => [
        fn () => Post::where('post_type', 'attachment')->get()->map(fn ($a) => $a->alt), 2,
    ],
    'filter by meta' => [
        fn () => Post::hasMeta('price')->get(), 2,
    ],
    'filter by taxonomy' => [
        fn () => Post::taxonomy('category', ['category-1', 'category-2'])->get(), 2,
    ],
]);

it('runs a number of queries that does not depend on the collection size: :dataset', function (Closure $operation, int $budget) use ($posts): void {
    $counts = colt_counts($posts, $operation);

    expect(QueryCounter::isConstant($counts))->toBeTrue(json_encode($counts))
        ->and($counts[100])->toBeLessThanOrEqual($budget);
})->with('post operations');

it('runs a constant number of queries on users and their meta', function () use ($posts): void {
    $counts = colt_counts($posts, fn () => User::all()->map(fn (User $u): array => [$u->first_name, $u->avatar, $u->toArray()]));

    expect(QueryCounter::isConstant($counts))->toBeTrue(json_encode($counts))
        ->and($counts[100])->toBeLessThanOrEqual(2);
});

it('runs a constant number of queries on taxonomies, their term and term meta', function () use ($posts): void {
    $counts = colt_counts($posts, fn () => Taxonomy::all()->map(fn (Taxonomy $t): array => [$t->name, $t->slug, $t->toArray()]));

    expect(QueryCounter::isConstant($counts))->toBeTrue(json_encode($counts))
        ->and($counts[100])->toBeLessThanOrEqual(2);

    $counts = colt_counts($posts, fn () => Term::all()->map(fn (Term $t) => $t->meta->color));

    expect(QueryCounter::isConstant($counts))->toBeTrue(json_encode($counts));
});

it('resolves the instance of every menu item in one query per class', function () use ($menu): void {
    $counts = colt_counts($menu, fn () => Menu::first()->items->map(fn ($item) => $item->instance()?->getKey()));

    expect(QueryCounter::isConstant($counts))->toBeTrue(json_encode($counts))
        ->and($counts[100])->toBeLessThanOrEqual(6);
});

it('resolves the parent of every menu item in one query per class', function () use ($menu): void {
    $counts = colt_counts($menu, fn () => Menu::first()->items->map(fn ($item) => $item->parent()?->getKey()));

    expect(QueryCounter::isConstant($counts))->toBeTrue(json_encode($counts))
        ->and($counts[100])->toBeLessThanOrEqual(6);
});

it('gives the same serialised posts as serialising them one by one', function () use ($posts): void {
    $posts(5);

    $batched = Post::where('post_type', 'post')->get();
    $oneByOne = $batched->map(fn (Post $p): array => Post::find($p->ID)->toArray())->all();

    expect($batched)->toBeInstanceOf(PostCollection::class)
        ->and($batched->toArray())->toEqual($oneByOne)
        ->and(json_decode($batched->toJson(), true))->toEqual(json_decode(json_encode($oneByOne), true));
});

it('gives the same menu targets as looking them up one by one', function () use ($menu): void {
    $menu(4);

    $items = Menu::first()->items;
    $expected = $items->map(fn ($item) => [
        'instance' => Post::find($item->meta->_menu_item_object_id)?->getKey(),
        'parent' => $item->meta->_menu_item_menu_item_parent
            ? Post::find($item->meta->_menu_item_menu_item_parent)?->getKey()
            : null,
    ])->all();

    $actual = $items->map(fn ($item) => [
        'instance' => $item->instance()?->getKey(),
        'parent' => $item->parent()?->getKey(),
    ])->all();

    expect($items)->toBeInstanceOf(MenuItemCollection::class)
        ->and($actual)->toEqual($expected);
});

it('queries again when a menu item target is asked a second time', function () use ($menu): void {
    $menu(3);
    $items = Menu::first()->items;
    $items->each(fn ($item) => $item->instance());

    $second = QueryCounter::count(fn () => $items->first()->instance());

    expect($second)->toBeGreaterThan(0);
});

it('still resolves a menu item that does not belong to a collection', function () use ($menu): void {
    $menu(2);
    $alone = MenuItem::first();

    expect($alone->instance())->toBeInstanceOf(Post::class)
        ->and(QueryCounter::count(fn () => $alone->instance()))->toBe(2);
});

it('loads the meta of terms and comments for the whole collection when one reads it', function () use ($posts): void {
    $counts = colt_counts($posts, fn () => Comment::all()->map(fn (Comment $c) => $c->meta->rating));

    expect(QueryCounter::isConstant($counts))->toBeTrue(json_encode($counts))
        ->and($counts[100])->toBeLessThanOrEqual(2);
});

it('does not load term meta nor serialise it when nobody reads it', function () use ($posts): void {
    $posts(3);

    $queries = QueryCounter::count(fn () => Term::all()->toArray());
    $term = Term::all()->first();

    expect($queries)->toBe(1)
        ->and($term->toArray())->not->toHaveKey('meta');
});

/*
 * Witnesses: an N+1 must make the check above fail, or the check proves nothing.
 */

it('detects an N+1 on a lazy relation read in a loop', function () use ($posts): void {
    $counts = colt_counts($posts, fn () => Post::where('post_type', 'post')->get()->map(fn (Post $p) => $p->comments->count()));

    expect(QueryCounter::isConstant($counts))->toBeFalse()
        ->and($counts[100] - $counts[10])->toBe(90);
});

it('detects an N+1 written as one query per id', function () use ($posts): void {
    $counts = colt_counts($posts, function () {
        foreach (Post::where('post_type', 'post')->pluck('ID') as $id) {
            Post::find($id);
        }
    });

    expect(QueryCounter::isConstant($counts))->toBeFalse();
});

it('counts exactly the queries that run', function (): void {
    ColtFixtures::posts(3);

    expect(QueryCounter::count(fn () => Post::where('post_type', 'post')->pluck('ID')))->toBe(1)
        ->and(QueryCounter::count(fn () => Post::where('post_type', 'post')->get()))->toBe(2)
        ->and(QueryCounter::count(fn () => null))->toBe(0)
        ->and(QueryCounter::queries(fn () => Post::find(1)))->toHaveCount(2);
});

it('tolerates a bounded growth only when asked to', function (): void {
    expect(QueryCounter::isConstant([10 => 4, 100 => 5]))->toBeFalse()
        ->and(QueryCounter::isConstant([10 => 4, 100 => 5], tolerance: 1))->toBeTrue();
});
