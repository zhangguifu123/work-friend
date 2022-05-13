<?php

namespace App\Http\Controllers;

use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ManagerController extends Controller
{
    /**
     * 更新已经验证过的用户的 API 令牌。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function update(Request $request)
    {
        $token = Str::random(60);
        $request->user()->forceFill([
            'api_token' => hash('sha256', $token),
        ])->save();

        return msg(0, __LINE__);
    }
    /**
     * 在有效注册之后创建一个新用户实例：
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return Manager::create([
            'phone' => $data['phone'],
            'department' => $data['department'],
            'level' => $data['level'],
            'name' => $data['name'],
            'password' => Hash::make($data['password']),
            'api_token' => Str::random(60),
        ]);
    }
    public function add (Request $request ) {
        $params = array(
            'phone'    => ['regex:/^[^\s]{8,20}$/'],
            'password' => ['regex:/^[^\s]{8,20}$/'],
            'department' => ['string'],
            'level' => ['integer'],
            'name' => ['string'],
        );
        $requestTest = handleData($request,$params);
        if(!is_object($requestTest)){
            return $requestTest;
        }
        //提取数据
        $data = $request->only(array_keys($params));
        $isManager = $request->header('Authorization');
        $Authorization    = substr($isManager, 7);
        $level  = Manager::query()->where('api_token', $Authorization)->first();
        if (!$level) {
            return msg(13, __LINE__);
        }
        $level  = $level->level;
        if ($level != 0 ) {
            return msg(10, __LINE__);
        }
        $manager = $this->create($data);
        return msg(0, $manager);
    }

    public function check(Request $request){
        $data = $this->_dataHandle($request);
        if (!is_array($data)){
            return $data;
        };
        $user = Manager::query()->where('phone', $data['phone'])->first();
        if (!$user){
            return msg(2,__LINE__);
        }
        if (Hash::check($data['password'], $user->password)) { //匹配数据库中的密码
            session(["level" => $user->level]);
            return msg(0,$user);
        } else {
            return msg(1,__LINE__);
        }
    }

    public function info(Request $request){
        $user  = $request->user();
        return msg(0, $user);
    }


    //删除管理员
    public function delete(Request $request)
    {
        $manager = Manager::query()->find($request->route("id"));
        if (!$manager) {
            return msg(3, "目标不存在" . __LINE__);
        } else if($manager->level > 0) {
            return msg(3, "权限不足" .__LINE__);
        }
        $result = $manager->delete();
        if ($result) {
            return msg(0, __LINE__);
        } else {
            return msg(4, __LINE__);
        }
    }
    //获取管理员列表

    public function getList()
    {
        $manager_list = Manager::query()->get(['id', 'name', 'phone', 'department', 'level'])->toArray();
        $level = [
            "0" => "超级管理员",
            "1" => "普通管理员"
        ];

        foreach ($manager_list as $manager) {
            $manager["level"] = $level[$manager["level"]];
        }
        $list_count = Manager::query()->count();
        $message = ['total'=>$list_count,'list'=>$manager_list];
        return msg(0, $message);
    }


    private function _dataHandle(Request $request){
        $mod = array(
            'phone'    => ['regex:/^[^\s]{8,20}$/'],
            'password' => ['regex:/^[^\s]{8,20}$/'],
        );
        if (!$request->has(array_keys($mod))) {
            return msg(1, __LINE__);
        }

        $data = $request->only(array_keys($mod));
        if (Validator::make($data, $mod)->fails()) {
            return msg(3, '数据格式错误' . __LINE__);
        };

        return $data;
    }
}
