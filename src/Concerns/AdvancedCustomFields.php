<?php

namespace Pollora\Colt\Concerns;

use LogicException;

/**
 * Trait HasAcfFields
 *
 * @package Pollora\Colt\Traits
 * @author Junior Grossi <juniorgro@gmail.com>
 * @deprecated Colt ships no ACF integration: the Pollora\Colt\Acf\AdvancedCustomFields
 *             class this trait relied on was never part of the package. Read ACF values
 *             with get_field() or through the model's meta.
 */
trait AdvancedCustomFields
{
    /**
     * @return object
     * @throws LogicException
     */
    public function getAcfAttribute()
    {
        $class = 'Pollora\\Colt\\Acf\\AdvancedCustomFields';

        if (class_exists($class)) {
            return new $class($this);
        }

        throw new LogicException(sprintf(
            '%s::$acf needs %s, which Colt does not provide. Use get_field() or the model\'s meta instead.',
            static::class,
            $class
        ));
    }
}
