<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\ResumeCollection;
use App\Models\WorkerOrderCollection;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CollectionController extends Controller
{
    //发布
    public function addResumeCollection(Request $request){
        //通过路由获取前端数据，并判断数据格式
        $data = $this->_dataCompanyHandle($request);
        if (!is_array($data)) {
            return $data;
        }
        $resume = Resume::query()->find($data['resume_id'] );
        if (!$resume){
            return msg(11, __LINE__);
        }
        $resumeCollection = new ResumeCollection($data);
        if ($resumeCollection->save()) {
            $resume->increment('collection_count');
            return msg(0,['collectionId' => $resumeCollection->id]);
        }
        //未知错误
        return msg(4, __LINE__);
    }
    /** 拉取列表信息 */
    public function getCompanyCollectionList(Request $request)
    {
        if (!$request->route('id')) {
            return msg(3 , __LINE__);
        }
        $cid = $request->route('id');
        $resumeList   = ResumeCollection::query()->where('uid', $cid)
            ->leftJoin('resumes', function ($join) use ($cid) {
                $join->on('resumes.id', '=', 'resume_collections.resume_id');
            })
            ->get([
                "resume_collections.id as collectionId", "resumes.openid", "avatar", "resumes.name", "sex", "age", "education" ,"phone","salary", "position", "posts.created_at", "city", "education_experience", "internship_experience", "project_experience", "self_assessment"
            ])
            ->toArray();
        return msg(0, $resumeList);
    }

    /** 删除 */
    public function deleteResumeCollection(Request $request)
    {
        if (!$request->route('id') || !$request->input('resumeId')) {
            return msg(3 , __LINE__);
        }
        $resume = Resume::query()->find($request->input('resumeId'));
        if (!$resume){
            return msg(11, __LINE__);
        }
        $resumeCollection = resumeCollection::query()->find($request->route('id'));
        if (!$resumeCollection){
            return msg(11, __LINE__);
        }
        $resume->decrement('collection_count');
        $resumeCollection->delete();
        return msg(0, __LINE__);
    }

    //发布
    public function addWorkOrderCollection(Request $request){
        //通过路由获取前端数据，并判断数据格式
        $data = $this->_dataWorkerHandle($request);
        if (!is_array($data)) {
            return $data;
        }
        $workOrder = WorkOrder::query()->find($data['work_order_id']);
        if (!$workOrder){
            return msg(11, __LINE__);
        }
        $workOrderCollection = new WorkerOrderCollection($data);
        if ($workOrderCollection->save()) {
            $workOrder->increment('collection_count');
            return msg(0,['collectionId' => $workOrderCollection->id]);
        }
        //未知错误
        return msg(4, __LINE__);
    }
    /** 拉取列表信息 */
    public function getWorkerCollectionList(Request $request)
    {
        if (!$request->route('id')) {
            return msg(3 , __LINE__);
        }
        $wid = $request->route('id');
        $workOrderList   = WorkerOrderCollection::query()->where('worker_id', $wid)
            ->leftJoin('work_orders', function ($join) use ($wid) {
                $join->on('work_orders.id', '=', 'worker_order_collections.work_order_id');
            })
            ->leftJoin('workers', 'work_orders.openid', '=', 'workers.openid')
            ->leftJoin('companies', 'work_orders.openid', '=', 'companies.openid')
            ->orderByDesc("work_orders.created_at")
            ->get([
                "worker_order_collections.id as collectionId", "companies.id as company_id", "workers.id as worker_id", "work_orders.id", "work_orders.openid" , "workers.name as worker_name", "workers.avatar as worker_avatar",
                "user_type", "order_type", "companies.name as company_name", "companies.avatar as company_avatar", "content", "place", "salary", "education", "dateline", "service_charge", "description", "collection_count",
            ])
            ->toArray();
        return msg(0, $workOrderList);
    }

    /** 删除 */
    public function deleteWorkOrderCollection(Request $request)
    {
        if (!$request->route('id') || !$request->input('workOrderId')) {
            return msg(3 , __LINE__);
        }
        $workOrder = WorkOrder::query()->find($request->input('workOrderId'));
        if (!$workOrder){
            return msg(11, __LINE__);
        }
        $workOrderCollection = WorkerOrderCollection::query()->find($request->route('id'));
        if (!$workOrderCollection){
            return msg(11, __LINE__);
        }
        $workOrder->decrement('collection_count');
        $workOrderCollection->delete();

        return msg(0, __LINE__);
    }

    //检查函数
    private function _dataCompanyHandle(Request $request){
        //声明理想数据格式
        $mod = [
            "resume_id"   => ["string"],
            "company_id"    => ["string"],
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
    //检查函数
    private function _dataWorkerHandle(Request $request){
        //声明理想数据格式
        $mod = [
            "work_order_id"   => ["string"],
            "worker_id"    => ["string"],
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
