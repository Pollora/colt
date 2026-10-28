<?php

declare(strict_types=1);

namespace Pollora\Colt\Tests\Unit\Laravel\Auth;

use Illuminate\Routing\Controller;
use Pollora\Colt\Laravel\Auth\ResetsPasswords;

class FakeController extends Controller
{
    use ResetsPasswords;
}
