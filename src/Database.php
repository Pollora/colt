<?php

namespace Pollora\Colt;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Class Database
 *
 * @package Pollora\Colt
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

    /**
     * @param array $params
     * @return \Illuminate\Database\Capsule\Manager
     */
    public static function connect(array $params)
    {
        $capsule = new Capsule();

        $params = array_merge(static::$baseParams, $params);
        $capsule->addConnection($params);
        $capsule->bootEloquent();

        return $capsule;
    }
}
