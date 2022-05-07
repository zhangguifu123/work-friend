<?php

namespace App\Http\Controllers;

use App\Models\ApplicationOrder;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class ApplicationOrderController extends Controller
{
    //
    public function make(Request $request){
        //通过路由获取前端数据，并判断数据格式
        $data = $this->_dataHandle($request);
        if (!is_array($data)) {
            return $data;
        }
        $data['status'] = 2;
        $data['publisher'] = $data['publisher_id'];
        $check = ApplicationOrder::query()->where([
            ['work_order_id', $data['work_order_id']],
            ['worker_id',     $data['worker_id']],
        ])->first();
        if ($check){
            return msg(8 , __LINE__);
        }
        $applicationOrder = new ApplicationOrder($data);
        if ($applicationOrder->save()) {
            return msg(0,$applicationOrder->id);
        }
        //未知错误
        return msg(4, __LINE__);
    }


    public function getMeList(Request $request){
        if (!$request->route('uid')) {
            return msg(3 , __LINE__);
        }
        $myApplicationOrder   = ApplicationOrder::query()->where('worker_id', $request->route('uid'))->get()->toArray();
        return msg(0, $myApplicationOrder);
    }

    public function getPublisherList(Request $request){
        if (!$request->route('pid')) {
            return msg(3 , __LINE__);
        }
        $myApplicationOrder   = ApplicationOrder::query()->where('publisher', $request->route('pid'))->get()->toArray();
        return msg(0, $myApplicationOrder);
    }

    public function getWorkerList(Request $request){
        if (!$request->route('wid')) {
            return msg(3 , __LINE__);
        }
        $myApplicationOrder   = ApplicationOrder::query()->where('work_order_id', $request->route('wid'))->get()->toArray();
        return msg(0, $myApplicationOrder);
    }

    /** 删除 */
    public function delete(Request $request)
    {
        $applicationOrder = ApplicationOrder::query()->find($request->route('id'));
        if (!$applicationOrder){
            return msg(11, __LINE__);
        };
        $applicationOrder->delete();
        return msg(0, __LINE__);
    }

    /** 修改 */
    public function update(Request $request)
    {
        if (!$request->input('status') || !$request->route('id')) {
            return msg(1, __LINE__);
        }
        //修改
        $applicationOrder = ApplicationOrder::query()->find($request->route('id'));
        if (!$applicationOrder) {
            return msg(4, __LINE__);
        }
        $applicationOrder->status = $request->input('status');
        $applicationOrder->save();
        return msg(0, __LINE__);
    }
    //检查函数
    private function _dataHandle(Request $request){
        //声明理想数据格式
        $mod = [
            "work_order_id"   => ["integer"],
            "worker_id"       => ["integer"],
            "publisher_id"    => ["string"],
            "recipient"       => ["string"],
        ];
        //是否缺失参数
        if (!$request->has(array_keys($mod))){
            return msg(1,__LINE__);
        }
        //提取数据
        $data = $request->only(array_keys($mod));
        //判断数据格式
        if (Validator::make($data, $mod)->fails()) {
            return msg(3, '数据格式错误' . __LINE__);
        };
        return $data;
    }
}
