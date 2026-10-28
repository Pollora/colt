<?php

declare(strict_types=1);

use Faker\Generator;
use Pollora\Colt\Model\Option;

$factory->define(Option::class, function (Generator $faker) {
    return [
        'option_name' => $faker->word,
        'option_value' => $faker->sentence(),
        'autoload' => 'yes',
    ];
});
