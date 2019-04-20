<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/4/20
 * Time: 10:00
 */
namespace app\admin\controller;

use app\admin\model\Level;

class Levels extends BaseMould
{
    //模型名字
    public $mouldname = 'level';
    //自身模型实例
    public $m;
    //添加数据


    public function _initialize()
    {
        //调用父类的构造函数
        parent::_initialize();
        $this->m = new Level;
    }

}