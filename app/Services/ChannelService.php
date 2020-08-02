<?php

namespace App\Services;

use App\channel;
use App\Http\Requests\Channel\StatisticsRequest;
use App\Http\Requests\Channel\UpdateChannelRequest;
use App\Http\Requests\Channel\UpdateSocialsRequest;
use App\Http\Requests\Channel\UploadBannerForChannelRequest;
use App\User;
use App\Video;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChannelService extends BaseService
{

    public static function updateChannelInfo(UpdateChannelRequest $request)
    {
        try {
            /** @var channel $channel */
            /** @var User $user */
            if ($channelId = $request->route('id')) {
                $channel = channel::findOrFail($channelId);
                $user = $channel->user;
            } else {
                $user = auth()->user();
                $channel = $user->channel;
            }
            DB::beginTransaction();
            $channel->name = $request->name;
            $channel->info = $request->info;
            $channel->save();
            $user->website = $request->website;
            $user->save();
            DB::commit();
            return response([
                'message' => 'Channel changes recorded successfully'
            ], 200);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return response([
                'message' => 'An error has occurred'
            ], 500);
        }
    }

    public static function uploadAvatarForChannel(UploadBannerForChannelRequest $request)
    {
        try {
            $banner = $request->file('banner');
            $fileName = md5(auth()->id()) . '-' . Str::random(15);
            Storage::disk('channel')->put($fileName, $banner->get());
            /** @var channel $channel */
            $channel = auth()->user()->channel;
            if ($channel->banner) {
                Storage::disk('channel')->delete($channel->banner);
            }
            $channel->banner = Storage::disk('channel')->path($fileName);
            $channel->save();
            return response([
                'banner' => Storage::disk('channel')->url($fileName)
            ], 200);
        } catch (Exception $exception) {
            return response([
                'message' => 'An error has occurred'
            ], 500);
        }
    }

    public static function updateSocials(UpdateSocialsRequest $request)
    {
        try {
            $socials = [
                'cloob' => $request->input('cloob'),
                'lenzor' => $request->input('lenzor'),
                'twitter' => $request->input('twitter'),
                'facebook' => $request->input('facebook'),
                'telegram' => $request->input('telegram'),
                'instagram' => $request->input('instagram'),
            ];
            auth()->user()->channel->update(['socials' => $socials]);
            return response([
                'message' => 'The data was successfully recorded'
            ], 200);
        } catch (Exception $exception) {
            Log::error($exception);
            return response([
                'message' => 'An error has occurred'
            ], 500);
        }
    }

    public static function statistics(StatisticsRequest $request)
    {
        $fromDate = now()->subDays($request->get('last_n_days', 7))->toDateString();
        $data = [
            'views' => [],
            'total_views' => 0,
            'total_followers' => $request->user()->followers()->count(),
            'total_videos' => $request->user()->channelVideos()->count(),
            'total_comment' => Video::channelComments($request->user()->id)
                ->selectRaw('comments.*')
                ->count(),//TODO: We need to get the number of unconfirmed comments
        ];
        Video::views($request->user()->id)
            ->whereRaw("date(video_views.created_at) >= '{$fromDate}'")
            ->selectRaw('date(video_views.created_at) as date, count(*) as views')
            ->groupBy(DB::raw('date(video_views.created_at)'))->get()
            ->each(function ($item) use (&$data) {
                $data['total_views'] += $item->views;
                $data['views'][$item->date] = $item->views;
            });
        return $data;
    }

}
