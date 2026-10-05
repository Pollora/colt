<?php

namespace Pollora\Colt\Tests\Unit\Concerns;

use Pollora\Colt\Concerns\CustomTimestamps;
use Pollora\Colt\Model\User;

class FakeUser extends User
{
    const CREATED_AT = 'foo_created';
    const UPDATED_AT = 'foo_updated';

    use CustomTimestamps;
}
