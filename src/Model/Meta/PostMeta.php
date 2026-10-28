<?php

declare(strict_types=1);

namespace Pollora\Colt\Model\Meta;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Pollora\Colt\Model\Post;

/**
 * Class PostMeta
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class PostMeta extends Meta
{
    /**
     * @var string
     */
    protected $table = 'postmeta';

    /**
     * @var array
     */
    protected $fillable = ['meta_key', 'meta_value', 'post_id'];

    /**
     * @return BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
