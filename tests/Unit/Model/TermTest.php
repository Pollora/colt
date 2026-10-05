<?php

use Pollora\Colt\Model\Term;

test('it can create term meta', function () {
    $term = factory(Term::class)->create();

    $meta = $term->meta()->create([
        'meta_key' => 'foo',
        'meta_value' => 'bar',
    ]);

    expect($meta->meta_key)->toEqual('foo');
    expect($meta->meta_value)->toEqual('bar');
});

test('it can create meta using helper method', function () {
    $term = factory(Term::class)->create();

    $term->saveMeta('foo', 'bar');
    $meta = $term->meta;

    expect($term->meta)->not->toBeEmpty();
    expect($meta->count())->toBeGreaterThan(0);
    expect($term->meta->foo)->toEqual('bar');
});

test('it has meta relation', function () {
    $term = createTermWithTwoMetaFields();

    $count = $term->meta->count();

    expect($count)->toEqual(2);
});

test('its meta can be queried by its relation', function () {
    $term = createTermWithTwoMetaFields();

    $meta = $term->meta()->where('meta_key', 'foo')->first();

    expect($meta->meta_value)->toEqual('bar');
});

function createTermWithTwoMetaFields(): Term
{
    $term = factory(Term::class)->create();

    $term->saveMeta('foo', 'bar');
    $term->saveMeta('fee', 'baz');

    return $term;
}
