<?php

declare(strict_types=1);

namespace Pollora\Colt\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Pollora\Colt\Model;

/**
 * Class TermRelationship.
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class TermRelationship extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $table = 'term_relationships';

    /**
     * @var array
     */
    protected $primaryKey = ['object_id', 'term_taxonomy_id'];

    /**
     * @return BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'object_id');
    }

    /**
     * @return BelongsTo
     */
    public function taxonomy()
    {
        return $this->belongsTo(Taxonomy::class, 'term_taxonomy_id');
    }
}
