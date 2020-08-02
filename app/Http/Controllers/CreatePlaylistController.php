<?php

namespace App\Http\Controllers;

use App\Http\Requests\Playlist\ListMyPlaylistRequest;
use App\Http\Requests\Playlist\ListPlaylistRequest;
use App\Services\PlaylistService;
use Illuminate\Routing\Controller as BaseController;

class CreatePlaylistController extends BaseController
{

    public function index(ListPlaylistRequest $request)
    {
        return PlaylistService::getAll($request);
    }

    public function my(ListMyPlaylistRequest $request)
    {
        return PlaylistService::my($request);
    }

    public function create(ListMyPlaylistRequest $request)
    {
        return PlaylistService::my($request);
    }

}
