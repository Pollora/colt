<?php

use Pollora\Colt\Model\Meta\PostMeta;

test('it unserialize serialized values', function () {
    $meta = factory(PostMeta::class)->create(['meta_value' => serialize('foo')]);
    expect($meta->value)->toEqual('foo');
});

test('it also works with unserialized values', function () {
    $meta = factory(PostMeta::class)->create(['meta_value' => 'foo']);
    expect($meta->value)->toEqual('foo');
});

test('it never instantiates serialized objects', function () {
    $meta = factory(PostMeta::class)->create(['meta_value' => serialize(new \ArrayObject([1]))]);

    expect($meta->value)->toBeInstanceOf(\__PHP_Incomplete_Class::class);
});

test('it keeps a serialized false', function () {
    $meta = factory(PostMeta::class)->create(['meta_value' => serialize(false)]);

    expect($meta->value)->toBeFalse();
});
