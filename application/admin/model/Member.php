<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/4/20
 * Time: 10:16
 */
namespace app\admin\model;

class Member extends Basemodel
{

    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }

    public function getTree($parentid, &$sort ,$str ='')
    {
        $list = parent::where('parentid','=',$parentid)->order('update')->select();
        //├─
        //└─
        if(!empty($list))
        {
            foreach($list as $k=>$v)
            {
                if($str <> '')
                {
                    $v['name']=$str.'├─'.$v['name'];
                }
                $sort[] = $v;
                $this->getTree($v->id,$sort,$str.'&nbsp;&nbsp;&nbsp;');
            }
        }
    }


    public function getParentidAttr($value)
    {
        $arr = Member::get(['id'=>$value]);
        if(empty($value))
        {
            return '顶级会员';
        }else{
            return $arr['name'];
        }
    }

    public function getLevelidAttr($value)
    {
        $arr = Level::get(['id'=>$value]);
        if(empty($value))
        {
            return '没有等级';
        }else{
            return $arr['name'];
        }
    }



}