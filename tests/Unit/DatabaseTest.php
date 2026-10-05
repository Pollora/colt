<?php

use Pollora\Colt\Model\Post;

test('it uses the default database connection', function () {
    factory(Post::class)->create();

    $connection = config('database.default');
    $post = Post::newest()->first();

    expect($post->getConnectionName())->toEqual($connection);
});

test('it uses colt connection if it is present', function () {
    factory(Post::class)->create();
    $post = Post::newest()->first();
    expect($post)->toBeInstanceOf(Post::class);

    $this->app['config']->set('colt.connection', 'foo');

    $post = Post::newest()->first();
    expect($post)->toBeNull();

    $post = factory(Post::class)->create();
    expect($post->getConnectionName())->toEqual('foo');
});
