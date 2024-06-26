<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ApplicationOrderController;
use App\Http\Controllers\TipController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\AvatarController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\StatisticController;
use App\Http\Controllers\AppealController;
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
    //统计
    Route::get('/statistics', [StatisticController::class, 'statistic']);
    //图片上传
    Route::post('/image', [ImageController::class, 'upload']);
    Route::post('/manager/login',[ManagerController::class, 'check']);
    Route::delete('/manager/{id}',[ManagerController::class, 'delete']);
    Route::get('/manager/list',[ManagerController::class, 'getList']);
    Route::post('/manager/add',[ManagerController::class, 'add']);
    //Collection
    Route::get('/collection/resume/{id}',[CollectionController::class, 'getCompanyCollectionList']);
    Route::get('/collection/workerOrder/{id}',[CollectionController::class, 'getWorkerCollectionList']);
    Route::post('/collection/resume',[CollectionController::class, 'addResumeCollection']);
    Route::post('/collection/workerOrder',[CollectionController::class, 'addWorkOrderCollection']);
    Route::delete('/collection/resume/{id}',[CollectionController::class, 'deleteResumeCollection']);
    Route::delete('/collection/workerOrder/{id}',[CollectionController::class, 'deleteWorkOrderCollection']);
    //Resume
    Route::post('/resume/list/{page}',[ResumeController::class, 'getList']);
    Route::get('/resume/me/{id}',[ResumeController::class, 'getMeList']);
    Route::post('/resume/add',[ResumeController::class, 'publish']);
    Route::delete('/resume/{id}',[ResumeController::class, 'delete']);
    Route::put('/resume/{id}',[ResumeController::class, 'update']);
    //Notice
    Route::get('/notice/me/{id}',[NoticeController::class, 'getList']);
    Route::post('/notice/add',[NoticeController::class, 'publish']);
    Route::delete('/notice/{id}',[NoticeController::class, 'delete']);
    Route::put('/notice/{id}',[NoticeController::class, 'update']);
    //申诉Appeal
    Route::get('/appeal/list/{page}',[AppealController::class, 'getList']);
    Route::get('/appeal/me/{id}',[AppealController::class, 'getMeList']);
    Route::post('/appeal/add',[AppealController::class, 'publish']);
    Route::delete('/appeal/{id}',[AppealController::class, 'delete']);
    Route::get('/appeal/{id}',[AppealController::class, 'getOneList']);
    Route::put('/appeal/{id}',[AppealController::class, 'update']);
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
    Route::put('/{id}/updateScore',[UserController::class, 'updateScore']);
    Route::post('/check',[UserController::class, 'check']);
    Route::post('/authenticate',[UserController::class, 'authenticate']);
    Route::post('/avatar',[AvatarController::class, 'upload']);

    //Banner
    Route::post('/banner/upload',[BannerController::class, 'upload']);
    Route::get('/banner/list/{page}',[BannerController::class, 'getList']);
    Route::delete('/banner/{id}',[BannerController::class, 'delete']);

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
    Route::get('/workOrder/me/{id}',[WorkOrderController::class, 'getMeList']);
    Route::post('/workOrder/list/{page}',[WorkOrderController::class, 'getList']);
});
