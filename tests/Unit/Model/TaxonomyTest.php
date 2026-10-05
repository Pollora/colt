<?php

use Pollora\Colt\Model\Post;
use Pollora\Colt\Tests\Unit\Model\Category;
use Pollora\Colt\Model\Taxonomy;
use Pollora\Colt\Model\Term;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

test('it belongs to a term', function () {
    $taxonomy = factory(Taxonomy::class)->create([
        'term_id' => 0,
        'count' => 1,
    ]);

    $term = factory(Term::class)->create();
    $taxonomy->term()->associate($term);

    expect($taxonomy->term_id)->toEqual($term->term_id);
});

test('it can filter taxonomy by term', function () {
    $taxonomy = createTaxonomyWithTermsAndPosts();
    $term = $taxonomy->term;

    $taxonomies = Taxonomy::slug($term->slug)->get();

    foreach ($taxonomies as $taxonomy) {
        expect($taxonomy->taxonomy)->toEqual('foo');
        expect($taxonomy->term_id)->not->toBeNull();
        expect($taxonomy->term->term_id)->toEqual($taxonomy->term_id);
    }
});

test('it can be queried by name and term slug', function () {
    createTaxonomyWithTermsAndPosts();

    $foo = Taxonomy::name('foo')->slug('bar')->first();
    expect($foo->name)->toEqual('Bar');

    $foo = Taxonomy::name('foo')->slug('bar')->get();
    $foo->each(function ($foo) {
        expect($foo->name)->toEqual('Bar');
        expect($foo->slug)->toEqual('bar');
    });
});

test('it can be queries by term as an aliases to slug', function () {
    createTaxonomyWithTermsAndPosts();

    $foo = Taxonomy::name('foo')->term('bar')->first();

    expect($foo->name)->toEqual('Bar');
});

test('it can query taxonomy by term and get all posts related', function () {
    createTaxonomyWithTermsAndPosts();

    $post = Taxonomy::name('foo')->slug('bar')
        ->orderBy('term_taxonomy_id', 'desc')->first()
        ->posts->first();

    expect($post->title)->toEqual('Foo bar');
});

test('its first post should have keywords if it has taxonomy and term', function () {
    $taxonomy = createTaxonomyWithTermsAndPosts();

    $post = $taxonomy->posts->first();

    expect(count($post->keywords))->toBeGreaterThan(0);
});

test('it has correct query with callback in where', function () {
    /** @var Builder $query */
    $query = Category::query()->where(function (Builder $q) {
        $q->where('foo', 'bar');
    });

    $expectedQuery = 'select * from "wp_term_taxonomy" where "taxonomy" = ? and ("foo" = ?)';
    $expectedBindings = ['category', 'bar'];

    expect($query->toSql())->toEqual($expectedQuery);
    expect($query->getBindings())->toBe($expectedBindings);
});

test('it has meta relation', function () {
    /** @var Taxonomy $taxonomy */
    $taxonomy = factory(Taxonomy::class)->create();

    /** @var Term $term */
    $term = $taxonomy->term;
    $term->saveMeta('foo', 'bar');

    expect($taxonomy->meta)->not->toBeEmpty();
    expect($taxonomy->meta->foo)->toEqual('bar');
});

test('it has parent', function () {
    /** @var Taxonomy $parent */
    $parent = factory(Taxonomy::class)->create();

    /** @var Taxonomy $taxonomy */
    $taxonomy = factory(Taxonomy::class)->create(['parent' => $parent->term_taxonomy_id]);

    expect($taxonomy->parent()->first())->toEqual($parent->fresh());
});

function createTaxonomyWithTermsAndPosts(): Taxonomy
{
    $taxonomy = factory(Taxonomy::class)->create([
        'taxonomy' => 'foo',
        'term_id' => function () {
            return factory(Term::class)->create([
                'name' => 'Bar',
                'slug' => 'bar',
            ])->term_id;
        }
    ]);

    $post = factory(Post::class)->create([
        'post_title' => 'Foo bar',
    ]);

    $post->taxonomies()->attach($taxonomy->term_taxonomy_id);

    return $taxonomy;
}
