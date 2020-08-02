<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property mixed title
 * @property mixed id
 * @property mixed state
 * @property mixed video_id
 * @method static create(array $data)
 * @method static count()
 * @method static truncate()
 * @method static join(string $string, string $string1, string $string2, string $string3)
 * @method get()
 */
class Comment extends Model
{
    use SoftDeletes;

    // for state
    const STATE_PENDING = 'pending';
    const STATE_READ = 'read';
    const STATE_ACCEPTED = 'accepted';
    const STATES = [
        self::STATE_PENDING,
        self::STATE_READ,
        self::STATE_ACCEPTED,
    ];

    protected $table = 'comments';

    protected $fillable = ['user_id', 'video_id', 'parent_id', 'body', 'state'];

    public function videos()
    {
        return $this->belongsTo(Video::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    //region custom static methods
    public static function channelComments($userId)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return Comment::join('videos', 'comments.video_id', '=', 'videos.id')
            ->selectRaw('comments.*')
            ->where('videos.user_id', $userId);
    }
    //endregion custom static methods

    //for delete sub comments by delete super comment
    public function children()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    // for override method
    public static function boot()
    {
        parent::boot();
        static::deleting(function ($comment) {
            /** @var Comment $comment */
            $comment->children()->delete();
        });
    }

}
