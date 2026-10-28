<?php

declare(strict_types=1);

namespace Pollora\Colt;

use Illuminate\Contracts\Foundation\Application;

/**
 * Class Colt
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class Colt
{
    public static function isLaravel(): bool
    {
        return function_exists('app') && (
            app() instanceof Application ||
            str_starts_with(app()->version(), 'Lumen')
        );
    }
}
