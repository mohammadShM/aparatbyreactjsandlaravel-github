<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $remoteAddr = $_SERVER['REMOTE_ADDR'] .'-'.
        md5($_SERVER['HTTP_USER_AGENT']);
    dd($remoteAddr);
    //dd($_SERVER);
});
