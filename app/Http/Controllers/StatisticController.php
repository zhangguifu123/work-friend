<?php

namespace App\Http\Controllers;

use App\Models\ApplicationOrder;
use App\Models\Company;
use App\Models\Worker;
use App\Models\WorkOrder;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    //
    public function statistic (Request $request) {
        //userCount
        $worker  = Worker::all();
        $workerCount = [];
        foreach ($worker as $value) {
            $workerCount[] = $value->openid;
        }
        $company = Company::all();
        $companyCount = [];
        foreach ($company as $value) {
            $companyCount[] = $value->openid;
        }
        $userCount = array_merge($companyCount, $workerCount);
        $userCount = sizeof(array_unique($userCount)) ;
        //orderCount
        $orderCount = ApplicationOrder::query()->where('status', 1)->count();
        //income
        $workOrderId = ApplicationOrder::query()->where('application_orders.status', '=', '1')->get('work_order_id')->toArray();
        $workOrderIds = [];
        foreach ($workOrderId as $value){
            $workOrderIds[] = $value['work_order_id'];
        }
        $incomeCount = WorkOrder::query()->whereIn('id', array_unique($workOrderIds))->sum('service_charge');
        $statistics['incomeCount'] = $incomeCount;
        $statistics['userCount']   = $userCount;
        $statistics['orderCount']  = $orderCount;
        return msg(0, $statistics);
    }
}
