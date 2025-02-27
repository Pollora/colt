<?php

use Pollora\Colt\Model\Meta\UserMeta;

$factory->define(UserMeta::class, function (Faker\Generator $faker) {
    return [
        'user_id' => function () {
            return factory(\Pollora\Colt\Model\User::class)->create()->ID;
        },
        'meta_key' => $faker->word,
        'meta_value' => $faker->sentence(),
    ];
});
