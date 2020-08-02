<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as User1;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Passport\HasApiTokens;

/**
 * @method static create(array $array)
 * @method static count()
 * @method static truncate()
 * @method static where(string $string, $username)
 * @method static find(string $userId)
 * @property mixed type
 * @property mixed mobile
 * @property mixed id
 * @property mixed republishedVideos
 * @property mixed channelVideos
 * @property string password
 * @property mixed email
 * @property mixed updated_at
 * @property int verify_code
 * @property Carbon verified_at
 */
class User extends User1
{
    use Notifiable, HasApiTokens, SoftDeletes;

    //region types
    const TYPE_ADMIN = 'admin';
    const TYPE_USER = 'user';
    const TYPES = [self::TYPE_ADMIN, self::TYPE_USER];
    //endregion types

    // region model configs
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 'mobile', 'email', 'name', 'password', 'avatar', 'website', 'verify_code', 'verified_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'verify_code',
    ];


    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'verified_at' => 'datetime',
    ];
    // endregion model configs

    // region custom methods
    // for line 43 in class UserRepository.php for Passport package
    // for login by email or username or phone number
    // customize in method in passport but not method->
    // <-in passport while name method into if() working
    /**
     * پیدا کردن کاربر برای ورود به سیستم از طریق موبایل یا ایمیل
     * @param $username
     * @return mixed
     */
    public function findForPassport($username)
    {
        /** @var User $user */
        $user = static::withoutTrashed()
            ->where('mobile', $username)
            ->orWhere('email', $username)->first();
        return $user;
    }

    // endregion custom methods

    // region setters
    // for replace +98 from everything for example (0098 , 98 , 09 , ...)
    public function setMobileAttribute($value)
    {
        // for ten number at end to the first
        $mobile = to_valid_mobile_number($value);
        $this->attributes['mobile'] = $mobile;
    }

    // endregion setters

    // region relations
    // for save channel migration table for use in for relationship
    public function channel()
    {
        return $this->hasOne(channel::class);
    }

    // for categories
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    // for playlist
    public function playlists()
    {
        return $this->hasMany(Playlist::class);
    }

    // for videos favourites
    public function favouriteVideos()
    {
        return $this->hasManyThrough(
            Video::class,
            VideoFavourite::class,
            'user_id', // republishes_videos.user_id
            'id', // video.id
            'id', // user.id
            'video_id')  // republishes_video.video_id
        ->selectRaw('videos.*,1 as republished');
    }

    // for videos myVideo
    public function channelVideos()
    {
        return $this->hasMany(Video::class)
            ->selectRaw('*,0 as republished');
    }

    //for video_republishes
    public function republishedVideos()
    {
        return $this->hasManyThrough(
            Video::class,
            VideoRepublish::class,
            'user_id', // republishes_videos.user_id
            'id', // video.id
            'id', // user.id
            'video_id')  // republishes_video.video_id
        ->selectRaw('videos.*,1 as republished');
    }

    // for videos (myVideo and republishes video)
    public function videos()
    {
        return $this->channelVideos()
            ->union($this->republishedVideos());
    }

    // for relationShips by Comment
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // endregion relations

    // call policy class for video model
    public function isAdmin()
    {
        return $this->type === User::TYPE_ADMIN;
    }

    public function isBaseUser()
    {
        return $this->type === User::TYPE_USER;
    }
    // end call policy class for video model

    // for uses in ChannelService for follow user
    public function follow(User $user)
    {
        return UserFollowing::create([
            'user_id1' => $this->id,
            'user_id2' => $user->id,
        ]);
    }

    // for uses in ChannelService for unfollow user
    public function unfollow(User $user)
    {
        return UserFollowing::where([
            'user_id1' => $this->id,
            'user_id2' => $user->id,
        ])->delete();
    }

    // for uses in ChannelService users following me
    public function followers()
    {
        return $this->hasManyThrough(User::class,
            UserFollowing::class,
            'user_id2',
            'id',
            'id',
            'user_id1');
    }

    // for uses in ChannelService my following users
    public function followings()
    {
        return $this->hasManyThrough(User::class,
            UserFollowing::class,
            'user_id1',
            'id',
            'id',
            'user_id2');
    }

    // for function show() in videoService
    public function views()
    {
        return $this->belongsToMany(Video::class, 'video_views')
            ->withTimestamps();
    }

    // for override method for delete user's video and restore
    public static function boot()
    {
        parent::boot();
        static::deleting(function ($user) {
            /** @var User $user */
            $user->channelVideos()->delete();
            $user->playlists()->delete();
            //$user->comments()->delete();
        });
        static::restoring(function ($user) {
            /** @var User $user */
            /** @noinspection PhpUndefinedMethodInspection */
            $user->channelVideos()->restore();
            /** @noinspection PhpUndefinedMethodInspection */
            $user->playlists()->restore();
            //$user->comments()->restore();
        });
    }

}
