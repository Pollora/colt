<?php

namespace Pollora\Colt\Tests\Unit\Laravel\Auth;

use Pollora\Colt\Laravel\Auth\ResetsPasswords;
use Pollora\Colt\Model\User;
use Pollora\Colt\Services\PasswordService;
use Pollora\Colt\Tests\TestCase;
use Illuminate\Routing\Controller;

class ResetPasswordsTest extends TestCase
{
    public function test_it_resets_password()
    {
        $user = factory(User::class)->create();
        $fake_class = new FakeController();

        $method = new \ReflectionMethod($fake_class, 'resetPassword');
        $method->setAccessible(true);
        $method->invoke($fake_class, $user, 'bar');

        $this->assertTrue((new PasswordService())->check('bar', $user->user_pass));
    }
}

class FakeController extends Controller
{
    use ResetsPasswords;
}
