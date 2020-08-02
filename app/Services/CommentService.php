<?php

namespace App\Services;

use App\Comment;
use App\Http\Requests\Comment\ChangeCommentStateRequest;
use App\Http\Requests\Comment\CreateCommentRequest;
use App\Http\Requests\Comment\DeleteCommentRequest;
use App\Http\Requests\Comment\ListCommentRequest;
use App\User;
use App\Video;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommentService extends BaseService
{

    public static function index(ListCommentRequest $request)
    {
        /** @var Comment $comments */
        $comments = Comment::channelComments($request->user()->id);
        if ($request->has('state')) {
            /** @noinspection PhpUndefinedMethodInspection */
            $comments = $comments->where('comments.state', $request->state);
        }
        return $comments->get();
    }

    public static function create(CreateCommentRequest $request)
    {
        /** @var Video $video */
        $video = Video::find($request->video_id);
        /** @var User $user */
        $user = $request->user();
        /** @var Comment $comment */
        $comment = $user->comments()->create([
            'video_id' => $request->video_id,
            'parent_id' => $request->parent_id,
            'body' => $request->body,
            'state' => $video->user_id == $user->id
                ? Comment::STATE_ACCEPTED
                : Comment::STATE_PENDING,
        ]);
        return $comment;
    }

    public static function changeState(ChangeCommentStateRequest $request)
    {
        /** @var Comment $comment */
        $comment = $request->comment;
        $comment->state = $request->state;
        $comment->save();
        return response(['message' => 'The situation has changed successfully'], 200);
    }

    public static function delete(DeleteCommentRequest $request)
    {
        try {
            DB::beginTransaction();
            $request->comment->delete();
            DB::commit();
            return response(['message' => 'Deleting a comment was successful'], 200);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return response(['message' => 'It is not possible to delete a comment'], 500);
        }
    }

}
