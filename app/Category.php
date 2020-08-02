<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static count()
 * @method static truncate()
 * @method static create(array $array)
 * @method static where(string $string, $value)
 */
class Category extends Model
{
    use SoftDeletes;

    protected $table = 'categories';

    protected $fillable = [
        'title', 'icon', 'banner', 'user_id'
    ];

    // region relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // end region relations
}
