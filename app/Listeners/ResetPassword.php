<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;

class ResetPassword
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(PasswordReset $event)
    {
        $user = $event->user;
        logActivity($user->id, 'User', config('constants.LOG_EVENTS.user_reset_password'), $user->id.' has successfully reset password');
    }
}
