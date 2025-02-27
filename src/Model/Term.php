<?php

namespace Pollora\Colt\Model;

use Pollora\Colt\Concerns\AdvancedCustomFields;
use Pollora\Colt\Concerns\MetaFields;
use Pollora\Colt\Model;

/**
 * Class Term.
 *
 * @package Pollora\Colt\Model
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class Term extends Model
{
    use MetaFields;
    use AdvancedCustomFields;

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
