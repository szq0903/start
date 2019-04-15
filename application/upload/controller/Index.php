<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/4/12
 * Time: 15:09
 */

namespace app\upload\controller;
use think\Controller;
use think\Request;

class Index extends Controller
{
    public function addimg($f) {

        if(!empty(request() -> file($f)))
        {
            $file = request() -> file($f);
        }

        if(!isset($file))
        {
            // 上传失败获取错误信息
            echo "{\"code\":-1, \"error\":\"Invalid file format\"}";
            exit;
        }

        // 移动到框架应用根目录/public/uploads/ 目录下
        $file->validate(['size'=>1024*1024*2,'ext'=>'jpg,png,gif']);
        $info = $file->rule('date')->move(ROOT_PATH . 'public' . DS . 'uploads'. DS .'images');

        if($info){
            $re =array(
                'code'=> 0,
                'message'=> '上传成功',
                'data'=>DS ."public" . DS . 'uploads'. DS .'images' . DS .$info->getSaveName()
            );
            echo json_encode($re);
        }else{
            // 上传失败获取错误信息
            echo "{\"code\":-1, \"error\":\"Invalid file format\"}";
        }
    }

}