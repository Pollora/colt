<?php

use Pollora\Colt\Model\Attachment;
use Pollora\Colt\Model\Post;

test('it inherits aliases from parent', function () {
    $attachment = factory(Attachment::class)->create([
        'post_status' => 'foo',
        'post_content' => 'bar',
    ]);

    expect($attachment->status)->not->toBeNull();
    expect($attachment->content)->not->toBeNull();
    expect($attachment->wrong_property)->toBeNull();
});

test('it has aliases after to array', function () {
    $post = factory(Post::class)->create([
        'post_title' => 'Test title',
    ]);
    $array = $post->toArray();

    expect($array['main_category'])->toEqual('Uncategorized');
    expect($array['post_title'])->toEqual('Test title');
    expect($array)->toHaveKey('title');
    expect($array)->not->toHaveKey('wrong_key');
    expect($array['title'])->toEqual($array['post_title']);
});
