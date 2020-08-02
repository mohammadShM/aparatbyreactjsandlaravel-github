<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @method static create(array $array)
 * @method static where(array $array)
 */
class VideoFavourite extends Pivot
{

    protected $table = 'video_favourites';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'video_id' ,'user_ip'
    ];

}
