<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/4/22
 * Time: 17:50
 */
namespace app\admin\model;

class Order extends Basemodel
{

    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }

    public function getMidAttr($value)
    {
        $arr = Member::get(['id'=>$value]);
        if(empty($value))
        {
            return '顶级会员';
        }else{
            return $arr['name'];
        }
    }
}