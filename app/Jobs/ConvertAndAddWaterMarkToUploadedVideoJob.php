<?php

namespace App\Jobs;

use App\Video;
use FFMpeg\Filters\Video\CustomFilter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Pbmedia\LaravelFFMpeg\Media;

class ConvertAndAddWaterMarkToUploadedVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Video
     */
    private $video;
    /**
     * @var int
     */
    private $videoId;
    /**
     * @var int
     */
    private $userId;
    /**
     * @var bool
     */
    private $addWatermark;

    /**
     * Create a new job instance.
     *
     * @param Video $video
     * @param string $videoId
     * @param bool $addWatermark
     */
    public function __construct(Video $video, string $videoId, bool $addWatermark)
    {
        $this->video = $video;
        $this->videoId = $videoId;
        $this->addWatermark = $addWatermark;
        $this->userId = auth()->id();
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        $uploadedVideoPath = '/tmp/' . $this->videoId;
        /** @noinspection PhpUndefinedMethodInspection */
        if ($this->video->trashed() || !Video::where('id', $this->videoId)->count()) {
            Storage::disk('videos')->delete($uploadedVideoPath);
            return;
        }
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpFullyQualifiedNameUsageInspection */
        // for change name and format and save for video upload
        /**
         * @var Media $videoUploaded
         */
        $videoUploaded = \FFM::fromDisk('videos')->open($uploadedVideoPath);
        /** @noinspection PhpFullyQualifiedNameUsageInspection */
        /** @noinspection SpellCheckingInspection */
        $format = new \FFMpeg\Format\Video\x264('libmp3lame');
        // for watermark == true
        if ($this->addWatermark) {
            // for watermark
            /** @noinspection SpellCheckingInspection */
            $filters = new CustomFilter("drawtext=text='http\\://user{$this->userId}.com':
                           fontcolor=blue: fontsize=24: box=1: boxcolor=white@0.5: boxborderw=5:
                           x=10: y=(h - text_h - 10)");
            $videoUploaded = $videoUploaded->addFilter($filters);
        }
        $videoFile = $videoUploaded->export()
            ->toDisk('videos')
            ->inFormat($format);
        $videoFile->save($this->userId . '/' . $this->video->slug . '.mp4');
        $this->video->duration = $videoUploaded->getDurationInSeconds();
        // for state two while transfer video file move to user's folder
        $this->video->state = Video::STATE_CONVERTED;
        $this->video->save();
        Storage::disk('videos')->delete($uploadedVideoPath);
    }
}
