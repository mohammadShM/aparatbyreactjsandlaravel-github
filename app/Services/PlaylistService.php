<?php

namespace App\Services;

use App\Http\Requests\Playlist\AddVideoToPlaylistRequest;
use App\Http\Requests\Playlist\CreatePlaylistRequest;
use App\Http\Requests\Playlist\ListMyPlaylistRequest;
use App\Http\Requests\Playlist\ListPlaylistRequest;
use App\Http\Requests\Playlist\ShowPlaylistRequest;
use App\Http\Requests\Playlist\SortVideosInPlaylistRequest;
use App\Playlist;
use Illuminate\Support\Facades\DB;

class PlaylistService extends BaseService
{

    public static function getAll(ListPlaylistRequest $request)
    {
        return Playlist::all();
    }

    public static function my(ListMyPlaylistRequest $request)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return Playlist::where('user_id', auth()->id())->get();
    }

    public static function create(CreatePlaylistRequest $request)
    {
        $data = $request->validated();
        $user = auth()->user();
        /** @noinspection PhpUndefinedMethodInspection */
        $playlist = $user->playlists()->create($data);
        return response([
            'data' => $playlist
        ], 200);
    }

    public static function addVideo(AddVideoToPlaylistRequest $request)
    {
        DB::table('playlist_videos')->where(['video_id' => $request->video->id])->delete();
        $request->playlist->videos()->syncWithoutDetaching($request->video->id);
        return response([
            'message' => 'The video has been successfully added to your playlist'
        ], 200);
    }

    public static function sortVideos(SortVideosInPlaylistRequest $request)
    {
        $request->playlist->videos()->detach($request->videos);
        $request->playlist->videos()->attach($request->videos);
        return response(['message' => 'The playlist was successfully sorted'], 200);
    }

    public static function show(ShowPlaylistRequest $request)
    {
        return Playlist::with('videos')
            ->find($request->playlist->id);
    }

}
