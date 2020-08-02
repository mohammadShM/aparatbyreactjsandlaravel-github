<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static findOrFail(\Illuminate\Routing\Route|object|string $channelId)
 * @method static truncate()
 * @property mixed user
 * @property mixed banner
 */
class channel extends Model
{

    use SoftDeletes;

    protected $table = 'channels';

    protected $fillable = ['user_id', 'name', 'info', 'banner', 'socials'];

    // for save user migration table for use in for relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // for videos relationship and use function statistics in ChannelService
    public function videos()
    {
        return $this->user->videos();
    }

    public function setSocialsAttribute($value)
    {
        if (is_array($value)) $value = json_encode($value);
        $this->attributes['socials'] = $value;
    }

    public function getSocialsAttribute()
    {
        return json_decode($this->attributes['socials'], true);
    }

    // region override model methods =======================================================================
    public function getRouteKeyName()
    {
        return 'name';
    }
    // end region override model methods ===================================================================

}
