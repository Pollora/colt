<?php

namespace Pollora\Colt\Model;

use Pollora\Colt\Concerns\CustomTimestamps;
use Pollora\Colt\Concerns\MetaFields;
use Pollora\Colt\Model;
use Pollora\Colt\Model\Builder\CommentBuilder;

/**
 * Class Comment
 *
 * @package Pollora\Colt\Model
 * @author Junior Grossi <juniorgro@gmail.com>
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Pollora\Colt\Model\Comment> $replies
 * @property-read \Pollora\Colt\Model\Collection\MetaCollection $meta
 */
class Comment extends Model
{
    use MetaFields;
    use CustomTimestamps;

    const CREATED_AT = 'comment_date';

    const UPDATED_AT = null;

    /**
     * @var string
     */
    protected $table = 'comments';

    /**
     * @var string
     */
    protected $primaryKey = 'comment_ID';

    /**
     * @var array
     */
    protected $dates = ['comment_date'];

    /**
     * Find a comment by post ID.
     *
     * @param int $postId
     * @return Comment
     */
    public static function findByPostId($postId)
    {
        return (new static())
            ->where('comment_post_ID', $postId)
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'comment_post_ID');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->original();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function original()
    {
        return $this->belongsTo(Comment::class, 'comment_parent');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany(Comment::class, 'comment_parent');
    }

    public function isApproved(): bool
    {
        return $this->attributes['comment_approved'] == 1;
    }

    public function isReply(): bool
    {
        return $this->attributes['comment_parent'] > 0;
    }

    public function hasReplies(): bool
    {
        return $this->replies->count() > 0;
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @return CommentBuilder
     */
    public function newEloquentBuilder($query)
    {
        return new CommentBuilder($query);
    }

    /**
     * @param mixed $value
     * @return void
     */
    public function setUpdatedAt($value)
    {
        //
    }
}
