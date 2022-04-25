<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Worker;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function check(Request $request){
        if ($request->input('js_code') || $request->input('status')) {
            return msg(1, __LINE__);
        }
        $data['js_code'] = $request->input('js_code');
        $data['status']  = $request->input('status');
        $http = new GuzzleHttp\Client;
        $response = $http->get('https://api.weixin.qq.com/sns/jscode2session?appid=APPID&secret=SECRET&js_code=JSCODE&grant_type=authorization_code
', [
            'query' => [
                'appid'      => 'wx434e0e175cbdd8a5',
                'secret'     => 'dc5793927faff4b09e60255fc206ea79',
                'grant_type' => 'authorization_code',
                'js_code'    => $data['js_code'],
            ],
        ]);
        $res    = json_decode( $response->getBody(), true);
        if(!key_exists('openid',$res)){
            return msg(4, $res);
        }
        $checkWorker = DB::table('workers')->where('openid', $res['openid'])->first();
        $checkCompany = DB::table('companies')->where('openid', $res['openid'])->first();
        if ($data['status'] == '1' && $checkWorker){
            return msg(0, $checkWorker);
        }
        if ($data['status'] == '2' && $checkCompany){
            return msg(0, $checkCompany);
        }
        if ($data['status'] == null){
            if ($checkWorker){
                return msg(0, $checkWorker);
            }
            if ($checkCompany){
                return msg(0, $checkCompany);
            }
            return msg(11, $res['openid']);
        }


    }
    public function authenticate(Request $request){
        //通过路由获取前端数据，并判断数据格式
        $data = $this->_dataHandle($request);
        if (!is_array($data)) {
            return $data;
        }
        $data['status'] = 0;
        $User = '无数据！！';
        if ($request->input('status') == 1){
            $User = new Worker($data);
            $User->save();
        }
        if ($request->input('status') == 2){
            $User = new Company($data);
            $User->save();
        }
        return msg(0, $User);

    }
    public function getList(Request $request){
        $type = $request->input('type');
        $page   = $request->route('page');
        $list   = 13;
        if ($type == 1){
            $model = Worker::query();
            $list = $this->_getList($model, $page);
        }
        if ($type == 2){
            $model = Company::query();
            $list = $this->_getList($model, $page);
        }
        if ($list == 13){
            return msg(13 , __LINE__);
        }
        return msg(0, $list);

    }
    protected function _getList($model, $page){
        //分页，每页10条
        $limit = 10;
        $offset = $page * $limit - $limit;
        $modelSum = $model->count();
        $modelList = $model
            ->limit(10)
            ->offset($offset)->orderByDesc("games.created_at")
            ->get()
            ->toArray();
        $message['gameList'] = $modelList;
        $message['total']    = $modelSum;
        $message['limit']    = $limit;
        if (isset($message['token'])){
            return 13;
        }
        return msg(0, $message);
    }
    //检查函数
    private function _dataHandle(Request $request){
        //声明理想数据格式
        if ($request->input('status') == 1){
            $mod = [
                "openid"   => ["string"],
                "uid"      => ["string"],
                "name"     => ["string"],
                "major"    => ["string"],
                "college"  => ["string"],
                "phone"    => ["string"],
                "avatar"   => ["string"],
            ];
        } else {
            $mod = [
                "openid"      => ["string"],
                "code"        => ["string"],
                "name"        => ["string"],
                "industry"    => ["string"],
                "legal_person"=> ["string"],
                "phone"       => ["string"],
                "avatar"      => ["string"],
            ];
        }


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
