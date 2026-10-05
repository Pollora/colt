<?php

use Pollora\Colt\Laravel\Auth\AuthUserProvider;
use Pollora\Colt\Model\User;
use Illuminate\Support\Str;

test('it can retrieve users by id', function () {
    $user = factory(User::class)->create();

    $provider = new AuthUserProvider();
    $new_user = $provider->retrieveById($user->ID);

    expect($new_user)->toEqual($user->fresh());
});

test('it can retrieve users by token', function () {
    /** @var User $user */
    $user = factory(User::class)->create();
    $user->saveMeta('remember_token', $token = Str::random());

    $provider = new AuthUserProvider();
    $new_user = $provider->retrieveByToken($user->ID, $token);

    expect($new_user)->toEqual($user->fresh());
});

test('it can update remember token', function () {
    $user = factory(User::class)->create();
    $provider = new AuthUserProvider();

    $provider->updateRememberToken($user, $token = Str::random());
    $new_user = $provider->retrieveByToken($user->ID, $token);

    expect($new_user)->toEqual($user->fresh());
});

test('it returns null if credentials do not match', function () {
    $provider = new AuthUserProvider();

    $user = $provider->retrieveByCredentials(['foo' => 'bar']);

    expect($user)->toBeNull();
});

test('it returns false if there is no password on validation', function () {
    $user = factory(User::class)->create();

    $provider = new AuthUserProvider();

    expect($provider->validateCredentials($user, ['username' => $user->username]))->toBeFalse();
});
