<?php

use Pollora\Colt\Model\Attachment;

test('it has aliases', function () {
    $attachment = createAttachmentWithMeta();

    expect($attachment->title)->toEqual($attachment->post_title);
    expect($attachment->url)->toEqual($attachment->guid);
    expect($attachment->type)->toEqual($attachment->post_mime_type);
    expect($attachment->description)->toEqual($attachment->post_content);
    expect($attachment->caption)->toEqual($attachment->post_excerpt);
    expect($attachment->alt)->toEqual($attachment->meta->_wp_attachment_image_alt);
});

test('its to array method has all appends property values', function () {
    $attachment = createAttachmentWithMeta();

    $array = $attachment->toArray();

    expect($array)->toHaveKey('title');
    expect($array)->toHaveKey('url');
    expect($array)->toHaveKey('type');
    expect($array)->toHaveKey('description');
    expect($array)->toHaveKey('caption');
    expect($array)->toHaveKey('alt');
});

function createAttachmentWithMeta()
{
    $attachment = factory(Attachment::class)->create();

    $attachment->saveMeta('_wp_attachment_image_alt', 'foobar');

    return $attachment;
}
