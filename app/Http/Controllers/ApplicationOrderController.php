<?php

namespace App\Http\Controllers;

use App\Models\ApplicationOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use function PHPUnit\Framework\isEmpty;

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
            $check = ApplicationOrder::query()->where([
                ['work_order_id', $data['work_order_id']],
                ['worker_id',     $data['worker_id']],
                ['status',        4],
            ])->first();
            if ($check) {
                $check->status = 2;
                $check->update();
                return msg(0, $check->id);
            }
            return msg(8, __LINE__);
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
        $uid = $request->route('uid');
        $myApplicationOrder   = ApplicationOrder::query()->where('worker_id', $uid)
            ->leftJoin('companies', 'companies.id', '=', 'application_orders.publisher')
            ->leftJoin('workers', 'workers.id', '=', 'application_orders.publisher')
            ->leftJoin('work_orders', 'work_orders.id', '=', 'application_orders.work_order_id')
            ->get([
                'companies.name as publisher_company_name', 'companies.id as publisher_company_id', 'companies.avatar as publisher_company_avatar',
                'workers.name as publisher_worker_name', 'workers.id as publisher_worker_id', 'workers.avatar as publisher_worker_avatar',
                'work_orders.content', 'work_orders.order_type as work_order_type', 'work_orders.salary',
                'application_orders.id as id', 'publisher_type', 'application_orders.status as application_order_status', 'work_orders.status as work_order_status',
                'application_orders.work_order_id', 'application_orders.worker_id', 'application_orders.publisher', 'application_orders.recipient',
            ])->toArray();
        return msg(0, $myApplicationOrder);
    }

    public function getPublisherList(Request $request){
        if (!$request->route('pid')) {
            return msg(3 , __LINE__);
        }
        $pid = $request->route('pid');
        $myApplicationOrder   = ApplicationOrder::query()->where('publisher', $pid)
            ->leftJoin('workers', 'workers.id', '=', 'application_orders.worker_id')
            ->get()->toArray();
        return msg(0, $myApplicationOrder);
    }

    public function getWorkerList(Request $request){
        if (!$request->route('wid')) {
            return msg(3 , __LINE__);
        }
        $myApplicationOrder   = ApplicationOrder::query()->where('work_order_id', $request->route('wid'))
            ->leftJoin('workers', 'workers.id', '=', 'application_orders.worker_id')
            ->leftJoin('work_orders', 'work_orders.id', '=', 'application_orders.work_order_id')
            ->get([
                'workers.openid as openid', 'workers.name as worker_name', 'workers.id as worker_id', 'workers.avatar as worker_avatar',
                'work_orders.content', 'work_orders.order_type as work_order_type', 'work_orders.salary',
                'application_orders.id as id', 'publisher_type', 'application_orders.status as application_order_status', 'work_orders.status as work_order_status',
                'application_orders.work_order_id', 'application_orders.worker_id', 'application_orders.publisher', 'application_orders.recipient',
            ])->toArray();
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
        if (is_null($request->input('status'))|| !$request->route('id')) {
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
            "publisher_id"    => ["integer"],
            "publisher_type"  => ["integer"],
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
