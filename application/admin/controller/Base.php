<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/4/11
 * Time: 15:40
 */

namespace app\admin\controller;

use think\Controller;



class Base extends Controller
{
    public $title;
    public $inputlist;

    public function _initialize()
    {
        check();
        $this->assign('menu', getLeftMenu());
        $this->title = config("project_name");
        $this->inputlist = config('inputlist');
        $this->assign('inputlist',$this->inputlist);

    }

    public function _empty()
    {
        echo "没有这个操作";
    }

}