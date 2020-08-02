<?php

namespace App\Http\Controllers;

use App\Http\Requests\Channel\StatisticsRequest;
use App\Http\Requests\Channel\UpdateChannelRequest;
use App\Http\Requests\Channel\UpdateSocialsRequest;
use App\Http\Requests\Channel\UploadBannerForChannelRequest;
use App\Services\ChannelService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;

class ChannelController extends Controller
{

    /**
     * @param UpdateChannelRequest $request
     * @return AuthorizationException|ResponseFactory|Response
     */
    public function update(UpdateChannelRequest $request)
    {
        return ChannelService::updateChannelInfo($request);
    }

    /**
     * @param UploadBannerForChannelRequest $request
     * @return ResponseFactory|Response
     */
    public function uploadBanner(UploadBannerForChannelRequest $request)
    {
        return ChannelService::uploadAvatarForChannel($request);
    }

    /**
     * @param UpdateSocialsRequest $request
     * @return ResponseFactory|Response
     */
    public function updateSocials(UpdateSocialsRequest $request)
    {
       return ChannelService::updateSocials($request);
    }

    public function statistics(StatisticsRequest $request)
    {
       return ChannelService::statistics($request);
    }

}
