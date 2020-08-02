<?php

namespace App\Services;

use App\Events\DeleteVideo;
use App\Events\UploadNewVideo;
use App\Events\VisitVideo;
use App\Http\Requests\Video\ChangeStateVideoRequest;
use App\Http\Requests\Video\CreateVideoRequest;
use App\Http\Requests\Video\DeleteVideoRequest;
use App\Http\Requests\Video\FavouritesVideoListRequest;
use App\Http\Requests\Video\LikedByCurrentUserVideoRequest;
use App\Http\Requests\Video\LikeVideoRequest;
use App\Http\Requests\Video\ListVideoRequest;
use App\Http\Requests\Video\RepublishVideoRequest;
use App\Http\Requests\Video\ShowVideoRequest;
use App\Http\Requests\Video\ShowVideoStatisticsRequest;
use App\Http\Requests\Video\UnlikeVideoRequest;
use App\Http\Requests\Video\UpdateVideoRequest;
use App\Http\Requests\Video\UploadVideoBannerRequest;
use App\Http\Requests\Video\UploadVideoRequest;
use App\Playlist;
use App\User;
use App\Video;
use App\VideoFavourite;
use App\VideoRepublish;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoService extends BaseService
{

    public static function list(ListVideoRequest $request)
    {
        $user = auth('api')->user();
        if ($request->has('republished')) {
            if ($user) {
                $videos = $request->republished ? $user->republishedVideos() : $user->channelVideos();
            } else {
                $videos = $request->republished ? Video::whereRepublished() : Video::whereNotRepublished();
            }
        } else {
            $videos = $user ? $user->videos() : Video::query();
        }
        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $result = $videos->orderBy('id')->paginate();
        return $result;
    }

    public static function show(ShowVideoRequest $request)
    {
        event(new VisitVideo($request->video));
        /** @var Video $videoData */
        $videoData = $request->video->toArray();
        $conditions = [
            'video_id' => $request->video->id,
            'user_id' => auth('api')->check() ? auth('api')->id() : null,
        ];
        if (!auth('api')->check()) {
            $conditions['user_ip'] = client_ip();
        }
        /** @noinspection PhpUndefinedMethodInspection */
        $videoData['liked'] = VideoFavourite::where($conditions)->count();
        $videoData['tags'] = $request->video->tags;
        $videoData['comments'] = sort_comments($request->video->comments, null);
        // for show equals videos
        $videoData['related_videos'] = $request->video->related()->take(5)->get();
        $videoData['playlist'] = $request->video->playlist()
            ->with('videos')->first();
        return $videoData;
    }

    public static function upload(UploadVideoRequest $request)
    {
        try {
            $video = $request->file('video');
            $fileName = time() . Str::random(10);
            Storage::disk('videos')->put('/tmp/' . $fileName, $video->get());
            return response([
                'video' => $fileName
            ], 200);
        } catch (Exception $exception) {
            return response([
                'message' => 'An error has occurred'
            ], 500);
        }
    }

    public static function uploadBanner(UploadVideoBannerRequest $request)
    {
        try {
            $banner = $request->file('banner');
            $fileName = time() . Str::random(10) . '-banner';
            Storage::disk('videos')->put('/tmp/' . $fileName, $banner->get());
            return response([
                'banner' => $fileName
            ], 200);
        } catch (Exception $exception) {
            return response([
                'message' => 'An error has occurred'
            ], 500);
        }
    }

    public static function create(CreateVideoRequest $request)
    {
        try {
            DB::beginTransaction();
            // save video
            /** @var Video $video */
            $video = Video::create([
                'title' => $request->title,
                'user_id' => auth()->id(),
                'category_id' => $request->category,
                'channel_category_id' => $request->chanel_category,
                'slug' => '',
                'info' => $request->info,
                'duration' => 0,
                'banner' => null,
                'enable_comments' => $request->enable_comments,
                'publish_at' => $request->publish_at,
                // for state one while transfer video file move to tmp folder
                'state' => Video::STATE_PENDING,
            ]);
            // create slug uniq from id
            $video->slug = uniqId($video->id);
            $video->banner = $video->slug . '-banner';
            $video->save();
            // for call event and video file
            event(new UploadNewVideo($video, $request));
            // save and banner
            if ($request->banner) {
                Storage::disk('videos')->move('/tmp/' . $request->banner, auth()->id() . '/' . $video->banner);
            }
            // Assign video to playlist
            if ($request->playlist) {
                /** @var Playlist $playlist */
                $playlist = Playlist::find($request->playlist);
                $playlist->videos()->attach($video->id);
            }
            // sync tags
            if (!empty($request->tags)) {
                $video->tags()->attach($request->tags);
            }
            DB::commit();
            return response([
                'message' => 'Video Recorded successfully',
                'data' => $video
            ], 200);
        } catch (Exception $exception) {
            Log::error($exception);
            DB::rollBack();
            return response([
                'message' => 'An error has occurred'
            ], 500);
        }
    }

    public static function changeState(ChangeStateVideoRequest $request)
    {
        $video = $request->video;
        $video->state = $request->state;
        $video->save();
        return response($video, 200);
    }

    public static function republish(RepublishVideoRequest $request)
    {
        try {
            $videoRepublish = VideoRepublish::create([
                'user_id' => auth()->id(),
                'video_id' => $request->video->id
            ]);
            return response(['message' => 'successfully publisher video', "data" => $videoRepublish], 200);
        } catch (Exception $exception) {
            Log::error($exception);
            return response(['message' => 'An error has occurred'], 500);
        }
    }

    /**
     * @param LikeVideoRequest $request
     * @return ResponseFactory|Response
     * @throws Exception
     */
    public static function like(LikeVideoRequest $request)
    {
        VideoFavourite::create([
            'user_id' => auth('api')->id(),
            'user_ip' => client_ip(),
            'video_id' => $request->video->id,
        ]);
        return response(['message' => 'Your request has been successfully submitted'], 200);
    }

    public static function unlike(UnlikeVideoRequest $request)
    {
        $user = auth('api')->user();
        $conditions = [
            'video_id' => $request->video->id,
            'user_id' => $user ? $user->id : null,
        ];
        if (empty($user)) {
            $conditions['user_ip'] = Client_ip();
        }
        /** @noinspection PhpUndefinedMethodInspection */
        VideoFavourite::where($conditions)->delete();
        return response(['message' => 'Your request has been successfully submitted'], 200);
    }

    public static function likedByCurrentUser(LikedByCurrentUserVideoRequest $request)
    {
        /** @var User $user */
        $user = $request->user();
        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $videos = $user->favouriteVideos()
            ->paginate();
        return $videos;
    }

    public static function delete(DeleteVideoRequest $request)
    {
        try {
            DB::beginTransaction();
            /** @var Video $video */
            $video = $request->video;
            $video->forceDelete();
            event(new DeleteVideo($video));
            DB::commit();
            return response(['message' => 'successfully deleted video'], 200);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return response(['message' => 'Deletion was not performed'], 500);
        }
    }

    public static function statistics(ShowVideoStatisticsRequest $request)
    {
        $fromDate = now()->subDays($request->get('last_n_days', 7))->toDateString();
        /** @var Video $video */
        $video = $request->video;
        $data = [
            'views' => [],
            'total_views' => 0,
        ];
        Video::views($request->user()->id)
            ->where('videos.id', $video->id)
            ->whereRaw("date(video_views.created_at) >= '{$fromDate}'")
            ->selectRaw('date(video_views.created_at) as date, count(*) as value')
            ->groupBy(DB::raw('date(video_views.created_at)'))->get()
            ->each(function ($item) use (&$data) {
                $data['total_views'] += $item->value;
                $data['views'][$item->date] = $item->value;
            });
        return $data;
    }

    public static function update(UpdateVideoRequest $request)
    {
        try {
            DB::beginTransaction();
            // save video
            /** @var Video $video */
            $video = $request->video;
            if ($request->has('title')) $video->title = $request->title;
            if ($request->has('info')) $video->info = $request->info;
            if ($request->has('category')) $video->category = $request->category;
            if ($request->has('channel_category')) $video->channel_category = $request->channel_category;
            if ($request->has('enable_comments')) $video->enable_comments = $request->enable_comments;
            if ($request->has('banner')) $video->banner = $request->banner;
            if ($request->banner) {
                Storage::disk('videos')
                    ->delete(auth()->id() . '/' . $video->banner);
                Storage::disk('videos')
                    ->move('/tmp/' . $request->banner, auth()->id() . '/' . $video->banner);
            }
            // sync tags
            if (!empty($request->tags)) {
                $video->tags()->attach($request->tags);
            }
            DB::commit();
            return response([
                'message' => 'Video Recorded successfully',
                'data' => $video
            ], 200);
        } catch (Exception $exception) {
            Log::error($exception);
            DB::rollBack();
            return response([
                'message' => 'An error has occurred'
            ], 500);
        }
    }

    public static function favourites(FavouritesVideoListRequest $request)
    {
        /** @var Video $videos */
        $videos = $request->user()->favouriteVideos()
            ->selectRaw('videos.*,channels.name channel_name')
            ->leftJoin('channels', 'channels.user_id', '=', 'videos.user_id')
            ->get();
        return [
            'videos' => $videos,
            'total_fav_videos' => count($videos),
            'total_videos' => $request->user()->channelVideos()->count(),
            'total_comments' => Video::channelComments($request->user()->id)
                ->selectRaw('comments.*')
                ->count(),//TODO:I have to get the number of approved comments
            'total_views' => Video::views($request->user()->id)->count(),
        ];
    }

}
