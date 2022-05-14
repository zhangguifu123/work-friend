<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    //
    public function upload(Request $request) {
        if (!$request->input('image')  || !$request->input('manager_id') || !$request->input('manager_name')) {
            return msg(3 , __LINE__);
        }
        $data = $request->all();
        $banner   = new Banner($data);
        $banner->save();
        return msg(0, $banner);
    }

    public function getList(Request $request) {
        if (!$request->route('page')){
            return msg(1, __LINE__);
        }
        //分页，每页10条
        $limit = 10;
        $offset = $request->route("page") * $limit - $limit;
        $banner =  Banner::query();
        $bannerSum = $banner->count();
        $bannerList = $banner
            ->limit(10)
            ->offset($offset)->orderByDesc("created_at")
            ->get()
            ->toArray();
        $message['bannerList'] = $bannerList;
        $message['total']    = $bannerSum;
        $message['limit']    = $limit;
        return msg(0, $message);
    }
}
