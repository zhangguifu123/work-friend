<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Worker;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GuzzleHttp;
ini_set("error_reporting", "E_ALL & ~E_NOTICE");
class UserController extends Controller
{
    public function check(Request $request){
        if (!$request->input('js_code')) {
            return msg(1, __LINE__);
        }
        $data['js_code'] = $request->input('js_code');
        $data['type']  = $request->input('type');
        $http = new GuzzleHttp\Client;
        $response = $http->get('https://api.weixin.qq.com/sns/jscode2session?appid=APPID&secret=SECRET&js_code=JSCODE&grant_type=authorization_code
', [
            'query' => [
                'appid'      => 'wxc48ee9576b6b6236',
                'secret'     => '0e18a790e1ea9b923feb1c103fe52feb',
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
        $result = [];
        if ($data['type'] == '1' && $checkWorker){
            $result['user'] = $checkWorker;
            $result['type'] = 1;
            return msg(0, $result);
        }
        if ($data['type'] == '2' && $checkCompany){
            $result['user'] = $checkCompany;
            $result['type'] = 2;
            return msg(0, $result);
        }
        if ($data['type'] == '0'){
            if ($checkWorker){
                $result['user'] = $checkWorker;
                $result['type'] = 1;
                return msg(0, $result);
            }
            if ($checkCompany){
                $result['user'] = $checkCompany;
                $result['type'] = 2;
                return msg(0, $result);
            }
        }
        return msg(13, $res['openid']);

    }
    public function authenticate(Request $request){
        //通过路由获取前端数据，并判断数据格式
        $data = $this->_dataHandle($request);
        if (!is_array($data)) {
            return $data;
        }
        $data['status'] = "2";
        $User = '无数据！！';
        if ($request->input('type') == 1){
            $User = new Worker($data);
            $User->save();
        }
        if ($request->input('type') == 2){
            $data += ['address' => '空', 'company_size' => '空', 'registered_capital' => '空', 'incorporation' => '空', 'introduce' => '空'];
            $User = new Company($data);
            $User->save();
        }
        return msg(0, $User);

    }
    public function updateScore(Request $request) {
        if (is_null($request->input('score')) || !$request->route('id') || !$request->input('type')){
            return msg(1, __LINE__);
        }
        $score  = $request->input('score');
        $openid = $request->route('id');
        $type   = $request->input('type');
        if ($type == "1"){
            $model = Worker::query();
        }
        if ($type == "2"){
            $model = Company::query();
        }
        $user = $model->where('openid', $openid)->first();
        if (!$user) {
            return msg(11, __LINE__);
        }
        $user->credit_score = $score;
        $user->save();
        return msg(0, $user);
    }
    public function updateStatus(Request $request){
        if (!$request->input('status') || !$request->input('type') || !$request->route('id')) {
            return msg(1, __LINE__);
        }
        $openid = $request->route('id');
        $type   = $request->input('type');

        if ($type == 2 || $type == '2'){
            $data = $this->_updateDataHandle($request);
        } else {
            $data = $this->_dataHandle($request);
        }
        if (!is_array($data)) {
            return $data;
        }
        $status = $request->input('status');
        $data += ["status" => $status];
        $model = Worker::query();
        if ($type == "1"){
            $model = Worker::query();
        }
        if ($type == "2"){
            $model = Company::query();
        }
        $user = $model->where('openid', $openid)->first();
        if ($user->update($data)) {
            return msg(0, $user);
        }
        return msg(4, __LINE__);
    }

    public function getOneWorker(Request $request){
        if (!$request->route('id')) {
            return msg(3 , __LINE__);
        }
        $worker   = Worker::query()->find($request->route('id'));
        return msg(0, $worker);
    }

    public function getOneCompany(Request $request){
        if (!$request->route('id')) {
            return msg(3 , __LINE__);
        }
        $company   = Company::query()->find($request->route('id'));
        return msg(0, $company);
    }

    public function getList(Request $request){
        $type = $request->input('type');
        $page   = $request->route('page');
        $list   = 13;
        if ($type == "1"){
            $model = Worker::query();
            $list = $this->_getList($model, $page);
        }
        if ($type == "2"){
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
            ->offset($offset)->orderByDesc("created_at")
            ->get()
            ->toArray();
        $message['gameList'] = $modelList;
        $message['total']    = $modelSum;
        $message['limit']    = $limit;
        if (isset($message['token'])){
            return 13;
        }
        return $message;
    }
//检查函数
    private function _updateDataHandle(Request $request){
        //声明理想数据格式
        $mod = [
            "openid"      => ["string"],
            "code"        => ["string"],
            "name"        => ["string"],
            "industry"    => ["string"],
            "legal_person"=> ["string"],
            "phone"       => ["string"],
            "avatar"      => ["string"],

            "address"      => ["string"],
            "company_size"      => ["string"],
            "registered_capital"      => ["string"],
            "incorporation"      => ["string"],
            "introduce"      => ["string"],
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
    private function _dataHandle(Request $request){
        //声明理想数据格式
        if ($request->input('type') == "2") {
            $mod = [
                "openid"      => ["string"],
                "code"        => ["string"],
                "name"        => ["string"],
                "industry"    => ["string"],
                "legal_person"=> ["string"],
                "phone"       => ["string"],
                "avatar"      => ["string"],
            ];
        } else {
            $mod = [
                "openid"   => ["string"],
                "uid"      => ["string"],
                "name"     => ["string"],
                "major"    => ["string"],
                "college"  => ["string"],
                "phone"    => ["string"],
                "avatar"   => ["string"],
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
