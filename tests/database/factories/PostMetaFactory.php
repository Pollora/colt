<?php

declare(strict_types=1);

use Faker\Generator;
use Pollora\Colt\Model\Meta\PostMeta;

$factory->define(PostMeta::class, function (Generator $faker) {
    return [
        'post_id' => $faker->numberBetween(1, 100),
        'meta_key' => $faker->word,
        'meta_value' => $faker->sentence(),
    ];
});
