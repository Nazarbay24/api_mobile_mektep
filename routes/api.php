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
        Route::get('/get-schools', [\App\Http\Controllers\AuthController::class, 'getSchools']);

        Route::get('/choice-school/{id}', [\App\Http\Controllers\AuthController::class, 'choiceSchool'])->where('id', '[0-9]+');

        Route::get('/today-diary', [\App\Http\Controllers\DiaryController::class, 'todayDiary']);

        Route::get('/subject/{id}', [\App\Http\Controllers\SubjectController::class, 'subject'])->where('id', '[0-9]+');

        Route::get('/my-subjects', [\App\Http\Controllers\SubjectController::class, 'mySubjects']);

        Route::get('/diary/{week}', [\App\Http\Controllers\DiaryController::class, 'diary']);
    });

});


