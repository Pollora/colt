<?php

use Pollora\Colt\Model\User;
use Illuminate\Contracts\Auth\Authenticatable;

test('get auth identifier name', function () {
    /** @var Authenticatable $user */
    $user = factory(User::class)->create();
    expect($user->getAuthIdentifierName())->toEqual('ID');
});

test('get auth identifier', function () {
    /** @var Authenticatable $user */
    $user = factory(User::class)->create();
    expect($user->getAuthIdentifier())->toEqual($user->ID);
});

test('get auth password', function () {
    /** @var Authenticatable $user */
    $user = factory(User::class)->create(['user_pass' => 'secret']);
    expect($user->getAuthPassword())->toEqual('secret');
});

test('get remember token', function () {
    /** @var User $user */
    $user = factory(User::class)->create();
    $user->saveField('remember_token', 'foo');
    expect($user->getRememberToken())->toEqual('foo');
});
