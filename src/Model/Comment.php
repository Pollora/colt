<?php

declare(strict_types=1);

namespace Pollora\Colt\Model;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Pollora\Colt\Concerns\CustomTimestamps;
use Pollora\Colt\Concerns\MetaFields;
use Pollora\Colt\Model;
use Pollora\Colt\Model\Builder\CommentBuilder;
use Pollora\Colt\Model\Collection\MetaCollection;

/**
 * Class Comment
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 *
 * @property-read Collection<int, Comment> $replies
 * @property-read MetaCollection $meta
 */
class Comment extends Model
{
    use CustomTimestamps;
    use MetaFields;

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
     * @param  int  $postId
     * @return Comment
     */
    public static function findByPostId($postId)
    {
        return (new static)
            ->where('comment_post_ID', $postId)
            ->get();
    }

    /**
     * @return BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'comment_post_ID');
    }

    /**
     * @return BelongsTo
     */
    public function parent()
    {
        return $this->original();
    }

    /**
     * @return BelongsTo
     */
    public function original()
    {
        return $this->belongsTo(Comment::class, 'comment_parent');
    }

    /**
     * @return HasMany
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
     * @param  Builder  $query
     * @return CommentBuilder
     */
    public function newEloquentBuilder($query)
    {
        return new CommentBuilder($query);
    }

    /**
     * @param  mixed  $value
     * @return void
     */
    public function setUpdatedAt($value)
    {
        //
    }
}
