<?php

use Illuminate\Support\Facades\Event;

use Pollora\Colt\Tests\Unit\Model\FakePage;
use Pollora\Colt\Tests\Unit\Model\FakePost;
use Pollora\Colt\Tests\Unit\Model\Video;
use Pollora\Colt\Model\Post;

test('it still has post type', function () {
    /** @var Post $post */
    $post = factory(Post::class)->create([
        'post_type' => 'video',
    ]);

    expect($post)->toBeInstanceOf(Post::class);
});

test('it has custom instance name', function () {
    Post::registerPostType('video', Video::class);
    factory(Post::class)->create(['post_type' => 'video']);

    $post = Post::newest()->first();

    expect($post)->toBeInstanceOf(Video::class);
    expect($post->getPostType())->toEqual('video');
});

test('it has meta fields using custom class', function () {
    factory(Post::class)->create(['post_type' => 'fake_post']);
    $fake = Post::newest()->first();

    expect($fake)->toBeInstanceOf(FakePost::class);

    $fake->createMeta('foo', 'bar');

    expect($fake->meta->foo)->toEqual('bar');
});

test('it has custom instance using custom class builder', function () {
    Post::registerPostType('video', Video::class);
    factory(Post::class)->create(['post_type' => 'video']);

    $video = Video::first();

    expect($video)->toBeInstanceOf(Video::class);
    expect($video->post_type)->toEqual('video');
});

test('it has fire retrieved event using custom class builder', function () {
    Event::fake();
    Post::registerPostType('video', Video::class);
    factory(Post::class)->create(['post_type' => 'video']);

    Video::first();

    Event::assertDispatched('eloquent.retrieved: ' . Video::class, 1);
    Event::assertNotDispatched('eloquent.retrieved: ' . Post::class);
});

test('it is configurable by the config file', function () {
    factory(Post::class)->create(['post_type' => 'fake_post']);
    $post = Post::type('fake_post')->first();
    expect($post)->not->toBeNull();
    expect($post)->toBeInstanceOf(FakePost::class);

    factory(Post::class)->create(['post_type' => 'fake_page']);
    $post = Post::type('fake_page')->first();
    expect($post)->not->toBeNull();
    expect($post)->toBeInstanceOf(FakePage::class);
});
