<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserController;

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

Route::namespace('Api')->group(function (){
    Route::get('/user/list/{page}',[UserController::class, 'getList']);
    Route::put('/{id}/update',[UserController::class, 'updateStatus']);
    Route::post('/check',[UserController::class, 'check']);
    Route::post('/authenticate',[UserController::class, 'authenticate']);
});
