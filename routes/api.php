<?php

use Illuminate\Support\Facades\Route;

/**
 * route group for maintenance auth routes
 **/
Route::group([], function ($router) {
    /** @var Route $router */
    // for custom token (override method) in passport
    Route::group(['namespace' => '\Laravel\Passport\Http\Controllers'], function ($router) {
        /** @var Route $router */
        $router->post('login', [
            'as' => 'auth.login',
            'middleware' => ['throttle'],
            'uses' => 'AccessTokenController@issueToken',
        ]);
    });
    // for register by email or mobile
    $router->post('register', [
        'as' => 'auth.register',
        'uses' => 'AuthController@register',
    ]);
    // for register-verify by email or mobile
    $router->post('register-verify', [
        'as' => 'auth.register.verify',
        'uses' => 'AuthController@registerVerify',
    ]);
    // for send again code for register-verify by email or mobile
    $router->post('resend-verification-code', [
        'as' => 'auth.register.resend.verification.code',
        'uses' => 'AuthController@resendVerificationCode',
    ]);
});
/**
 * route group as for user class
 **/
Route::group(['middleware' => ['auth:api'],], function ($router) {
    /** @var Route $router */
    // for change email
    $router->post('change-email', [
        'as' => 'change.email',
        'uses' => 'UserController@changeEmail'
    ]);
    // for verify change email
    $router->post('change-email-submit', [
        'as' => 'change.email.submit',
        'uses' => 'UserController@changeEmailSubmit'
    ]);
    // for change password
    $router->match(['post', 'put'], 'change-password', [
        'as' => 'password.change',
        'uses' => 'UserController@changePassword'
    ]);
    Route::group(['prefix' => 'user'], function ($router) {
        /** @var Route $router */
        // for follow user's user in user
        $router->match(['post', 'get'], '/{channel}/follow', [
            'as' => 'user.follow',
            'uses' => 'UserController@follow'
        ]);
        // for unfollow user's user in user
        $router->match(['post', 'get'], '/{channel}/unfollow', [
            'as' => 'user.unfollow',
            'uses' => 'UserController@unfollow'
        ]);
        // for show my list users following
        $router->get('/followings', [
            'as' => 'user.followings',
            'uses' => 'UserController@followings'
        ]);
        // for show my list users followers
        $router->get('/followers', [
            'as' => 'user.followers',
            'uses' => 'UserController@followers'
        ]);
        // for delete user
        $router->delete('/me', [
            'as' => 'user.unregister',
            'uses' => 'UserController@unregister'
        ]);
    });
});
/**
 * route group as for channel class
 **/
Route::group(['middleware' => ['auth:api'], 'prefix' => '/channel'], function ($router) {
    /** @var Route $router */
    // for update channel
    $router->put('/{id?}', [
        'as' => 'channel.update',
        'uses' => 'ChannelController@update'
    ]);
    // for upload image in channel
    $router->match(['post', 'put'], '/', [
        'as' => 'channel.upload.banner',
        'uses' => 'ChannelController@uploadBanner'
    ]);
    // for update socials in channel
    $router->match(['post', 'put'], '/socials', [
        'as' => 'channel.update.socials',
        'uses' => 'ChannelController@updateSocials'
    ]);
    // Statistics of channel visitors
    $router->get('/{statistics}', [
        'as' => 'channel.statistics',
        'uses' => 'ChannelController@statistics'
    ]);
});
/**
 * Route for video
 */
Route::group(['middleware' => [], 'prefix' => '/video'], function ($router) {
    /** @var Route $router */
    // Route that does not need login
    // for like video favourites
    $router->match(['get', 'post'], '/{video}/like', [
        'as' => 'video.like',
        'uses' => 'VideoController@like'
    ]);
    // for unlike video favourites
    $router->match(['get', 'post'], '/{video}/unlike', [
        'as' => 'video.unlike',
        'uses' => 'VideoController@unlike'
    ]);
    // for get all video at user
    $router->get('/', [
        'as' => 'video.list',
        'uses' => 'VideoController@list'
    ]);
    // Routes that users must login to access
    Route::group(['middleware' => ['auth:api']], function ($router) {
        /** @var Route $router */
        // for upload video
        $router->post('/upload', [
            'as' => 'video.upload',
            'uses' => 'VideoController@upload'
        ]);
        // for upload video banner
        $router->post('/upload-banner', [
            'as' => 'video.upload.banner',
            'uses' => 'VideoController@uploadBanner'
        ]);
        // for save video
        $router->post('/', [
            'as' => 'video.create',
            'uses' => 'VideoController@create'
        ]);
        // for publishes video
        $router->post('/{video}/republish', [
            'as' => 'video.republish',
            'uses' => 'VideoController@republish'
        ]);
        // for change state video
        $router->put('/{video}/state', [
            'as' => 'video.change.state',
            'uses' => 'VideoController@changeState'
        ]);
        // for update one video
        $router->put('/{video}', [
            'as' => 'video.change.update',
            'uses' => 'VideoController@update'
        ]);
        // for get all video favourites at user
        $router->get('/liked', [
            'as' => 'video.liked',
            'uses' => 'VideoController@likedByCurrentUser'
        ]);
        // for statistics views video
        $router->get('/{video}/statistics', [
            'as' => 'video.statistics',
            'uses' => 'VideoController@statistics'
        ]);
        // for show list videos favourites
        $router->get('/favourites', [
            'as' => 'video.favourites',
            'uses' => 'VideoController@favourites'
        ]);
        // for delete video at user
        $router->delete('/{video}', [
            'as' => 'video.delete',
            'uses' => 'VideoController@delete'
        ]);
    });
    // Statistics of video visitors
    $router->get('/{video}', [
        'as' => 'video.show',
        'uses' => 'VideoController@show'
    ]);
});
/**
 * for category
 */
Route::group(['middleware' => ['auth:api'], 'prefix' => '/category'], function ($router) {
    /** @var Route $router */
    // for show all categories
    $router->get('/', [
        'as' => 'category.all',
        'uses' => 'CategoryController@index'
    ]);
    // for show my categories
    $router->get('/my', [
        'as' => 'category.my',
        'uses' => 'CategoryController@my'
    ]);
    // for upload banner for  my category
    $router->post('/upload-banner', [
        'as' => 'category.upload.banner',
        'uses' => 'CategoryController@uploadBanner'
    ]);
    // for create my category
    $router->post('/', [
        'as' => 'category.create',
        'uses' => 'CategoryController@create'
    ]);
});
/**
 * for playlist
 */
Route::group(['middleware' => ['auth:api'], 'prefix' => '/playlist'], function ($router) {
    /** @var Route $router */
    // for show all playlists
    $router->get('/', [
        'as' => 'playlist.all',
        'uses' => 'PlaylistController@index'
    ]);
    // for show my playlists
    $router->get('/my', [
        'as' => 'playlist.my',
        'uses' => 'PlaylistController@my'
    ]);
    // for show one playlists
    $router->get('/{playlist}', [
        'as' => 'playlist.show',
        'uses' => 'PlaylistController@show'
    ]);
    // for create my playlist
    $router->post('/', [
        'as' => 'playlist.create',
        'uses' => 'PlaylistController@create'
    ]);
    // for sort videos in playlist
    $router->match(['post', 'put'], '/{playlist}/sort', [
        'as' => 'playlist.sort',
        'uses' => 'PlaylistController@sortVideos'
    ]);
    // for video add to playlist
    $router->match(['post', 'put'], '/{playlist}/{video}', [
        'as' => 'playlist.add-video',
        'uses' => 'PlaylistController@addVideo'
    ]);
});
/**
 * for tags
 */
Route::group(['middleware' => ['auth:api'], 'prefix' => '/tag'], function ($router) {
    /** @var Route $router */
    // for show all tags
    $router->get('/', [
        'as' => 'tag.all',
        'uses' => 'TagController@index'
    ]);
    // for create my tag
    $router->post('/', [
        'as' => 'tag.create',
        'uses' => 'TagController@create'
    ]);

});
/**
 * for comments
 */
Route::group(['middleware' => ['auth:api'], 'prefix' => '/comment'], function ($router) {
    /** @var Route $router */
    // for show all comment
    $router->get('/', [
        'as' => 'comment.all',
        'uses' => 'CommentController@index'
    ]);
    // for show all comment
    $router->post('/', [
        'as' => 'comment.create',
        'uses' => 'CommentController@create'
    ]);
    // for change state for comment
    $router->match(['post', 'put'], '/{comment}/state', [
        'as' => 'comment.change.state',
        'uses' => 'CommentController@changeState'
    ]);
    // for delete for comment
    $router->delete('/{comment}', [
        'as' => 'comment.delete',
        'uses' => 'CommentController@delete'
    ]);
});
