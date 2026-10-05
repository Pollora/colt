<?php

use Pollora\Colt\Model\Comment;
use Pollora\Colt\Model\Meta\CommentMeta;

test('comment relation', function () {
    $comment = factory(Comment::class)->create();
    $comment_meta = factory(CommentMeta::class)->create(['comment_id' => $comment->comment_ID]);

    expect($comment_meta->comment)->toBeInstanceOf(Comment::class);
});
