<?php

declare(strict_types=1);

namespace Pollora\Colt;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Class Database
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class Database
{
    /**
     * @var array
     */
    protected static $baseParams = [
        'driver' => 'mysql',
        'host' => 'localhost',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => 'wp_',
    ];

    public static function connect(array $params): Manager
    {
        $capsule = new Capsule;

        $params = array_merge(static::$baseParams, $params);
        $capsule->addConnection($params);
        $capsule->bootEloquent();

        return $capsule;
    }
}
