<?php

namespace App\Policies;

use App\User;
use App\Video;
use App\VideoFavourite;
use App\VideoRepublish;
use Illuminate\Auth\Access\HandlesAuthorization;

class VideoPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param Video|null $video
     * @return bool
     */
    public function changeState(User $user, Video $video = null)
    {
        return $user->isAdmin();
    }

    /**
     * @param User $user
     * @param Video|null $video
     * @return bool
     */
    public function republish(User $user, Video $video = null)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $video && $video->isAccepted() &&
            (
                $video->user_id != $user->id &&
                VideoRepublish::where([
                    'user_id' => $user->id,
                    'video_id' => $video->id
                ])->count() < 1
            );
    }

    /**
     * @param User|null $user
     * @param Video|null $video
     * @return bool
     */
    public function like(User $user = null, Video $video = null)
    {
        if ($video && $video->isAccepted()) {
            $conditions = [
                'video_id' => $video->id,
                'user_id' => $user ? $user->id : null,
            ];
            if (empty($user)) {
                $conditions['user_ip'] = Client_ip();
            }
            /** @noinspection PhpUndefinedMethodInspection */
            return VideoFavourite::where($conditions)->count() == 0;
        }
        return false;
    }

    /**
     * @param User|null $user
     * @param Video|null $video
     * @return bool
     */
    public function unlike(User $user = null, Video $video = null)
    {
        $conditions = [
            'video_id' => $video->id,
            'user_id' => $user ? $user->id : null,
        ];
        if (empty($user)) {
            $conditions['user_ip'] = Client_ip();
        }
        /** @noinspection PhpUndefinedMethodInspection */
        return VideoFavourite::where($conditions)->count();
    }

    /**
     * @param User $user
     * @param Video|null $video
     * @return bool
     */
    public function seeLikedList(User $user, Video $video = null)
    {
        return true;
    }

    public function delete(User $user, Video $video)
    {
        return $video->user_id === $user->id;
    }

    public function showStatistics(User $user, Video $video)
    {
        return $video->user_id === $user->id;
    }

    public function update(User $user ,Video $video)
    {
        return $video->user_id === $user->id;
    }

}
