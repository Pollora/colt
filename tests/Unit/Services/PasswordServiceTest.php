<?php

declare(strict_types=1);

use Pollora\Colt\Services\PasswordService;

it('hashes a password that it then accepts', function () {
    $service = new PasswordService;
    $hash = $service->makeHash('s3cret');

    expect($hash)->toStartWith('$P$')
        ->and($service->check('s3cret', $hash))->toBeTrue()
        ->and($service->check('wrong', $hash))->toBeFalse();
});

it('trims the password before hashing it', function () {
    $service = new PasswordService;

    expect($service->check('s3cret', $service->makeHash('  s3cret  ')))->toBeTrue();
});

it('still accepts legacy md5 hashes', function () {
    $service = new PasswordService;

    expect($service->check('legacy', md5('legacy')))->toBeTrue()
        ->and($service->check('other', md5('legacy')))->toBeFalse();
});
