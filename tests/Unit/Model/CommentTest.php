<?php

use Pollora\Colt\Model\Comment;
use Pollora\Colt\Model\Post;

test('it has the correct instance', function () {
    $comment = factory(Comment::class)->create();

    expect($comment)->not->toBeNull();
    expect($comment)->toBeInstanceOf(Comment::class);
});

test('its id is an integer', function () {
    $comment = factory(Comment::class)->create();

    expect($comment->comment_ID)->toBeInt();
});

test('it has approved scope', function () {
    factory(Comment::class)->create(['comment_approved' => 0]);
    $lastComment = Comment::orderBy('created_at', 'desc')->approved()->first();
    expect($lastComment)->toBeNull();

    $comment = factory(Comment::class)->create(['comment_approved' => 1]);
    $lastComment = Comment::orderBy('created_at', 'desc')->approved()->first();
    expect($lastComment)->not->toBeNull();
    expect($lastComment->comment_ID)->toEqual($comment->comment_ID);
    expect($lastComment->comment_author)->toEqual($comment->comment_author);
    expect($lastComment->comment_date)->toEqual($comment->comment_date);
});

test('it has post relation', function () {
    $comment = factory(Comment::class)->create();

    expect($post = $comment->post)->not->toBeNull();
    expect($post)->toBeInstanceOf(Post::class);
    expect($post->ID)->toBeInt();
});

test('it can query post by id', function () {
    $post = createPostWithComments();
    $comments = Comment::findByPostId($post->ID);

    expect($comments->count())->toEqual(2);
    expect($comments->first())->toBeInstanceOf(Comment::class);
    expect($comments->first()->post->ID)->toEqual($post->ID);
});

test('it has parent', function () {
    $comment = createCommentWithParent();

    expect($comment->original)->toBeInstanceOf(Comment::class);
    expect($comment->original->comment_ID)->toEqual($comment->comment_parent);
});

test('it is approved', function () {
    $comment = factory(Comment::class)->create();

    expect($comment->isApproved())->toBeBool();
    expect($comment->isApproved())->toBeTrue();
});

test('it can be a reply', function () {
    $comment = createCommentWithReplies();

    expect($comment->replies)->toHaveCount(3);
    expect($comment->replies->first())->toBeInstanceOf(Comment::class);
    expect($comment->replies->first()->isReply())->toBeBool();
    expect($comment->replies->first()->isReply())->toBeTrue();
});

test('it has replies', function () {
    $comment = createCommentWithReplies();

    expect($comment->hasReplies())->toBeTrue();
    expect($comment->hasReplies())->toBeBool();
});

test('it can have a different database connection name', function () {
    $comment = factory(Comment::class)->make();
    $comment->setConnection('foo');
    $comment->save();

    $post = factory(Post::class)->create();
    $comment->post()->associate($post);
    $comment->save();

    expect($comment->getConnectionName())->toEqual('foo');
    expect($comment->post->getConnectionName())->toEqual('foo');
});

test('it can have meta fields', function () {
    $comment = factory(Comment::class)->create();

    $comment->saveField('foo', 'bar');

    expect($comment->meta->foo)->toEqual('bar');
});

test('it can update meta', function () {
    $comment = factory(Comment::class)->create();
    $comment->saveMeta('foo', 'bar');

    expect($comment->meta->foo)->toEqual('bar');

    $comment->saveField('foo', 'baz');

    expect($comment->meta->foo)->toEqual('baz');
});

test('it has meta', function () {
    factory(Comment::class)->create()
        ->saveMeta('foo', 'bar');

    $comment = Comment::hasMeta('foo', 'bar')->first();

    expect($comment)->toBeInstanceOf(Comment::class);
});

/**
 * @return Post
 */
function createPostWithComments()
{
    $post = factory(Post::class)->create();

    $post->comments()->saveMany([
        factory(Comment::class)->make(),
        factory(Comment::class)->make(),
    ]);

    return $post;
}

/**
 * @return Comment
 */
function createCommentWithParent()
{
    return factory(Comment::class)->create([
        'comment_parent' => function () {
            return factory(Comment::class)->create()->comment_ID;
        }
    ]);
}

/**
 * @return Comment
 */
function createCommentWithReplies()
{
    $comment = factory(Comment::class)->create();

    $comment->replies()->saveMany([
        factory(Comment::class)->make(),
        factory(Comment::class)->make(),
        factory(Comment::class)->make(),
    ]);

    return $comment;
}
