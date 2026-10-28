<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\CanResetPassword;
use Pollora\Colt\Model\User;

test('get email for password reset', function () {
    /** @var CanResetPassword $user */
    $user = factory(User::class)->create();
    expect($user->getEmailForPasswordReset())->toEqual($user->user_email);
});

test('send password reset notification', function () {
    /** @var CanResetPassword $user */
    $user = factory(User::class)->create();
    expect($user->sendPasswordResetNotification('foo'))->toBeEmpty();
});
