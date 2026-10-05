<?php

use Carbon\Carbon;
use Pollora\Colt\Model\User;
use Pollora\Colt\Tests\Unit\Concerns\FakeUser;

test('it overrides the default timestamps fields', function () {
    $fake = new FakeUser();
    $fake->setCreatedAt($created_at = Carbon::now()->toDateTimeString());
    $fake->setUpdatedAt($updated_at = Carbon::now()->toDateTimeString());

    expect($fake->foo_created)->toEqual($created_at);
    expect($fake->foo_created_gmt)->toEqual($created_at);
    expect($fake->foo_updated_gmt)->toEqual($updated_at);
    expect($fake->foo_updated_gmt)->toEqual($updated_at);
});
