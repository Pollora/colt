<?php

declare(strict_types=1);

use Faker\Generator;
use Pollora\Colt\Model\Meta\CommentMeta;

$factory->define(CommentMeta::class, function (Generator $faker) {
    return [
        'comment_id' => $faker->numberBetween(1, 100),
        'meta_key' => $faker->word,
        'meta_value' => $faker->sentence(),
    ];
});
