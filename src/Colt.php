<?php

namespace Pollora\Colt;

use Illuminate\Contracts\Foundation\Application;

/**
 * Class Colt
 *
 * @package Pollora\Colt
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class Colt
{
    /**
     * @return bool
     */
    public static function isLaravel()
    {
        return function_exists('app') && (
            app() instanceof Application ||
            strpos(app()->version(), 'Lumen') === 0
        );
    }
}
