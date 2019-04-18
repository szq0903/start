<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/4/18
 * Time: 11:08
 */

namespace app\admin\controller;

use app\admin\model\Article;
use app\admin\model\Msg;

class Msgs extends BaseMould
{
    //模型名字
    public $mouldname = 'msg';
    //自身模型实例
    public $m;
    //添加数据

    public $list;

    public function _initialize()
    {
        //调用父类的构造函数
        parent::_initialize();
        $this->m = new Article;
    }



}
