<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/4/20
 * Time: 10:49
 */
namespace app\admin\model;
use think\Model;

class Basemodel extends Model
{

    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }

    public function getSelectArray($array,$topArr =array(0=>'请选择'))
    {
        $arr = $topArr;;
        foreach ($array as $value)
        {
            $arr[$value['id']] = $value['name'];
        }
        return $arr;
    }


}