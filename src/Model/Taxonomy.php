<?php

declare(strict_types=1);

namespace Pollora\Colt\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Pollora\Colt\Model;
use Pollora\Colt\Model\Builder\TaxonomyBuilder;
use Pollora\Colt\Model\Meta\TermMeta;

/**
 * Class Taxonomy
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 *
 * @property string $taxonomy
 * @property-read Term|null $term
 */
class Taxonomy extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'term_taxonomy';

    /**
     * @var string
     */
    protected $primaryKey = 'term_taxonomy_id';

    /**
     * @var array
     */
    protected $with = ['term'];

    /**
     * @return HasMany
     */
    public function meta()
    {
        return $this->hasMany(TermMeta::class, 'term_id');
    }

    /**
     * @return BelongsTo
     */
    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    /**
     * @return BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Taxonomy::class, 'parent');
    }

    /**
     * @return BelongsToMany
     */
    public function posts()
    {
        return $this->belongsToMany(
            Post::class,
            'term_relationships',
            'term_taxonomy_id',
            'object_id'
        );
    }

    /**
     * @param  Builder  $query
     * @return TaxonomyBuilder
     */
    public function newEloquentBuilder($query)
    {
        return new TaxonomyBuilder($query);
    }

    /**
     * @return TaxonomyBuilder
     */
    public function newQuery()
    {
        return isset($this->taxonomy) && $this->taxonomy ?
            parent::newQuery()->where('taxonomy', $this->taxonomy) :
            parent::newQuery();
    }

    /**
     * Magic method to return the meta data like the post original fields.
     *
     * @param  string  $key
     * @return string
     */
    public function __get($key)
    {
        if (! isset($this->$key)) {
            if (isset($this->term->$key)) {
                return $this->term->$key;
            }
        }

        return parent::__get($key);
    }
}
