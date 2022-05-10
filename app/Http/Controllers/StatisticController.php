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
        $userCount = array_unique($userCount);
        //orderCount
        $orderCount = ApplicationOrder::query()->where('status', 1)->count();
        //income
        $incomeCount = WorkOrder::query()->leftJoin('application_orders', function ($join) {
           $join->on('application_orders.work_order_id', '=', 'work_orders.id')
                ->on('application_orders.status', '=', 1);
        })->sum('service_charge');
        print_r($incomeCount);
    }
}
