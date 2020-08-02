<?php

namespace App\Policies;

use App\Comment;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param Comment $comment
     * @param null $state
     * @return bool
     */
    public function changeState(User $user, Comment $comment, $state = null)
    {

        return (
            (($comment->state == Comment::STATE_PENDING &&
                    ($state === Comment::STATE_READ ||
                        $state === Comment::STATE_ACCEPTED)) ||
                ($comment->state == Comment::STATE_READ &&
                    $state === Comment::STATE_ACCEPTED)) &&
            $user->channelVideos()->where('id', $comment->video_id)->count());
    }

    /**
     * @param User $user
     * @param Comment $comment
     * @return int
     */
    public function delete(User $user, Comment $comment)
    {
        return $user->channelVideos()->where('id', $comment->video_id)->count();
    }

}
