<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/4/11
 * Time: 17:29
 */

namespace app\admin\controller;
use think\Controller;
use think\Request;

class MyError extends Controller
{
    public function index(Request $request)
    {
        echo "没有这个控制器";
    }

    public function _empty()
    {
        echo "没有这个操作";
    }

}