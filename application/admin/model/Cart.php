<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/4/22
 * Time: 10:07
 */
namespace app\admin\model;

class Cart extends Basemodel
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
            return ;
        }else{
            return $arr['name'];
        }
    }

    public function getPidAttr($value)
    {
        $arr = Article::get(['id'=>$value]);
        if(empty($value))
        {
            return ;
        }else{
            return $arr['title'];
        }
    }

}