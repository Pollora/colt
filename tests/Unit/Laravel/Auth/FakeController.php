<?php

namespace Pollora\Colt\Tests\Unit\Laravel\Auth;

use Pollora\Colt\Laravel\Auth\ResetsPasswords;
use Illuminate\Routing\Controller;

class FakeController extends Controller
{
    use ResetsPasswords;
}
