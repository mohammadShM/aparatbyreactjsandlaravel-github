<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\RegisterNewUserRequest;
use App\Http\Requests\Auth\RegisterVerifyUserRequest;
use App\Http\Requests\Auth\ResendVerificationCodeRequest;
use App\Services\UserService;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    /**
     * ثبت نام کاربر
     * User registration
     * @param RegisterNewUserRequest $request
     * @return ResponseFactory|Response
     */
    public function register(RegisterNewUserRequest $request)
    {
        return UserService::registerNewUser($request);
    }

    /**
     * تایید کد فعالسازی کاربر
     * Confirm user activation code
     * @param RegisterVerifyUserRequest $request
     * @return ResponseFactory|Response
     */
    public function registerVerify(RegisterVerifyUserRequest $request)
    {
        return UserService::registerNewUserVerify($request);
    }

    /**
     * ارسال مجدد کد به ایمیل یا شماره تلفن
     * @param ResendVerificationCodeRequest $request
     * @return ResponseFactory|Response
     * @throws Exception
     */
    public function resendVerificationCode(ResendVerificationCodeRequest $request)
    {
        return UserService::resendVerificationCodeToUser($request);
    }

}
