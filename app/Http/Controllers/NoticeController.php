<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NoticeController extends Controller
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
        $notice = new Notice($data);
        if ($notice->save()) {
            return msg(0,$notice->id);
        }
        //未知错误
        return msg(4, __LINE__);
    }
    /** 拉取列表信息 */
    public function getList(Request $request)
    {
        if (!$request->route('id')) {
            return msg(3 , __LINE__);
        }
        $worker   = Notice::query()->where([
            ['openid', $request->route('id')],
            ['status', 2]
        ])->get()->toArray();
        return msg(0, $worker);
    }

    /** 删除 */
    public function delete(Request $request)
    {
        if (!$request->route('id')) {
            return msg(3 , __LINE__);
        }
        $notice = Notice::query()->find($request->route('id'));
        if (!$notice){
            return msg(11, __LINE__);
        }
        $notice->delete();

        return msg(0, __LINE__);
    }

    /** 修改 */
    public function update(Request $request)
    {
        if (!$request->route('id') || !$request->input('status')) {
            return msg(3 , __LINE__);
        }
        //修改
        $notice = Notice::query()->find($request->route('id'));
        $notice = $notice->update(['status' => $request->input('status')]);
        if ($notice) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }

    //检查函数
    private function _dataHandle(Request $request = null){
        //声明理想数据格式
        $mod = [
            "content"   => ["string"],
            "openid"    => ["string"],
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
