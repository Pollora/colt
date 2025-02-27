<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Colt Database Connection Name
    |--------------------------------------------------------------------------
    |
    | By default, Colt uses your default database connection, set on
    | `config/database.php` (`default` key). Usually you'd like to use a
    | custom database just for WordPress. First you must configure that
    | database connection in `config/database.php`, and then set here its
    | name, like 'colt', for example. Then you can work with two or more
    | database, but this one is only for your WordPress tables.
    |
    */

    'connection' => 'colt',

    /*
    |--------------------------------------------------------------------------
    | Registered Custom Post Types
    |--------------------------------------------------------------------------
    |
    | WordPress allows you to create your own custom post types. Colt
    | makes querying posts using a custom post type easier, but here you can
    | set a list of your custom post types, and Colt will automatically
    | register all of them, making returning those custom classes, instead
    | of just Post objects.
    |
    */

    'post_types' => [
//        'video' => App\Models\Video::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Registered Shortcodes
    |--------------------------------------------------------------------------
    |
    | With Colt you can register as many shortcodes you want, but that's
    | usually made in runtime. Here it's the place to set all your custom
    | shortcodes to make Colt registering all of them automatically. Just
    | create your own shortcode class implementing `Colt\Shortcode` interface.
    |
    */

    'shortcodes' => [
//        'foo' => App\Shortcodes\FooShortcode::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Registered Shortcode Parser
    |--------------------------------------------------------------------------
    |
    | Colt uses the thunderer/shortcode library to parse shortcodes. Thunderer
    | provides three different parsers for shortcodes. You can use a
    | different parser if it suits your requirements better, or create your own.
    |
    */

    'shortcode_parser' => Thunder\Shortcode\Parser\RegularParser::class,
    // 'shortcode_parser' => Thunder\Shortcode\Parser\RegexParser::class,
    // 'shortcode_parser' => Thunder\Shortcode\Parser\WordpressParser::class,

];
