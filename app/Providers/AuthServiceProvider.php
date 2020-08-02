<?php

namespace App\Providers;

use App\Comment;
use App\Playlist;
use App\Policies\CommentPolicy;
use App\Policies\PlaylistPolicy;
use App\Policies\UserPolicy;
use App\Policies\VideoPolicy;
use App\User;
use App\Video;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
        // call policy class for video model
        //\App\Video::class => \App\Policies\VideoPolicy::class,
        //'App\Video' => 'App\Policies\VideoPolicy',
        Video::class => VideoPolicy::class,
        User::class => UserPolicy::class,
        Comment::class => CommentPolicy::class,
        Playlist::class => PlaylistPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        // add routes passport
        // for set routes default package passport input project for use
        // Passport::routes();
        // add Expiry Time for token and refresh token
        // for token expire in 10 days (10days)===(24*60*10)===>minute
        Passport::tokensExpireIn(now()
            ->addMinutes(config('auth.token_expiration.token')));
        // for refresh token
        Passport::refreshTokensExpireIn(now()
            ->addMinutes(config('auth.token_expiration.refresh_token')));
        // call policy class for video model
        $this->registerGates();
    }

    // call policy class for video model
    private function registerGates()
    {
        Gate::before(function ($user, $ability) {
            // if (Gate::has('changeState')) {
            /** @var User $user */
            if ($user->isAdmin()) {
                return true;
            }
            // }
        });
    }

}
