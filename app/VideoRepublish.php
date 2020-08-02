<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @method static create(array $array)
 * @method static where(array $array)
 * @method static pluck(string $string)
 */
class VideoRepublish extends Pivot
{

    protected $table = 'video_republishes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'video_id'
    ];

}
