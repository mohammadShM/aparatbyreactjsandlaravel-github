<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @method static create(array $array)
 * @method static where(array $array)
 */
class UserFollowing extends Pivot
{

    protected $table = 'followers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id1', 'user_id2'
    ];

}
