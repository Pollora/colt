<?php

declare(strict_types=1);

use Faker\Generator;
use Pollora\Colt\Model\Meta\TermMeta;
use Pollora\Colt\Model\Term;

$factory->define(TermMeta::class, function (Generator $faker) {
    return [
        'term_id' => function () {
            return factory(Term::class)->create()->term_id;
        },
        'meta_key' => $faker->word,
        'meta_value' => $faker->sentence(),
    ];
});
