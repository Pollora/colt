<?php

declare(strict_types=1);

namespace Pollora\Colt\Model;

use Pollora\Colt\Concerns\MetaFields;
use Pollora\Colt\Model;

/**
 * Class Term.
 *
 * @package Pollora\Colt\Model
 * @author Junior Grossi <juniorgro@gmail.com>
 *
 * @property-read \Pollora\Colt\Model\Collection\MetaCollection $meta
 */
class Term extends Model
{
    use MetaFields;

    /**
     * @var string
     */
    protected $table = 'terms';

    /**
     * @var string
     */
    protected $primaryKey = 'term_id';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function taxonomy()
    {
        return $this->hasOne(Taxonomy::class, 'term_id');
    }
}
