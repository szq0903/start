<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/4/18
 * Time: 11:22
 */
namespace app\admin\model;

class Article extends Basemodel
{

    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }

    public function getCidAttr($value)
    {
        $arr = Category::get(['id'=>$value]);
        if(empty($value))
        {
            return ;
        }else{
            return $arr['name'];
        }
    }

    public function getRecommendAttr($value)
    {
        $arr = array('正常','推荐','置顶','头条');
        if(empty($value))
        {
            return '正常';
        }else{
            return $arr[$value];
        }
    }
}