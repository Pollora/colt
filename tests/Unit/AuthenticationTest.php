<?php

use Pollora\Colt\Laravel\Auth\AuthUserProvider;
use Pollora\Colt\Model\User;
use Pollora\Colt\Services\PasswordService;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->checker = new PasswordService();
    $this->provider = new AuthUserProvider();
});

test('it can check passwords', function () {
    expect($this->checker->check('admin', $this->checker->makeHash('admin')))->toBeTrue();
    expect($this->checker->check('admin', '$P$BrYiES.08ardK6pQme0LdlmQ0idrIe/'))->toBeTrue();
    expect($this->checker->check('rEn2b2N3TX', $this->checker->makeHash('rEn2b2N3TX')))->toBeTrue();

    expect($this->checker->check(
        '+0q?\'t&SBT\'*2VBk7UE(,uj6UG23Us',
        $this->checker->makeHash('+0q?\'t&SBT\'*2VBk7UE(,uj6UG23Us')
    ))->toBeTrue();
});

test('it can validate simple passwords', function () {
    $user = factory(User::class)->make([
        'user_pass' => $this->checker->makeHash('foobar'),
    ]);

    expect($this->provider->validateCredentials($user, ['password' => 'foobar']))->toBeTrue();
    expect($this->provider->validateCredentials($user, ['password' => 'foobaz']))->toBeFalse();
});

test('it can validate complex passwords', function () {
    $password = ')_)E~O79}?w+5"4&6{!;ct>656Lx~5';

    $user = factory(User::class)->make([
        'user_pass' => $this->checker->makeHash($password),
    ]);

    expect($this->provider->validateCredentials($user, compact('password')))->toBeTrue();
    expect($this->provider->validateCredentials($user, ['password' => $password.'a']))->toBeFalse();
});

test('it can authenticate users using auth facade with email', function () {
    factory(User::class)->create([
        'user_pass' => $this->checker->makeHash('correct-password'),
    ]);

    expect(Auth::validate([
        'email' => 'admin@example.com',
        'password' => 'correct-password',
    ]))->toBeTrue();

    expect(Auth::validate([
        'email' => 'admin@example.com',
        'password' => 'wrong-password',
    ]))->toBeFalse();
});

test('it can authenticate users using auth facade with username', function () {
    factory(User::class)->create([
        'user_pass' => $this->checker->makeHash('correct-password'),
    ]);

    expect(Auth::validate([
        'username' => 'admin',
        'password' => 'correct-password',
    ]))->toBeTrue();

    expect(Auth::validate([
        'username' => 'admin',
        'password' => 'wrong-password',
    ]))->toBeFalse();
});