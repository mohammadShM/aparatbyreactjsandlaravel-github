<?php

namespace App\Providers;

use App\Events\ActiveUnregisterUser;
use App\Events\DeleteVideo;
use App\Events\UploadNewVideo;
use App\Events\VisitVideo;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Laravel\Passport\Events\AccessTokenCreated;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        // for event me
        UploadNewVideo::class => [
            'App\Listeners\ProcessUploadedVideo'
        ],
        // for event me function = video delete
        DeleteVideo::class => [
            'App\Listeners\DeleteVideoData'
        ],
        // for event me function = show to videoService
        VisitVideo::class => [
            'App\Listeners\AddVisitedVideoLogToViewsTable'
        ],
        // use by class library passport (AccessTokenCreated===for library passport)
        AccessTokenCreated::class => [
            'App\Listeners\ActiveUnregisteredUserAfterLogin'
        ],
        // for event me function = user unregister to register
        ActiveUnregisterUser::class => [
            //TODO: What needs to be done after activating the user
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

//        Event::listen('*', function ($event) {
//            var_dump($event);
//        });

    }
}
