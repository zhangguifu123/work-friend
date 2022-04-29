<?php
use App\Models\Company;
use App\Models\Worker;
use Illuminate\Http\Request;
function handleUid(Request $request) {

}

function msg($code, $msg) {
$status = array(
    0 => '成功',
    1 => '缺失参数',
    2 => '账号密码错误',
    3 => '错误访问',
    4 => '未知错误',
    5 => '其他错误',
    6 => '未登录',
    7 => '重复访问',
    8 => '重复添加',
    9 => '无刷新次数',
    10 => '非本人',
    11 => '目标不存在',
    12 => '图片不和谐',
    13 => '未认证',
);

    $result = array(
        'code' => $code,
        'status' => $status[$code],
        'data' => $msg
    );


    return json_encode($result, JSON_UNESCAPED_UNICODE);
}

function checkUserType($type){
    $userType = array(
        '1' => Worker::query(),
        '2' => Company::query(),
    );
    return $userType[$type];
}

function compressedImage($imgsrc, $imgdst) {
    list($width, $height, $type) = getimagesize($imgsrc);

    $new_width = $width;//压缩后的图片宽
    $new_height = $height;//压缩后的图片高

    if($width >= 600){
        $per = 600 / $width;//计算比例
        $new_width = $width * $per;
        $new_height = $height * $per;
    }

    switch ($type) {
        case 1:
            $giftype = check_gifcartoon($imgsrc);
            if ($giftype) {
                header('Content-Type:image/gif');
                $image_wp = imagecreatetruecolor($new_width, $new_height);
                $image = imagecreatefromgif($imgsrc);
                imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                //90代表的是质量、压缩图片容量大小
                imagejpeg($image_wp, $imgdst, 90);
                imagedestroy($image_wp);
                imagedestroy($image);

                return 0;
            }
            break;
        case 2:
//            header('Content-Type:image/jpeg');
            $image_wp = imagecreatetruecolor($new_width, $new_height);
            $image = imagecreatefromjpeg($imgsrc);
            imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            //90代表的是质量、压缩图片容量大小
            imagejpeg($image_wp, $imgdst, 90);
            imagedestroy($image_wp);
            imagedestroy($image);

            return 0;
            break;
        case 3:
            header('Content-Type:image/png');
            $image_wp = imagecreatetruecolor($new_width, $new_height);
            $image = imagecreatefrompng($imgsrc);
            imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            //90代表的是质量、压缩图片容量大小
            imagejpeg($image_wp, $imgdst, 90);
            imagedestroy($image_wp);
            imagedestroy($image);

            return 0;
            break;
    }
}
