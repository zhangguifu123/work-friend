<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WorkOrderController extends Controller
{
    //发布订单
    public function publish(Request $request){
        //通过路由获取前端数据，并判断数据格式
        $data = $this->_dataHandle($request);
        if (!is_array($data)) {
            return $data;
        }
        $workOrder = new WorkOrder($data);

        if ($workOrder->save()) {
            return msg(0,$workOrder->id);
        }
        //未知错误
        return msg(4, __LINE__);
    }
    /** 拉取列表信息 */
    public function getList(Request $request)
    {
        //分页，每页10条
        $limit = 10;
        $offset = $request->route("page") * $limit - $limit;
        $workOrder = WorkOrder::query();
        $workOrderSum = $workOrder->count();
        $workOrderList = $workOrder
            ->limit(10)
            ->offset($offset)->orderByDesc("work_orders.created_at")
            ->get()
            ->toArray();
        $message['workOrderList'] = $workOrderList;
        $message['total']    = $workOrderSum;
        $message['limit']    = $limit;
        if (isset($message['token'])){
            return msg(13,$message);
        }
        return msg(0, $message);
    }

    public function getMeList(Request $request)
    {
        if (!$request->route('cid')) {
            return msg(3 , __LINE__);
        }
        $workOrder   = WorkOrder::query()->where('recipient', $request->route('cid'))->get()->toArray();
        return msg(0, $workOrder);
    }

    public function getOneOrder(Request $request)
    {
        if (!$request->route('id')) {
            return msg(3 , __LINE__);
        }
        $workOrder   = WorkOrder::query()->find($request->route('id'));
        return msg(0, $workOrder);
    }

    /** 删除 */
    public function delete(Request $request)
    {
        $workOrder = WorkOrder::query()->find($request->route('id'));
        if (!$workOrder){
            return msg(11, __LINE__);
        };
        $workOrder->delete();

        return msg(0, __LINE__);
    }

    /** 修改 */
    public function update(Request $request)
    {
        //通过路由获取前端数据，并判断数据格式
        $data = $this->_dataHandle($request);
        //如果$data非函数说明有错误，直接返回
        if (!is_array($data)) {
            return $data;
        }
        //修改
        $workOrder = WorkOrder::query()->find($request->route('id'));
        $workOrder = $workOrder->update($data);
        if ($workOrder) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }

    //检查函数
    private function _dataHandle(Request $request = null){
        //声明理想数据格式
        $mod = [
            "company_id"   => ["string"],
            "type"         => ["string"],
            "content"      => ["string"],
            "place"        => ["string"],
            "salary"       => ["string"],
            "education"    => ["string"],
            "dateline"     => ["string"],
            "description"  => ["string", "nullable"],
            "service_charge" => ["string"],
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