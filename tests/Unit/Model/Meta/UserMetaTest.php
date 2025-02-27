<?php

namespace Pollora\Colt\Tests\Unit\Model\Meta;

use Pollora\Colt\Model\Meta\UserMeta;
use Pollora\Colt\Model\User;

/**
 * Class UserMetaTest
 *
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class UserMetaTest extends \Pollora\Colt\Tests\TestCase
{
    public function test_user_relation()
    {
        $user_meta = factory(UserMeta::class)->create();

        $this->assertInstanceOf(User::class, $user_meta->user);
    }
}
