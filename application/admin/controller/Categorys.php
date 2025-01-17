<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/4/12
 * Time: 10:40
 */

namespace app\admin\controller;

use app\admin\model\Category;

class Categorys extends BaseMould
{
    //模型名字
    public $mouldname = 'category';
    //自身模型实例
    public $m;
    //添加数据


    public function _initialize()
    {
        //调用父类的构造函数
        parent::_initialize();
        $this->m= new Category;
    }

    public function index()
    {
        $this->list =array();
        $this->m->getTree(0, $this->list);
        foreach ($this->list as $key=>$val)
        {
            //添加下级文章列表
            $this->list[$key]['list'] = url('admin/articles/index',['id'=>$val['id']]);
        }
        $this->isPage = false; //不分页


        return parent::index(); // TODO: Change the autogenerated stub
    }

    public function add()
    {
        //准备上级栏目下拉内容
        $mv =array();
        $this->m->getTree(0, $mv);
        $carr = $this->m->getSelectArray($mv,array(0=>'顶级栏目'));

        foreach ($this->field as $k=>$val) {
            if($val['fieldname'] == 'pid')
            {
                $this->field[$k]['vdefault'] = $carr;
            }
        }

        return parent::add(); // TODO: Change the autogenerated stub
    }


    public function edit($id)
    {
        //准备上级栏目下拉内容
        $mv =array();
        $this->m->getTree(0, $mv);
        $carr = $this->m->getSelectArray($mv,array(0=>'顶级栏目'));

        foreach ($this->field as $k=>$val) {
            if($val['fieldname'] == 'pid')
            {
                $this->field[$k]['vdefault'] = $carr;
                $this->field[$k]['isAdd'] = 1;
            }elseif($val['fieldname'] == 'type')
            {
                $this->field[$k]['isAdd'] = 1;
            }
        }
        return parent::edit($id); // TODO: Change the autogenerated stub
    }
}