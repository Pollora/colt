<?php

use Pollora\Colt\Model\Meta\PostMeta;
use Pollora\Colt\Model\Post;

test('it has correct instance type', function () {
    $meta = factory(PostMeta::class)->create();

    expect($meta)->toBeInstanceOf(PostMeta::class);
});

test('its id is an integer', function () {
    $meta = factory(PostMeta::class)->create();

    expect($meta)->not->toBeNull();
    expect($meta->meta_id)->toBeInt();
});

test('it has post relation', function () {
    $meta = createMetaWithPost();

    expect($meta->post)->toBeInstanceOf(Post::class);
});

test('it has meta key and value', function () {
    $meta = factory(PostMeta::class)->create();

    expect($meta)->not->toBeNull();
    expect($meta->meta_key)->not->toBeNull();
    expect($meta->meta_value)->not->toBeNull();
});

test('its value has the same value than post meta value', function () {
    $meta = createMetaWithPost();

    $post = $meta->post;
    $key = $meta->meta_key;

    expect($post->meta->$key)->toEqual($meta->meta_value);
});

test('its value can be reached by value property', function () {
    $meta = factory(PostMeta::class)->create();

    expect($meta->value)->not->toBeNull();
    expect($meta->value)->toEqual($meta->meta_value);
});

test('its value can be serialized', function () {
    $meta = factory(PostMeta::class)->create();

    $meta->meta_value = serialize($expected = ['foo' => 'bar']);

    expect($meta->value)->toEqual($expected);
});

test('it has has meta scope', function () {
    $post = factory(Post::class)->create();
    $post->saveMeta('one', 'two');
    $post->saveMeta('three', 'four');

    $newPost = Post::hasMeta('one')->first();
    expect($newPost->ID)->toEqual($post->ID);

    $newPost = Post::hasMeta('one', 'two')->first();
    expect($newPost->ID)->toEqual($post->ID);
});

test('its has meta scope accepts array as parameter', function () {
    $post = factory(Post::class)->create();
    $post->saveMeta('one', 'two');
    $post->saveMeta('three', 'four');

    $newPost = Post::hasMeta(['one' => 'two'])->first();

    expect($newPost)->not->toBeNull();
    expect($newPost->title)->toEqual($post->title);
    expect($newPost->ID)->toEqual($post->ID);

    $newPost = Post::hasMeta([
        'one' => 'two',
        'three' => 'four',
    ])->first();

    expect($newPost)->not->toBeNull();
    expect($newPost->title)->toEqual($post->title);
    expect($newPost->ID)->toEqual($post->ID);
});

test('its has meta scope can have array with only values', function () {
    $post = factory(Post::class)->create();
    $post->saveMeta('one', 'two');
    $post->saveMeta('three', 'four');

    $newPost = Post::hasMeta(['one', 'three'])->first();

    expect($newPost)->not->toBeNull();
    expect($newPost->title)->toEqual($post->title);
    expect($newPost->ID)->toEqual($post->ID);
});

test('higher order functions can be executed', function () {
    $post = factory(Post::class)->create();
    $post->saveMeta('one', 'two');
    $post->saveMeta('three', 'four');

    expect([1, 2])->toEqual($post->meta->map->getQueueableId()->all());
    expect('two')->toEqual($post->meta->one);
});

/**
 * @return PostMeta
 */
function createMetaWithPost()
{
    return factory(PostMeta::class)->create([
        'post_id' => function () {
            return factory(Post::class)->create()->ID;
        },
    ]);
}
