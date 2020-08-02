<?php

use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

/** @noinspection PhpUndefinedMethodInspection */
//Artisan::command('inspire', function () {
//    $this->comment(Inspiring::quote());
//})->describe('Display an inspiring quote');
/**
 * for delete video and image while migration: fresh => clear data
 */
/** @noinspection PhpUndefinedMethodInspection */
Artisan::command('aparat:clear', function () {
    clear_storage('videos');
    $this->info('Clear uploaded video files');
    clear_storage('category');
    $this->info('Clear uploaded category files');
    clear_storage('channel');
    $this->info('Clear uploaded channel files');
})->describe('Clear all temporary files,....');
