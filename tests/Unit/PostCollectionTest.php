<?php

declare(strict_types=1);

/*
 * Behaviour of PostCollection that the query-count tests do not pin: which
 * posts get their relations loaded, and which are left alone. The collection
 * `$all` holds every post; `$some` is a smaller collection over the first two
 * of them, so a relation loaded "for the collection" is visible on the posts
 * that are outside of it.
 */

use Pollora\Colt\Model\Collection\PostCollection;
use Pollora\Colt\Model\Post;
use Pollora\Colt\Tests\Support\ColtFixtures;
use Pollora\Colt\Tests\Support\QueryCounter;

/**
 * Seeds `$n` posts and returns them as a collection.
 */
function colt_all_posts(int $n = 5): PostCollection
{
    ColtFixtures::posts($n);

    return Post::where('post_type', 'post')->get();
}

/**
 * Whether each post of the list has the relation loaded, in order.
 *
 * @param  iterable<Post>  $posts
 * @return list<bool>
 */
function colt_loaded(iterable $posts, string $relation): array
{
    $loaded = [];
    foreach ($posts as $post) {
        $loaded[] = $post->relationLoaded($relation);
    }

    return $loaded;
}

it('loads the appended relations of the serialised posts only: :dataset', function (Closure $serialise): void {
    $all = colt_all_posts();
    $some = $all->take(2);

    $serialise($some);

    expect(colt_loaded($all, 'taxonomies'))->toBe([true, true, false, false, false])
        ->and(colt_loaded($all, 'thumbnail'))->toBe([true, true, false, false, false]);
})->with([
    'toArray' => [fn (PostCollection $posts) => $posts->toArray()],
    'jsonSerialize' => [fn (PostCollection $posts) => $posts->jsonSerialize()],
    'toJson' => [fn (PostCollection $posts) => $posts->toJson()],
]);

it('serialises a collection that holds something else than posts, and still primes the posts after it', function (): void {
    $all = colt_all_posts(5);
    $mixed = new PostCollection([new stdClass, ...$all->take(2)->all()]);

    expect($mixed->toArray())->toHaveCount(3)
        ->and($mixed->jsonSerialize())->toHaveCount(3)
        ->and(colt_loaded($all, 'taxonomies'))->toBe([true, true, false, false, false]);
});

it('does not load again a relation that the posts already have', function (): void {
    $all = colt_all_posts(3);
    $all->load('taxonomies', 'thumbnail');
    $taxonomies = $all->first()->taxonomies;
    $thumbnail = $all->first()->thumbnail;

    $all->toArray();

    expect($all->first()->taxonomies)->toBe($taxonomies)
        ->and($all->first()->thumbnail)->toBe($thumbnail);
});

it('does not load the relations of posts that append nothing that reads them', function (): void {
    $all = colt_all_posts(3);
    $all->each(fn (Post $post) => $post->setAppends(['title']));

    $all->toArray();

    expect(colt_loaded($all, 'taxonomies'))->toBe([false, false, false])
        ->and(colt_loaded($all, 'thumbnail'))->toBe([false, false, false]);
});

it('loads a relation once for every post that lacks it', function (): void {
    $all = colt_all_posts(4);
    $all->first()->load('taxonomies');

    $queries = QueryCounter::count(fn () => $all->loadRelationForAll('taxonomies'));

    expect(colt_loaded($all, 'taxonomies'))->toBe([true, true, true, true])
        ->and($queries)->toBe(2); // the taxonomies, then their term
});

it('loads the relation for the collection the post was first in', function (): void {
    $all = colt_all_posts();
    $all->take(2);

    $all->first()->terms;

    expect(colt_loaded($all, 'taxonomies'))->toBe([true, true, true, true, true]);
});
