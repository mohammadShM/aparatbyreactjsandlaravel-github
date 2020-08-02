<?php

namespace App\Services;

use App\Exceptions\UserAlreadyRegisteredException;
use App\Http\Requests\Auth\RegisterNewUserRequest;
use App\Http\Requests\Auth\RegisterVerifyUserRequest;
use App\Http\Requests\Auth\ResendVerificationCodeRequest;
use App\Http\Requests\User\FollowingsUserRequest;
use App\Http\Requests\User\FollowUserRequest;
use App\Http\Requests\User\UnfollowUserRequest;
use App\Http\Requests\User\ChangeEmailRequest;
use App\Http\Requests\User\ChangeEmailSubmitRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\UnregisterUserRequest;
use App\User;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService extends BaseService
{

    const CHANGE_EMAIL_CACHE_KEY = 'change.email.for.user.';

    /**
     * @param RegisterNewUserRequest $request
     * @return ResponseFactory|Response
     * @throws UserAlreadyRegisteredException
     */
    public static function registerNewUser(RegisterNewUserRequest $request)
    {
        // for use try because save together user table and channel table  or not save both
        try {
            // DB => use for both save or both not create
            DB::beginTransaction();
            $field = $request->getFieldName();
            $value = $request->getFieldValue();
            // if user already registered must Let's break the registration procedure
            /** @noinspection PhpUndefinedMethodInspection */
            if ($user = User::where($field, $value)->first()) {
                // If my user has already completed her registration, I must give her an error (for woman)
                // If my user has already completed his registration, I must give him an error (for man)
                if ($user->verified_at) {
                    throw new UserAlreadyRegisteredException('You have already registered');
                }
                return response(['message' => 'The Activation code has already been sent to you'], 200);
            }
            // Created random code to send
            $code = random_verification_code();
            // for first save in user
            $user = User::create([
                // 'type' => User::TYPE_USER,  // remove code => because default set TYPE_USER in table database
                $field => $value,
                'verify_code' => $code,
            ]);
            //TODO: send email or sms at user
            Log::info('SEND-REGISTER_CODE-MESSAGE-TO-USER', ['code' => $code]);
            DB::commit();
            return response(['message' => 'Registered user temporarily', 'data' => $user], 200);
        } catch (Exception $exception) {
            DB::rollBack();
            if ($exception instanceof UserAlreadyRegisteredException) {
                throw $exception;
            }
            Log::error($exception);
            return response([
                'message' => 'An error has occurred'
            ]);
        }
    }

    /**
     * @param RegisterVerifyUserRequest $request
     * @return ResponseFactory|Response
     */
    public static function registerNewUserVerify(RegisterVerifyUserRequest $request)
    {
        $field = $request->has('email') ? 'email' : 'mobile';
        $code = $request->code;
        // If the user information is found in the cache correctly we will create the user
        /** @var User $user */
        /** @noinspection PhpUndefinedMethodInspection */
//        $user = User::where([
//            $field => $request->input($field),
//            'verify_code' => $code,
//        ])->first();
        $user = User::where(
            $field, $request->input($field)
        )->where('verify_code', $code)->first();
        if (empty($user)) {
            throw new ModelNotFoundException('No user found with the desired code');
        }
        $user->verify_code = null;
        $user->verified_at = now();
        $user->save();
        return response($user, 200);
    }

    /**
     * @param ResendVerificationCodeRequest $request
     * @return ResponseFactory|Response
     * @throws Exception
     */
    public static function resendVerificationCodeToUser(ResendVerificationCodeRequest $request)
    {
        $field = $request->getFieldName();
        $value = $request->getFieldValue();
        /** @var User $user */
        /** @noinspection PhpUndefinedMethodInspection */
        $user = User::where($field, $value)->whereNull('verified_at')->first();
        if (!empty($user)) {
            // Show how far past the present at created at in minutes
            // if under 60 minutes again code Previous sending for user
            // if more 60 minutes new code send in user
            $dateDiff = now()->diffInMinutes($user->updated_at);
            if ($dateDiff > config('auth.resent_verification_code_time_diff', 60)) {
                $user->verify_code = random_verification_code();
                $user->save();
            }
            //TODO: send again code for email or sms at user
            Log::info('RESEND-REGISTER_CODE-MESSAGE-TO-USER', ['code' => $user->verify_code]);
            return response([
                'message' => 'The code has been resent to you'
            ], 200);
        }
        throw new ModelNotFoundException('No user found with this profile or enabled already');
    }

    /**
     * تغییر ایمیل
     * @param ChangeEmailRequest $request
     * @return ResponseFactory|Response
     */
    public static function changeEmail(ChangeEmailRequest $request)
    {
        try {
            $email = $request->email;
            $userId = auth()->id();
            $code = random_verification_code();
            $expireDate = now()->addMinutes(config('auth.chane_email_cache_expiration', 1440));
            Cache::put(self::CHANGE_EMAIL_CACHE_KEY . $userId, compact('email', 'code'), $expireDate);
            //TODO: send email for user set in later
            Log::info('SEND-CHANGE-EMAIL-CODE', compact('code'));
            return response([
                'message' => "successfully send code in email please check your inbox email"
            ], 200);
        } catch (Exception $e) {
            Log::error($e);
            return response([
                'message' => "can't connect to server and an error has occurred"
            ], 500);
        }
    }

    /**
     * تغییر تایید ایمیل کاربر
     * @param ChangeEmailSubmitRequest $request
     * @return ResponseFactory|Response
     */
    public static function changeEmailSubmit(ChangeEmailSubmitRequest $request)
    {
        $userId = auth()->id();
        $cacheKey = self::CHANGE_EMAIL_CACHE_KEY . $userId;
        $cache = Cache::get($cacheKey);
        if (empty($cache) || $cache['code'] != $request->code) {
            return response([
                'message' => 'Invalid request'
            ], 400);
        }
        /** @var User $user */
        $user = auth()->user();
        $user->email = $cache['email'];
        $user->save();
        Cache::forget($cacheKey);
        return response([
            'message' => 'Email changed successfully'
        ], 200);
    }

    /**
     * for change password
     * @param ChangePasswordRequest $request
     * @return ResponseFactory|Response
     */
    public static function changePassword(ChangePasswordRequest $request)
    {
        try {
            /** @var User $user */
            $user = auth()->user();
            if (!Hash::check($request->old_password, $user->password)) {
                return response([
                    'message' => 'The password you entered does not match'
                ], 400);
            }
            $user->password = bcrypt($request->new_password);
            $user->save();
            return response([
                'message' => 'Password change successful'
            ], 200);
        } catch (Exception $exception) {
            Log::error($exception);
            return response([
                'message' => 'An error has occurred'
            ], 500);
        }
    }

    public static function follow(FollowUserRequest $request)
    {
        try {
            /** @var User $user */
            $user = $request->user();
            $user->follow($request->channel->user);
            return response(['message' => 'successfully followed channel user'], 200);
        } catch (Exception $exception) {
            dd($exception);
            Log::error($exception);
            return response([
                'message' => 'An error has occurred'
            ], 500);
        }
    }

    public static function unfollow(UnfollowUserRequest $request)
    {
        /** @var User $user */
        $user = $request->user();
        $user->unfollow($request->channel->user);
        return response(['message' => 'successfully followed channel user'], 200);
    }

    public static function followings(FollowingsUserRequest $request)
    {
        /** @var User $user */
        $user = $request->user();
        return $user->followings()->paginate();
    }

    public static function followers(FollowingsUserRequest $request)
    {
        /** @var User $user */
        $user = $request->user();
        return $user->followers()->paginate();
    }

    public static function unregister(UnregisterUserRequest $request)
    {
        try {
            DB::beginTransaction();
            $request->user()->delete();
            DB::commit();
            return response(['message' => 'The user was successfully disabled',
                'note' => 'To log in again, just log in once'], 200);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return response(['message' => 'It is not possible to delete a comment'], 500);
        }
    }

}
