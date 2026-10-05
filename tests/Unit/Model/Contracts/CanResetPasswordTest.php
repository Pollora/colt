<?php

use Pollora\Colt\Model\User;
use Illuminate\Contracts\Auth\CanResetPassword;

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
