<?php

use Pollora\Colt\Model\Meta\UserMeta;
use Pollora\Colt\Model\User;

test('user relation', function () {
    $user_meta = factory(UserMeta::class)->create();

    expect($user_meta->user)->toBeInstanceOf(User::class);
});
