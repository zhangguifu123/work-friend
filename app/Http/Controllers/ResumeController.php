<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\ResumeCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResumeController extends Controller
{
    //
    //发布
    public function publish(Request $request){
        //通过路由获取前端数据，并判断数据格式
        $data = $this->_dataHandle($request);
        if (!is_array($data)) {
            return $data;
        }
        $data['status'] = 2;
        $data['collection_count'] = 0;
        $resume = new Resume($data);
        if ($resume->save()) {
            return msg(0,$resume->id);
        }
        //未知错误
        return msg(4, __LINE__);
    }
    /** 拉取列表信息 */
    public function getMeList(Request $request)
    {
        if (!$request->route('id')) {
            return msg(3 , __LINE__);
        }
        $worker   = Resume::query()->where(
            'openid', $request->route('id')
        )->first();
        return msg(0, $worker);
    }

    /** 拉取列表信息 */
    public function getList(Request $request)
    {
        if (!$request->input('companyId')){
            return msg(11, __LINE__);
        }
        $data      = $request->all();
        $companyId = $data['companyId'];
        //分页，每页10条
        $limit = 10;
        $offset = $request->route("page") * $limit - $limit;
        $resume = Resume::query();
        if (!is_array($data['sex'])) {
            $resume = $resume->whereIn('sex', $data['sex']);
        }
        $resumeSum = $resume->count();
        $resumeList = $resume
            ->limit(10)
            ->offset($offset)->orderByDesc("created_at")
            ->get()
            ->toArray();
        $newResumeList = [];
        if (is_array($data['age'])) {
            foreach ( $resumeList as $resume ) {
                $resume['age'] = json_decode($resume['age'], true);
                if ( $data['age']['max'] >= $resume['age']['max'] && $data['age']['min'] <= $resume['age']['min'] ) {
                    $resume['age'] = json_encode($resume['age'], true);
                    $newResumeList[] = $resume;
                }
            }
        } else {
            $newResumeList = $resumeList;
        }
        $resumeList = $this->_isCollection($companyId, $newResumeList);
        $message['resumeList'] = $resumeList;
        $message['total']    = $resumeSum;
        $message['limit']    = $limit;
        if (isset($message['token'])){
            return msg(13,$message);
        }
        return msg(0, $message);
    }
    private function _isCollection($companyId, $resumeList){
        $resumeCollection = ResumeCollection::query()->where('company_id', $companyId)->get()->toArray();
        $collectionArray  = [];
        foreach ($resumeCollection as $value){
            $collectionArray[$value['resume_id']] = $value['id'];
        }
        $newResumeList = [];
        foreach ($resumeList as $resume){
            if (array_key_exists($resume['id'], $collectionArray)) {
                $resume += ['isCollection' => 1, 'collectionId' => $collectionArray[$resume['id']]];
            } else {
                $resume += ['isCollection' => 0];
            };
            $newResumeList[] = $resume;
        }
        return $newResumeList;
    }
    /** 删除 */
    public function delete(Request $request)
    {
        if (!$request->route('id')) {
            return msg(3 , __LINE__);
        }
        $resume = Resume::query()->find($request->route('id'));
        if (!$resume){
            return msg(11, __LINE__);
        }
        $resume->delete();

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
        $resume = Resume::query()->find($request->route('id'));
        $resume = $resume->update($data);
        if ($resume) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }

    //检查函数
    private function _dataHandle(Request $request){
        //声明理想数据格式
        $mod = [
            "openid"   => ["string"],
            "name"     => ["string"],
            "sex"      => ["string"],
            "age"      => ["string"],
            "phone"    => ["string"],
            "education"=> ["string"],
            "avatar"   => ["string"],
            "worker_id"=> ["integer"],

            "salary"   => ["string"],
            "position" => ["string"],
            "city"     => ["string"],

            "education_experience" => ["string"],
            "internship_experience" => ["string"],
            "project_experience"    => ["string"],
            "self_assessment"       => ["string"],
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
