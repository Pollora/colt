<?php

declare(strict_types=1);

namespace Pollora\Colt\Model\Meta;

use Pollora\Colt\Concerns\SafeUnserialize;
use Pollora\Colt\Model;
use Pollora\Colt\Model\Collection\MetaCollection;

/**
 * Class Meta
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 *
 * @property string $meta_key
 * @property mixed $meta_value
 */
abstract class Meta extends Model
{
    use SafeUnserialize;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $primaryKey = 'meta_id';

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
     * @return MetaCollection
     */
    public function newCollection(array $models = [])
    {
        return new MetaCollection($models);
    }
}
