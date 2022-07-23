<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => '{locale}', 'where' => ['locale' => '[a-zA-Z]{2}'], 'middleware' => 'setlocale'], function() {

    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);

    Route::group(['middleware' => ['jwt.verify']], function() {
        Route::get('/get-school', [\App\Http\Controllers\AuthController::class, 'getSchool']);

        Route::get('/choice-school/{id}', [\App\Http\Controllers\AuthController::class, 'choiceSchool'])->where('id', '[0-9]+');
    });

});

//Route::prefix('{locale}')->group(function () {
//    Route::get('/choice-school/{id}', [\App\Http\Controllers\AuthController::class, 'choiceSchool']);
//});
//
//Route::get('/choice-school/{id}', [\App\Http\Controllers\AuthController::class, 'choiceSchool']);


