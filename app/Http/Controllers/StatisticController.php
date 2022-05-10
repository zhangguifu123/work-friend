<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Worker;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    //
    public function statistic (Request $request ) {
        $workerCount  = Worker::query()->select('openid');
        $companyCount = Company::query()->select('openid');
        print_r($workerCount);

    }
}
