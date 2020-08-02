<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\FollowingsUserRequest;
use App\Http\Requests\User\FollowUserRequest;
use App\Http\Requests\User\UnfollowUserRequest;
use App\Http\Requests\User\ChangeEmailRequest;
use App\Http\Requests\User\ChangeEmailSubmitRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\UnregisterUserRequest;
use App\Services\UserService;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;

class UserController extends Controller
{

    /**
     * @param ChangeEmailRequest $request
     * @return ResponseFactory|Response
     */
    public function changeEmail(ChangeEmailRequest $request)
    {
        return UserService::changeEmail($request);
    }

    /**
     * @param ChangeEmailSubmitRequest $request
     * @return ResponseFactory|Response
     */
    public function changeEmailSubmit(ChangeEmailSubmitRequest $request)
    {
        return UserService::changeEmailSubmit($request);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        return UserService::changePassword($request);
    }

    public function follow(FollowUserRequest $request)
    {
        return UserService::follow($request);
    }

    public function unfollow(UnfollowUserRequest $request)
    {
        return UserService::unfollow($request);
    }

    public function followings(FollowingsUserRequest $request)
    {
        return UserService::followings($request);
    }

    public function followers(FollowingsUserRequest $request)
     {
         return UserService::followers($request);
     }

     public function unregister(UnregisterUserRequest $request)
     {
         return UserService::unregister($request);
     }

}
