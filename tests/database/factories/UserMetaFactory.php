<?php

declare(strict_types=1);

use Faker\Generator;
use Pollora\Colt\Model\Meta\UserMeta;
use Pollora\Colt\Model\User;

$factory->define(UserMeta::class, function (Generator $faker) {
    return [
        'user_id' => function () {
            return factory(User::class)->create()->ID;
        },
        'meta_key' => $faker->word,
        'meta_value' => $faker->sentence(),
    ];
});
