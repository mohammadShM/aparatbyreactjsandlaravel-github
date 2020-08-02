<?php

namespace App\Observers;

use App\User;
use Illuminate\Support\Str;

class UserObserver
{
    /**
     * Handle the user "created" event.
     * @param User $user
     * @return void
     */
    public function created(User $user)
    {
        // if mobile without (+98) and else email after (@) save in name channel
        $channelName = !empty($user->email)
            ? Str::before($user->email, '@')
            : Str::after($user->mobile, '+98');
        // for relationship create channel table for user in time create user
        // save automatic user_id save in channel table for use relationship
        $user->channel()->create(['name' => $channelName]);
    }

    /**
     * Handle the user "updated" event.
     *
     * @param User $user
     * @return void
     */
    public function updated(User $user)
    {
        //
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param User $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the user "restored" event.
     *
     * @param User $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @param User $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
