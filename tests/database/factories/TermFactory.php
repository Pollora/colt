<?php

declare(strict_types=1);

use Faker\Generator;
use Illuminate\Support\Str;
use Pollora\Colt\Model\Term;

$factory->define(Term::class, function (Generator $faker) {
    return [
        'name' => $name = $faker->word,
        'slug' => Str::slug($name),
        'term_group' => 0,
    ];
});
