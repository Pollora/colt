<?php

declare(strict_types=1);

use Faker\Generator;
use Pollora\Colt\Model\Meta\ThumbnailMeta;
use Pollora\Colt\Model\Post;

$factory->define(ThumbnailMeta::class, function (Generator $faker) {
    return [
        'meta_key' => '_thumbnail_id',
        'meta_value' => $faker->sentence(),
        'post_id' => function () {
            return factory(Post::class)->create()->ID;
        },
    ];
});
