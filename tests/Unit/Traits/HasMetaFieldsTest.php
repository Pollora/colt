<?php

use Pollora\Colt\Model;
use Pollora\Colt\Model\Post;
use Pollora\Colt\Model\User;
use Illuminate\Support\Collection;

test('it can update meta', function () {
    $post = factory(Post::class)->create();
    $post->saveMeta('foo', 'bar');
    $post->saveMeta('foo', 'baz');

    $meta = $post->meta()->where('meta_key', 'foo')->first();

    expect($meta->meta_value)->toEqual('baz');
});

test('it can save multiples metas', function () {
    $user = factory(User::class)->create();

    $user->saveMeta([
        'foo' => 'bar',
        'fee' => 'baz',
    ]);

    expect($user->meta->foo)->toEqual('bar');
    expect($user->meta->fee)->toEqual('baz');
});

test('it can create multiples metas', function () {
    $user = factory(User::class)->create();

    $user->createMeta([
        'foo' => 'bar',
        'fee' => 'baz',
    ]);

    expect($user->meta->foo)->toEqual('bar');
    expect($user->meta->fee)->toEqual('baz');
    expect($user->meta->count())->toEqual(2);
});

test('it gets meta after creating meta', function () {
    $user = factory(User::class)->create();

    $metas = $user->createMeta(['foo' => 'bar']);
    expect($metas)->toBeInstanceOf(Collection::class);
    expect($metas->count())->toEqual(1);

    $meta = $user->createMeta('foo', 'bar');
    expect($meta)->toBeInstanceOf(Model::class);
});

test('it can get meta data from get meta method', function () {
    $user = factory(User::class)->create();

    $user->createMeta('foo', 'bar');

    expect($user->getMeta('foo'))->toEqual('bar');
});

test('it can check meta using has meta method', function () {
    factory(User::class)->create()->createMeta(['foo' => 'ba']);
    factory(User::class)->create()->createMeta(['foo' => 'bar']);
    factory(User::class)->create()->createMeta(['foo' => 'baz']);
    factory(User::class)->create()->createMeta(['foo' => 'BA']);

    /** @var Collection $users */
    $users = User::hasMeta(['foo' => 'ba'])->get();

    expect($users)->toBeInstanceOf(Collection::class);
    expect($users->count())->toEqual(1);
});

test('it can find users by meta like after creating meta', function () {
    factory(User::class)->create()->createMeta(['foo' => 'ba']);
    factory(User::class)->create()->createMeta(['foo' => 'bar']);
    factory(User::class)->create()->createMeta(['foo' => 'baz']);
    factory(User::class)->create()->createMeta(['foo' => 'BA']);

    /** @var Collection $users */
    $users = User::hasMetaLike(['foo' => 'ba'])->get();

    expect($users)->toBeInstanceOf(Collection::class);
    expect($users->count())->toEqual(2);
});

test('it can find users by meta like with wildcard after creating meta', function () {
    factory(User::class)->create()->createMeta(['foo' => 'ba']);
    factory(User::class)->create()->createMeta(['foo' => 'bar']);
    factory(User::class)->create()->createMeta(['foo' => 'baz']);

    /** @var Collection $users */
    $users = User::hasMetaLike(['foo' => 'ba%'])->get();

    expect($users)->toBeInstanceOf(Collection::class);
    expect($users->count())->toEqual(3);
});
