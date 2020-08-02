<?php

namespace App\Listeners;

use App\Events\VisitVideo;
use App\VideoView;
use Exception;
use Illuminate\Support\Facades\Log;

class AddVisitedVideoLogToViewsTable
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param VisitVideo $event
     * @return void
     */
    public function handle(VisitVideo $event)
    {
        try {
            $video = $event->getVideo();
            $clientIp = client_ip();
            $conditions = [
                'user_id' => auth('api')->id(),
                'video_id' => $video->id,
                // In order to record the video viewing time for each
                // person in the video_views table every day
                ['created_at', '>', now()->subDays(1)]
            ];
            if (!auth('api')->check()) {
                $conditions['user_ip'] = $clientIp;
            }
            /** @noinspection PhpUndefinedMethodInspection */
            if (!VideoView::where($conditions)->count()) {
                VideoView::create([
                    'user_id' => auth('api')->id(),
                    'video_id' => $video->id,
                    'user_ip' => $clientIp
                ]);
            }
            //$video->viewers()->attach(auth('api')->id());
        } catch (Exception $exception) {
            Log::error($exception);
        }
    }
}
