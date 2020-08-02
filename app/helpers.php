<?php
//TODO: Paste the file into its own folder

// _h for helper function code

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

if (!function_exists('to_valid_mobile_number')) {

    /**
     * add +98 to first number phone
     *  number phone
     * @param string $mobile
     * @return string
     */
    function to_valid_mobile_number(string $mobile)
    {
        return $mobile = '+98' . substr($mobile, -10, 10);
    }

}

if (!function_exists('random_verification_code')) {

    /**
     * create random active code for registered
     * @return int
     * @throws Exception
     */
    function random_verification_code()
    {
        return random_int(100000, 999999);
    }

}

// for uniqId
if (!function_exists('uniqId')) {
    function uniqId(int $value)
    {
        $hash = new  Hashids\Hashids(env('APP_KEY'), 10);
        return $hash->encode($value);
    }
}

// for routes => console.php
if (!function_exists('clear_storage')) {
    function clear_storage(string $storageName)
    {
        try {
            Storage::disk($storageName)->delete(Storage::disk($storageName)->allFiles());
            foreach (Storage::disk($storageName)->allDirectories() as $dir) {
                Storage::disk($storageName)->deleteDirectory($dir);
            }
            return true;
        } catch (Exception $exception) {
            Log::error($exception);
            return false;
        }
    }
}

// for get client ip for row user_ip in video_favourites table
if (!function_exists('client_ip')) {
    function client_ip($withDate = false)
    {
        $ip = $_SERVER['REMOTE_ADDR'] . '-' . md5($_SERVER['HTTP_USER_AGENT']);
        if ($withDate) {
            // In order to record the video viewing time for each person in the video_views table every day
            $ip .= '-' . now()->toDateString();
        }
        return $ip;
    }
}

// for show method in VideoService table
if (!function_exists('sort_comments')) {
    function sort_comments($comments, $parentId = null)
    {
        $result = [];
        foreach ($comments as $comment) {
            if ($comment->parent_id === $parentId) {
                $data = $comment->toArray();
                $data['children'] = sort_comments($comments, $comment->id);
                $result[] = $data;
            }
        }
        return $result;
    }
}
