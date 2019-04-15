<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/4/12
 * Time: 10:31
 */

namespace app\admin\controller;

use think\Request;
use app\admin\model\Sysinfo;
use app\admin\model\Mould;
use app\admin\model\Field;
use lib\Form;


/**
 * 用户管理控制器
 * @author myeoa
 * @email  6731834@163.com
 * @date 2017年6月15日 上午11:07:56
 */
class BaseMould extends Base
{

    public $mould;
    public $field;

    public function _initialize()
    {
        //调用父类的构造函数
        parent::_initialize();

        //初始化模型
        $this->mould= Mould::get(['table'=>$this->mouldname]);
        $this->assign('mould',$this->mould);

        //初始化字段
        $this->field = Field::where(['mid'=>$this->mould->id])->order('rank')->select();
        $this->assign('field',$this->field);

        //初始化url
        $url['add'] =  url('admin/'.$this->mould->table.'s/add');
        $url['index'] =  url('admin/'.$this->mould->table.'s/index');
        $this->assign('url',$url);

        //初始化act
        $request = Request::instance();
        $this->assign('act', $request->controller());

    }


    public function index()
    {
        $list = $this->m->order('update','desc')->paginate(10);;

        foreach ($list as $key=>$val)
        {
            $list[$key]['edit'] = url('admin/'.$this->mould->table.'s/edit',['id'=>$val['id']]);
            $list[$key]['del'] = url('admin/'.$this->mould->table.'s/del',['id'=>$val['id']]);
        }

        $this->assign('list',$list);

        $this->assign('title','管理'.$this->mould['name'].'-'.$this->title);
        return $this->fetch('');
    }

    public function add()
    {
        //处理字段显示
        $form = new Form();
        $formhtml = array();
        foreach ($this->field as $val)
        {
            if($val['ishide'] ==1)//隐藏时跳过本次
            {
                continue;
            }
            $arr['html'] = $form->fieldToForm($val,'form-control',$val['fieldname']);
            $arr['fieldname'] = $val['fieldname'];
            $arr['itemname'] = $val['itemname'];
            $formhtml[] = $arr;
        }

        $this->assign('formhtml',$formhtml);

        $this->assign('title','添加'.$this->mould->name.'-'.$this->title);
        return $this->fetch('edit');
    }

    public function edit($id)
    {
        $this->assign('title','修改'.$this->mould->name.'-'.$this->title);
        return $this->fetch('');
    }

    public function del($id)
    {
        $this->assign('title','删除'.$this->mould->name.'-'.$this->title);
        return $this->fetch('');
    }

}