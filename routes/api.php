<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ApplicationOrderController;
use App\Http\Controllers\TipController;
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
    Route::post('/manager/login',[ManagerController::class, 'check']);
    //反馈Tip
    Route::get('/tip/list/{page}',[TipController::class, 'getList']);
    Route::post('/tip/add',[TipController::class, 'publish']);
    Route::delete('/tip/{id}',[TipController::class, 'delete']);
    Route::put('/tip/{id}',[TipController::class, 'update']);
    //用户
    Route::get('/user/list/{page}',[UserController::class, 'getList']);
    Route::get('/worker/{id}',[UserController::class, 'getOneWorker']);
    Route::get('/company/{id}',[UserController::class, 'getOneCompany']);
    Route::put('/{id}/update',[UserController::class, 'updateStatus']);
    Route::post('/check',[UserController::class, 'check']);
    Route::post('/authenticate',[UserController::class, 'authenticate']);
    //应聘订单
    Route::post('/applicationOrder/add',[ApplicationOrderController::class, 'make']);
    Route::delete('/applicationOrder/{id}',[ApplicationOrderController::class, 'delete']);
    Route::put('/applicationOrder/{id}',[ApplicationOrderController::class, 'update']);
    Route::get('/applicationOrder/user/{uid}',[ApplicationOrderController::class, 'getMeList']);
    Route::get('/applicationOrder/workOrder/{wid}',[ApplicationOrderController::class, 'getWorkerList']);
    Route::get('/applicationOrder/publisher/{pid}',[ApplicationOrderController::class, 'getPublisherList']);
    //招聘订单
    Route::post('/workOrder/add',[WorkOrderController::class, 'publish']);
    Route::delete('/workOrder/{id}',[WorkOrderController::class, 'delete']);
    Route::put('/workOrder/{id}',[WorkOrderController::class, 'update']);
    Route::get('/workOrder/{id}',[WorkOrderController::class, 'getOneOrder']);
    Route::get('/workOrder/{cid}',[WorkOrderController::class, 'getMeList']);
    Route::get('/workOrder/list/{page}',[WorkOrderController::class, 'getList']);
});
