<?php

namespace Pollora\Colt\Tests\Unit\Model\Meta;

use Pollora\Colt\Model\Comment;
use Pollora\Colt\Model\Meta\CommentMeta;

/**
 * Class CommentMetaTest
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class CommentMetaTest extends \Pollora\Colt\Tests\TestCase
{
    public function test_comment_relation()
    {
        $comment = factory(Comment::class)->create();
        $comment_meta = factory(CommentMeta::class)->create(['comment_id' => $comment->comment_ID]);

        $this->assertInstanceOf(Comment::class, $comment_meta->comment);
    }
}
