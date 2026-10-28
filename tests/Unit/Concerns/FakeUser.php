<?php

declare(strict_types=1);

namespace Pollora\Colt\Tests\Unit\Concerns;

use Pollora\Colt\Concerns\CustomTimestamps;
use Pollora\Colt\Model\User;

class FakeUser extends User
{
    use CustomTimestamps;

    const CREATED_AT = 'foo_created';

    const UPDATED_AT = 'foo_updated';
}
