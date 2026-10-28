<?php

declare(strict_types=1);

use Faker\Generator;
use Pollora\Colt\Model\Menu;
use Pollora\Colt\Model\Term;

$factory->define(Menu::class, function (Generator $faker) {
    return [
        'taxonomy' => 'nav_menu',
        'description' => $faker->sentence(),
        'parent' => 0,
        'count' => 1,
        'term_id' => function () {
            return factory(Term::class)->create([
                'name' => 'Foo',
                'slug' => 'foo',
            ])->term_id;
        },
    ];
});
