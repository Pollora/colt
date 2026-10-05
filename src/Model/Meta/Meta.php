<?php

namespace Pollora\Colt\Model\Meta;

use Pollora\Colt\Concerns\SafeUnserialize;
use Pollora\Colt\Model;
use Pollora\Colt\Model\Collection\MetaCollection;

/**
 * Class Meta
 *
 * @package Pollora\Colt\Model\Meta
 * @author Junior Grossi <juniorgro@gmail.com>
 */
abstract class Meta extends Model
{
    use SafeUnserialize;

    /**
     * @var string
     */
    protected $primaryKey = 'meta_id';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $appends = ['value'];

    /**
     * @return mixed
     */
    public function getValueAttribute()
    {
        return $this->maybeUnserialize($this->meta_value);
    }

    /**
     * @param array $models
     * @return MetaCollection
     */
    public function newCollection(array $models = [])
    {
        return new MetaCollection($models);
    }
}
