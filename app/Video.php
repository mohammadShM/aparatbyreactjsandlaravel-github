<?php

namespace App;

use Countable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * @method static create(array $array)
 * @method getDurationInSeconds()
 * @method static findOrFail()
 * @method static whereNotIn(string $string, $pluck)
 * @method static whereRaw(string $string, $toArray)
 * @method static find()
 * @method static selectRaw()
 * @property string slug
 * @property string banner
 * @property mixed id
 * @property mixed duration
 * @property mixed video_id
 * @property string state
 * @property mixed user_id
 * @property mixed title
 * @property mixed viewers
 * @property mixed info
 * @property mixed tags
 * @property mixed category
 * @property mixed channel_category
 * @property mixed enable_comments
 * @property mixed video_link
 * @property mixed banner_link
 */
class Video extends Model implements Countable
{
    use SoftDeletes;

    const STATE_PENDING = 'pending';
    const STATE_CONVERTED = 'converted';
    const STATE_ACCEPTED = 'accepted';
    const STATE_BLOCKED = 'blocked';
    const STATES = [self::STATE_PENDING, self::STATE_CONVERTED, self::STATE_ACCEPTED, self::STATE_BLOCKED];

    protected $table = 'videos';

    protected $fillable = [
        'title', 'user_id', 'category_id', 'channel_category_id', 'slug', 'info',
        'duration', 'banner', 'publish_at', 'enable_comments', 'state'
    ];

    // region relations =======================================================================
    public function playlist()
    {
        return $this->belongsToMany(Playlist::class, 'playlist_videos');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'video_tags');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // for relationShips by Comment
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // end relations ===========================================================================

    // region override model methods =======================================================================
    public function getRouteKeyName()
    {
        return 'slug';
    }
    // end region override model methods ====================================================================

    // for state video ============================
    public function isInState($state)
    {
        return $this->state === $state;
    }

    public function isPending()
    {
        return $this->isInState(self::STATE_PENDING);
    }

    public function isConverted()
    {
        return $this->isInState(self::STATE_CONVERTED);
    }

    public function isAccepted()
    {
        return $this->isInState(self::STATE_ACCEPTED);
    }

    public function isBlocked()
    {
        return $this->isInState(self::STATE_BLOCKED);
    }
    // end state video ======================

    //region getters
    public function getVideoLinkAttribute()
    {
        return Storage::disk('videos')->url($this->user_id
            . '/' . $this->slug . '.mp4');
    }

    public function getBannerLinkAttribute()
    {
        return Storage::disk('videos')->url($this->user_id
            . '/' . $this->slug . '-banner');
    }
    //endregion getters

    // override method =======================================================
    public function toArray()
    {
        $data = parent::toArray();
        $data['link'] = $this->video_link;
        $data['banner_link'] = $this->banner_link;
        /** @noinspection PhpUndefinedMethodInspection */
        $data['views'] = VideoView::where(['video_id' => $this->id])->count();
        //$data['comments'] = $this->tags;
        return $data;
    }

    // for video service in method list
    public static function whereNotRepublished()
    {
        return static::whereRaw('id not in (select video_id from video_republishes)', null);
    }

    // for video service in method list
    public static function whereRepublished()
    {
        return static::whereRaw('id in (select video_id from video_republishes)', null);
    }

    // To reduce the query by sending the viewers method when loading each video
    //protected $with = ['viewers'];

    // for function show() in videoService
    public function viewers()
    {
        //TODO: Add user data that is not yet login in statistics
        return $this->belongsToMany(User::class, 'video_views')
            ->withTimestamps();
    }

    /**
     * @param $userId
     * @return Builder
     */
    public static function views($userId)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return static::where('videos.user_id', $userId)
            ->join('video_views', 'videos.id', '=', 'video_views.video_id');
    }

    /**
     * @param $userId
     * @return Builder
     */
    // for function statistics comment video in channelService
    public static function channelComments($userId)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return static::where('videos.user_id', $userId)
            ->join('comments', 'videos.id', '=', 'comments.video_id');
    }

    // for function related videos if show video in VideoService
    public function related()
    {
        return static::selectRaw('COUNT(*) related_tags, videos.*')
            ->leftJoin('video_tags', 'videos.id', '=', 'video_tags.video_id')
            ->whereRaw('videos.id != ' . $this->id)
            ->whereRaw("videos.state = '" . self::STATE_ACCEPTED . "'")
            ->whereIn(DB::raw('video_tags.tag_id'), function ($query) {
                $query->selectRaw('video_tags.tag_id')
                    ->from('videos')
                    ->leftJoin('video_tags', 'videos.id', '=', 'video_tags.video_id')
                    ->whereRaw('videos.id = ' . $this->id);
            })
            ->groupBy(DB::raw('videos.id'))
            ->orderBy('related_tags', 'desc');
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
    }
}
