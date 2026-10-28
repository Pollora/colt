<?php

declare(strict_types=1);

namespace Pollora\Colt\Model;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Pollora\Colt\Concerns\MetaFields;
use Pollora\Colt\Model;
use Pollora\Colt\Model\Collection\MetaCollection;

/**
 * Class Term.
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 *
 * @property-read MetaCollection $meta
 */
class Term extends Model
{
    use MetaFields;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'terms';

    /**
     * @var string
     */
    protected $primaryKey = 'term_id';

    /**
     * @return HasOne
     */
    public function taxonomy()
    {
        return $this->hasOne(Taxonomy::class, 'term_id');
    }
}
