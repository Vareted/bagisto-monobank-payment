<?php

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::group([
    'as' => 'monobank.',
    'prefix' => 'monobank',
    'middleware' => ['web', 'theme', 'locale', 'currency'],
    'namespace' => 'Vareted\Monobank\Http\Controllers',
], function () {

    Route::get('redirect',  'MonobankController@redirect')->name('redirect');
    Route::post('webhook',  'MonobankController@webhook')->name('webhook')->withoutMiddleware([VerifyCsrfToken::class]);
    Route::get('result',  'MonobankController@result')->name('result');
});
