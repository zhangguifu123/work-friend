<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Worker;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    //
    public function statistic (Request $request) {
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
        print_r($workerCount);

    }
}
