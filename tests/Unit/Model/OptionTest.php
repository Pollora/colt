<?php

use Pollora\Colt\Model\Option;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

test('it can return all configs as array', function () {
    factory(Option::class)->create([
        'option_name' => 'foo',
        'option_value' => 'bar',
    ]);

    $options = Option::asArray();
    $expected = ['foo' => 'bar'];

    expect($options)->toEqualCanonicalizing($expected);
    expect($options)->toHaveKey('foo');
    expect($options['foo'])->toEqual('bar');
});

test('it can return just the config passing the keys', function () {
    Option::add('one', 'two');
    Option::add('three', 'four');
    Option::add('five', 'six');

    $options = Option::asArray(['three', 'five']);

    expect($options)->toHaveCount(2);
    expect($options)->toHaveKey('three');
    expect($options)->toHaveKey('five');
    expect($options)->not->toHaveKey('one');
    expect($options['three'])->toEqual('four');
});

test('it has a countable as array method', function () {
    factory(Option::class, 2)->create();

    $options = Option::asArray();

    expect($options)->toBeArray();
    expect(count($options))->toBeGreaterThan(0);
});

test('it can have serialized data', function () {
    factory(Option::class)->create([
        'option_name' => 'foo',
        'option_value' => serialize($array = ['foo', 'bar']),
    ]);

    $options = Option::asArray();

    expect($options)->toHaveKey('foo');
    expect($options['foo'])->toBeArray();
    expect($options)->toContain($array);
    expect($options['foo'])->toEqualCanonicalizing($array);
});

test('it returns null if not found', function () {
    $value = Option::get('b03e3fd');

    expect($value)->toBeNull();
});

test('it has simple value attribute', function () {
    $option = factory(Option::class)->create([
        'option_name' => 'foo',
        'option_value' => 'bar',
    ]);

    expect($option->value)->toEqual('bar');
});

test('it can unserialize data if necessary', function () {
    $option = factory(Option::class)->create([
        'option_name' => 'foo',
        'option_value' => serialize($array = [1, 2, 3]),
    ]);

    expect($option->value)->toEqual($array);
});

test('it never instantiates serialized objects', function () {
    $option = factory(Option::class)->create([
        'option_name' => 'foo',
        'option_value' => serialize(new \ArrayObject([1])),
    ]);

    expect($option->value)->toBeInstanceOf(\__PHP_Incomplete_Class::class);
});

test('it can be converted to simple array', function () {
    $option = factory(Option::class)->create([
        'option_name' => 'foo',
        'option_value' => 'bar',
    ]);

    expect($option->toArray())->toEqualCanonicalizing(['foo' => 'bar']);
});

test('it can add new option using add static method', function () {
    $option = Option::add('foo', 'bar');

    expect($option->value)->toEqual('bar');
    expect($option->toArray())->toEqualCanonicalizing(['foo' => 'bar']);
});
