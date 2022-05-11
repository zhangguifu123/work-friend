<?php

namespace App\Http\Controllers;

use App\Models\Appeal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppealController extends Controller
{
    //发布
    public function publish(Request $request){
        //通过路由获取前端数据，并判断数据格式
        $data = $this->_dataHandle($request);
        if (!is_array($data)) {
            return $data;
        }
        $data['status'] = 2;
        $appeal = new Appeal($data);
        if ($appeal->save()) {
            return msg(0,$appeal->id);
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
        $worker   = Appeal::query()->where([
            ['fromId', $request->route('id')],
            ['toType', $request->input('type')]
        ])->get()->toArray();
        return msg(0, $worker);
    }

    /** 拉取列表信息 */
    public function getList(Request $request)
    {
        //分页，每页10条
        $limit = 10;
        $offset = $request->route("page") * $limit - $limit;
        $appeal = Appeal::query();
        $appealSum = $appeal->count();
        $appealList = $appeal
            ->limit(10)
            ->offset($offset)->orderByDesc("created_at")
            ->get()
            ->toArray();
        $message['AppealList'] = $appealList;
        $message['total']    = $appealSum;
        $message['limit']    = $limit;
        if (isset($message['token'])){
            return msg(13,$message);
        }
        return msg(0, $message);
    }

    /** 删除 */
    public function delete(Request $request)
    {
        $appeal = Appeal::query()->find($request->route('id'));
        if (!$appeal){
            return msg(11, __LINE__);
        }
        $appeal->delete();

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
        $appeal = Appeal::query()->find($request->route('id'));
        $appeal = $appeal->update($data);
        if ($appeal) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }

    //检查函数
    private function _dataHandle(Request $request = null){
        //声明理想数据格式
        $mod = [
            "fromId"        => ["string"],
            "toId"          => ["string"],
            "toName"        => ["string"],
            "fromName"      => ["string"],
            "work_order_id" => ["string"],
            "work"          => ["string"],
            "measure"       => ["string"],
            "fromType"      => ["string"],
            "toType"        => ["string"],
            "img"           => ["json"],
            "content"       => ["string"],
            "status"        => ["string", 'nullable'],
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
