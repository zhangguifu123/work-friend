<?php

namespace App\Http\Controllers;

use App\Models\WorkerOrderCollection;
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
        $data['collection_count'] = 0;
        $data['status'] = 2;
        $data['salary'] = json_encode($data['salary']);
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
        if (!$request->input('workerId')){
            return msg(11, __LINE__);
        }
        $data     = $request->all();
        $workerId = $data['workerId'];

        //分页，每页10条
        $limit = 10;
        $offset = $request->route("page") * $limit - $limit;
        $workOrder = WorkOrder::query();
        if (!is_null($data['education'])) {
            $workOrder = $workOrder->whereIn('education', $data['education']);
        }
        $workOrderSum = $workOrder->count();
        $workOrderList = $workOrder
            ->limit(10)->where('order_type', $data['type'])
            ->leftJoin('workers', 'work_orders.openid', '=', 'workers.openid')
            ->leftJoin('companies', 'work_orders.openid', '=', 'companies.openid')
            ->offset($offset)->orderByDesc("work_orders.created_at")
            ->get([
                "companies.id as company_id", "workers.id as worker_id", "work_orders.id", "work_orders.openid" , "workers.name as worker_name", "workers.avatar as worker_avatar",
                "user_type", "order_type", "companies.name as company_name", "companies.avatar as company_avatar", "content", "place", "salary", "education", "dateline", "service_charge", "description", "collection_count",
                "work_orders.status",
            ])
            ->toArray();
        $newWorkOrderList = [];
        if (is_array($data['salary'])) {
            if ($data['type'] == 'partTime'){
                foreach ( $workOrderList as $workOrder ) {
                    if ( $data['salary']['max'] >= $workOrder['salary'] && $data['salary']['min'] <= $workOrder['salary'] ) {
                        $newWorkOrderList[] = $workOrder;
                    }
                }
            } else {
                foreach ( $workOrderList as $workOrder ) {
                    $workOrder['salary'] = json_decode($workOrder['salary'], true);
                    if ( $data['salary']['max'] >= $workOrder['salary']['max'] && $data['salary']['min'] <= $workOrder['salary']['min'] ) {
                        $workOrder['salary'] = json_encode($workOrder['salary'], true);
                        $newWorkOrderList[] = $workOrder;
                    }
                }
            }
        } else {
            $newWorkOrderList = $workOrderList;
        }
        $workOrderList = $this->_isCollection($workerId, $newWorkOrderList);
        $message['workOrderList'] = $workOrderList;
        $message['total']    = $workOrderSum;
        $message['limit']    = $limit;
        if (isset($message['token'])){
            return msg(13,$message);
        }
        return msg(0, $message);
    }

    private function _isCollection($workerId, $resumeList){
        $workOrderCollection = WorkerOrderCollection::query()->where('worker_id', $workerId)->get()->toArray();
        $collectionArray  = [];
        foreach ($workOrderCollection as $value){
            $collectionArray[$value['work_order_id']] = $value['id'];
        }
        $newWorkOrderList = [];
        foreach ($resumeList as $resume){
            if (array_key_exists($resume['id'], $collectionArray)) {
                $resume += ['isCollection' => 1, 'collectionId' => $collectionArray[$resume['id']]];
            } else {
                $resume += ['isCollection' => 0];
            };
            $newWorkOrderList[] = $resume;
        }
        return $newWorkOrderList;
    }

    public function getMeList(Request $request)
    {
        if (!$request->route('id')) {
            return msg(3 , __LINE__);
        }
        $workOrder   = WorkOrder::query()
            ->where('openid', $request->route('id'))->get()->toArray();
        return msg(0, $workOrder);
    }

    public function getOneOrder(Request $request)
    {
        if (!$request->route('id')) {
            return msg(3 , __LINE__);
        }
        $workOrder   = WorkOrder::query()
            ->leftJoin('workers', 'work_orders.openid', '=', 'workers.openid')
            ->leftJoin('companies', 'work_orders.openid', '=', 'companies.openid')
            ->get([
                "companies.id as company_id", "workers.id as worker_id", "work_orders.id", "work_orders.openid" , "workers.name as worker_name", "workers.avatar as worker_avatar",
                "user_type", "order_type", "companies.name as company_name", "companies.avatar as company_avatar", "content", "place", "salary", "education", "dateline", "service_charge", "description", "collection_count",
                "work_orders.status"
            ])
            ->find($request->route('id'));
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
        $workOrder->update($data);
        if ($workOrder) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }

    //检查函数
    private function _dataHandle(Request $request = null){
        //声明理想数据格式
        $mod = [
            "openid"       => ["string"],
            "user_type"    => ["string"],
            "order_type"   => ["string"],
            "content"      => ["string"],
            "place"        => ["string"],
            "salary"       => ["array"],
            "education"    => ["string"],
            "dateline"     => ["string"],
            "description"  => ["string", "nullable"],
            "service_charge" => ["double"],
            "status"       => ["integer", 'nullable'],
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
