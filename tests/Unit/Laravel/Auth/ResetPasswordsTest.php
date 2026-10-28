<?php

declare(strict_types=1);

use Pollora\Colt\Model\User;
use Pollora\Colt\Services\PasswordService;
use Pollora\Colt\Tests\Unit\Laravel\Auth\FakeController;

test('it resets password', function () {
    $user = factory(User::class)->create();
    $fake_class = new FakeController;

    $method = new ReflectionMethod($fake_class, 'resetPassword');
    $method->setAccessible(true);
    $method->invoke($fake_class, $user, 'bar');

    expect((new PasswordService)->check('bar', $user->user_pass))->toBeTrue();
});
