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
        $workerId = $request->input('workerId');
        //分页，每页10条
        $limit = 10;
        $offset = $request->route("page") * $limit - $limit;
        $workOrder = $this->_select($request);
        if (isset($workOrder['code'])){
            $fall = $workOrder;
            return $fall;
        }
        $workOrderSum = $workOrder->count();
        $workOrderList = $workOrder
            ->limit(10)
            ->leftJoin('workers', 'work_orders.openid', '=', 'workers.openid')
            ->leftJoin('companies', 'work_orders.openid', '=', 'companies.openid')
            ->offset($offset)->orderByDesc("work_orders.created_at")
            ->get([
                "companies.id as company_id", "workers.id as worker_id", "work_orders.id", "work_orders.openid" , "workers.name as worker_name", "workers.avatar as worker_avatar",
                "user_type", "order_type", "companies.name as company_name", "companies.avatar as company_avatar", "content", "place", "salary", "education", "dateline", "service_charge", "description", "collection_count",
                "work_orders.status",
            ])
            ->toArray();
        $workOrderList = $this->_isCollection($workerId, $workOrderList);
        $message['workOrderList'] = $workOrderList;
        $message['total']    = $workOrderSum;
        $message['limit']    = $limit;
        if (isset($message['token'])){
            return msg(13,$message);
        }
        return msg(0, $message);
    }
    private function _select(Request $request)
    {
        $workOrder = WorkOrder::query();
        $data = $request->input();
        switch (true) {
            case $request->input('order_type') == 'partTime';
                switch (true) {
                    case (is_null($data['salary']) && is_null($data['education'])):
                        $workOrder = $workOrder->where('order_type', 'partTime');
                        break;
                    case (is_null($data['salary']) && !is_null($data['education'])):
                        $workOrder = $workOrder->where('order_type', 'partTime')
                            ->whereIn('education', $data['education']);
                        break;
                    case (!is_null($data['salary']) && is_null($data['education'])):
                        $workOrder = $workOrder->where('order_type', 'partTime')
                            ->whereIn('salary', $data['salary']);
                        break;
                    case (!is_null($data['salary']) && !is_null($data['education'])):
                        $workOrder = $workOrder->where('order_type', 'partTime')
                            ->whereIn('salary', $data['salary'])
                            ->whereIn('education', $data['education']);
                        break;
                    default :
                        return msg(4, __LINE__);
                }
                break;
            case $request->input('order_type') == 'fullTime';
                switch (true) {
                    case (is_null($data['education']) && is_null($data['salary']) && is_null($data['company_size'])):
                        $workOrder = $workOrder->where('order_type', 'fullTime');
                        break;
                    case (!is_null($data['education']) && !is_null($data['salary']) && !is_null($data['company_size'])):
                        $workOrder = $workOrder->where('order_type', 'fullTime')
                            ->whereIn('salary', $data['salary'])
                            ->whereIn('education', $data['education'])
                            ->whereIn('company_size', $data['company_size']);
                        break;
                    case (!is_null($data['education']) && is_null($data['salary']) && is_null($data['company_size'])):
                        $workOrder = $workOrder->where('order_type', 'fullTime')
                            ->whereIn('education', $data['education']);
                        break;
                    case (is_null($data['education']) && !is_null($data['salary']) && is_null($data['company_size'])):
                        $workOrder = $workOrder->where('order_type', 'fullTime')
                            ->whereIn('salary', $data['salary']);
                        break;
                    case (is_null($data['education']) && is_null($data['salary']) && !is_null($data['company_size'])):
                        $workOrder = $workOrder->where('order_type', 'fullTime')
                            ->whereIn('company_size', $data['company_size']);
                        break;
                    case (!is_null($data['education']) && !is_null($data['salary']) && is_null($data['company_size'])):
                        $workOrder = $workOrder->where('order_type', 'fullTime')
                            ->whereIn('salary', $data['salary'])
                            ->whereIn('education', $data['education']);
                        break;
                    case (is_null($data['education']) && !is_null($data['salary']) && !is_null($data['company_size'])):
                        $workOrder = $workOrder->where('order_type', 'fullTime')
                            ->whereIn('education', $data['education'])
                            ->whereIn('company_size', $data['company_size']);
                        break;
                    case (!is_null($data['education']) && is_null($data['salary']) && !is_null($data['company_size'])):
                        $workOrder = $workOrder->where('order_type', 'fullTime')
                            ->whereIn('salary', $data['salary'])
                            ->whereIn('company_size', $data['company_size']);
                        break;
                    default :
                        return msg(4, __LINE__);
                        break;
            default :
                return msg(4, __LINE__);
        }
    }

        return $workOrder;
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
            "salary"       => ["string"],
            "education"    => ["string"],
            "dateline"     => ["string"],
            "description"  => ["string", "nullable"],
            "service_charge" => ["string"],
            "status"       => ["integer"],
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
