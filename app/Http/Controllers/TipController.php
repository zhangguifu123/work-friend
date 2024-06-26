<?php

namespace App\Http\Controllers;

use App\Models\Tip;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class TipController extends Controller
{
    //发布赛事
    public function publish(Request $request){
        //通过路由获取前端数据，并判断数据格式
        $data = $this->_dataHandle($request);
        if (!is_array($data)) {
            return $data;
        }
        $tip = new Tip($data);
        $data['status'] = 2;
        if ($tip->save()) {
            return msg(0,$tip->id);
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
        $tip = Tip::query();
        $tipSum = $tip->count();
        $tipList = $tip->limit(10)
            ->offset($offset)->orderByDesc("created_at")
            ->get()->toArray();
        $message['tipList'] = $tipList;
        $message['total']    = $tipSum;
        $message['limit']    = $limit;
        return msg(0, $message);
    }

    /** 删除 */
    public function delete(Request $request)
    {
        $tip = Tip::query()->find($request->route('id'));

        if (!$tip){
            return msg(11,__LINE__);
        }
        $tip->delete();
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
        $tip = Tip::query()->find($request->route('id'));
        $tip = $tip->update($data);
        if ($tip) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }

    //检查函数
    private function _dataHandle(Request $request = null){
        //声明理想数据格式
        $mod = [
            "user_name"      => ["string", "max:20"],
            "user_id"       => ["string"],
            "title"         => ["string", "max:20"],
            "content"       => ["string", "nullable"],
            "status"       => ["string", "nullable"],
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
