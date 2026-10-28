<?php

namespace Pollora\Colt\Model\Collection;

use Illuminate\Database\Eloquent\Collection;

/**
 * Class MetaCollection
 *
 * @package Pollora\Colt\Model\Collection
 * @author Junior Grossi <juniorgro@gmail.com>
 *
 * @extends Collection<int, \Pollora\Colt\Model\Meta\Meta>
 */
class MetaCollection extends Collection
{
    /**
     * @param string $key
     * @return mixed
     * @throws \Exception
     */
    public function __get($key)
    {
        if (in_array($key, static::$proxies)) {
            return parent::__get($key);
        }

        if (isset($this->items) && count($this->items)) {
            $meta = $this->first(fn($meta): bool => $meta->meta_key === $key);

            return $meta ? $meta->meta_value : null;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function __isset(string $name)
    {
        return !is_null($this->__get($name));
    }
}
