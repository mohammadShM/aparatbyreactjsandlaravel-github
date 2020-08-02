<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static find($playlist)
 * @method static count()
 * @method static truncate()
 * @method static create(array $array)
 * @method static where(string $string, int|string|null $id)
 * @method static select()
 * @property mixed user_id
 */
class Playlist extends Model
{
    use SoftDeletes;

    protected $table = 'playlist';

    protected $fillable = ['user_id', 'title'];

    // region relations
    // for video
    public function videos()
    {
        return $this->belongsToMany(Video::class, 'playlist_videos')
            ->orderBy('playlist_videos.id');
    }

    // for user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // end region relations

    // override function for get count
    public function toArray()
    {
        $data = parent::toArray();
        $data['size'] = $this->videos()->count();
        return $data;
    }

}
