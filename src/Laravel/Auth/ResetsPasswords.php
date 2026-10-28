<?php

declare(strict_types=1);

namespace Pollora\Colt\Laravel\Auth;

use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Auth;
use Pollora\Colt\Services\PasswordService;

/**
 * Trait ResetsPasswords
 *
 * @author Mickael Burguet <www.rundef.com>
 * @author Junior Grossi <juniorgro@gmail.com>
 */
trait ResetsPasswords
{
    /**
     * Reset the given user's password.
     *
     * @param  string  $password
     */
    protected function resetPassword(CanResetPassword $user, $password)
    {
        $user->user_pass = (new PasswordService)->makeHash($password);
        $user->save();

        $this->guard()->login($user);
    }

    /**
     * @return mixed
     */
    protected function guard()
    {
        return Auth::guard();
    }
}
