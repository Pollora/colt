<?php

use Pollora\Colt\Model\Attachment;
use Pollora\Colt\Model\Meta\ThumbnailMeta;
use Pollora\Colt\Model\Post;

test('its meta belongs to post', function () {
    $meta = factory(ThumbnailMeta::class)->create();

    expect($meta->post)->toBeInstanceOf(Post::class);
});

test('its post has thumbnail relation', function () {
    $meta = factory(ThumbnailMeta::class)->create();

    $post = $meta->post;

    expect($post->thumbnail)->toBeInstanceOf(ThumbnailMeta::class);
});

test('it has an attachment', function () {
    $meta = createThumbnailMetaWithAttachment();

    expect($meta->attachment)->toBeInstanceOf(Attachment::class);
    expect($meta->post->image)->toBeString();
});

test('its post thumbnail attachment url is valid', function () {
    $post = createPostWithThumbnail();

    expect($post->thumbnail->attachment->url)->toEqual('http://google.com');
});

test('it has different sizes', function () {
    $meta = createThumbnailMetaWithAttachment();

    $thumbnail = $meta->size(ThumbnailMeta::SIZE_THUMBNAIL);

    expect($thumbnail['file'])->toEqual('foobar.jpg');
    expect($thumbnail['url'])->toEqual('http://example.com/foobar.jpg');
    expect($thumbnail['width'])->toEqual(150);
    expect($thumbnail['height'])->toEqual(150);
    expect($thumbnail['mime-type'])->toEqual('image/jpeg');
});

test('it returns full size for unknown size', function () {
    $meta = createThumbnailMetaWithAttachment();

    $fullSize = $meta->size(ThumbnailMeta::SIZE_FULL);
    $unknownSize = $meta->size('unknown');

    expect($unknownSize)->toEqual($fullSize);
});

function createThumbnailMetaWithAttachment(): ThumbnailMeta
{
    $attachment = factory(Attachment::class)->create();
    saveThumbnailSizes($attachment);

    $meta = factory(ThumbnailMeta::class)->create([
        'meta_value' => $attachment->ID,
    ]);

    $meta->attachment()->associate($attachment);

    return $meta;
}

function createPostWithThumbnail(): Post
{
    $thumbnail = factory(ThumbnailMeta::class)->create([
        'meta_value' => function () {
            return factory(Attachment::class)->create([
                'guid' => 'http://google.com',
            ])->ID;
        },
    ]);

    return $thumbnail->post;
}

function saveThumbnailSizes(Attachment $attachment): Attachment
{
    $attachment->saveMeta('_wp_attachment_metadata', serialize([
        'sizes' => [
            'thumbnail' => [
                'file' => 'foobar.jpg',
                'width' => 150,
                'height' => 150,
                'mime-type' => 'image/jpeg',
            ],
        ],
    ]));

    return $attachment;
}
