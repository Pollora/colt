<?php

declare(strict_types=1);

use Faker\Generator;
use Pollora\Colt\Model\Taxonomy;
use Pollora\Colt\Model\Term;

$factory->define(Taxonomy::class, function (Generator $faker) {
    return [
        'taxonomy' => $faker->word,
        'description' => $faker->sentence(),
        'parent' => 0,
        'count' => 1,
        'term_id' => function () {
            return factory(Term::class)->create([
                'name' => 'Bar',
                'slug' => 'bar',
            ])->term_id;
        },
    ];
});
