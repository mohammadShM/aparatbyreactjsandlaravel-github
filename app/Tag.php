<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property mixed title
 * @property mixed id
 * @method static create(array $data)
 * @method static count()
 * @method static truncate()
 */
class Tag extends Model
{
    use SoftDeletes;

    protected $table = 'tags';

    protected $fillable = ['title'];

    public function videos()
    {
        return $this->belongsToMany(Video::class, 'video_tags');
    }

    // region override model method
    public function toArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title
        ];
    }
    // end region override model method

}
