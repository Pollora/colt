<?php

use Pollora\Colt\Model\Option;

$factory->define(Option::class, function (Faker\Generator $faker) {
    return [
        'option_name' => $faker->word,
        'option_value' => $faker->sentence(),
        'autoload' => 'yes',
    ];
});
