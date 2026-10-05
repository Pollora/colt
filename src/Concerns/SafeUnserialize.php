<?php

namespace Pollora\Colt\Concerns;

/**
 * Trait SafeUnserialize
 *
 * Unserializes values stored by WordPress without ever instantiating objects:
 * a serialized object comes back as __PHP_Incomplete_Class, never as a live
 * instance whose magic methods could run.
 *
 * @package Pollora\Colt\Concerns
 */
trait SafeUnserialize
{
    /**
     * @param mixed $value
     * @return mixed
     */
    protected function maybeUnserialize($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        $unserialized = @unserialize($value, ['allowed_classes' => false]);

        if ($unserialized === false && $value !== serialize(false)) {
            return $value;
        }

        return $unserialized;
    }
}
