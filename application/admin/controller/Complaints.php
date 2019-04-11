<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/1/31
 * Time: 17:03
 */

namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Db;
use think\Config;
use app\index\model\Area;
use app\index\model\Field;
use app\index\model\Mould;
use app\index\model\Complaint;
use lib\Form;

class Complaints extends Controller
{
    public $title='SEOCRM管理系统';
    public $mould;
    public $field;

    public function _initialize()
    {
        check();
        $this->assign('menu', getLeftMenu());

        //初始化模型
        $this->mould= Mould::get(['table'=>'complaint']);
        $this->assign('mould',$this->mould);

        //初始化字段
        $this->field = Field::where(['mid'=>$this->mould->id])->order('rank')->select();
        $this->assign('field',$this->field);

        //初始化url
        $url['add'] =  url('index/'.$this->mould->table.'s/add');
        $url['index'] =  url('index/'.$this->mould->table.'s/index');
        $this->assign('url',$url);
    }

    /**
     * 列表
     */
    public function index(){

        // 查询数据集
        $list = Complaint::order('update','desc')->paginate(10);;
        foreach ($list as $key=>$val)
        {
            $list[$key]['edit'] = url('index/'.$this->mould->table.'s/edit',['id'=>$val['id']]);
            $list[$key]['del'] = url('index/'.$this->mould->table.'s/del',['id'=>$val['id']]);
        }

        // 把数据赋值给模板变量list
        $this->assign('list', $list);

        $field =array();
        foreach ($this->field as $val) {
            if ($val['islist'] == 1)//隐藏时跳过本次
            {
                continue;
            }else{
                $field[] =$val;
            }
        }
        $this->assign('field', $field);


        //获取当当前控制器
        $request = Request::instance();
        $this->assign('act', $request->controller());
        $this->assign('title',$this->mould->name.'管理-'.$this->title);
        return $this->fetch();
    }

    /**
     * 添加
     * @return mixed
     */
    public function add()
    {
        //是否为提交表单
        if (Request::instance()->isPost())
        {
            $complaint           = new Complaint();
            foreach ($this->field as $val)
            {
                $complaint->$val['fieldname'] = Request::instance()->post($val['fieldname']);
            }
            $complaint->update = time();
            $complaint->save();
            $this->success('添加成功！');
        }

        //处理字段显示
        $form = new Form();
        $formhtml = array();
        foreach ($this->field as $val)
        {
            if($val['ishide'] ==1)//隐藏时跳过本次
            {
                continue;
            }
            $arr['html'] = $form->fieldToForm($val,'form-control');
            $arr['itemname'] = $val['itemname'];
            $arr['fieldname'] = $val['fieldname'];
            $formhtml[] = $arr;
        }
        $this->assign('formhtml',$formhtml);



        $this->assign('title','添加'.$this->mould->name.'-'.$this->title);
        $request = Request::instance();
        $this->assign('act', $request->controller());
        return $this->fetch('edit');
    }

    /**
     * 修改
     * @param $id
     */
    public function edit($id)
    {
        $complaint = Complaint::get($id);

        //判断模型是否存在
        if(empty($complaint))
        {
            $this->error('要修改的'.$this->mould->name.'不存在');
        }

        //是否为提交表单
        if (Request::instance()->isPost())
        {
            foreach ($this->field as $val)
            {
                if($val['ishide'] ==1)//隐藏时跳过本次
                {
                    continue;
                }
                $complaint->$val['fieldname'] = Request::instance()->post($val['fieldname']);
            }
            $complaint->save();
            $this->success('修改成功！');
        }


        //处理字段显示
        $form = new Form();
        $formhtml = array();
        foreach ($this->field as $val)
        {
            if($val['ishide'] ==1)//隐藏时跳过本次
            {
                continue;
            }
            $val['vdefault'] = $complaint[$val['fieldname']];
            $arr['html'] = $form->fieldToForm($val,'form-control');
            $arr['itemname'] = $val['itemname'];
            $arr['fieldname'] = $val['fieldname'];
            $formhtml[] = $arr;
        }
        $this->assign('formhtml',$formhtml);

        $this->assign('title','修改'.$this->mould->name.'-'.$this->title);
        $request = Request::instance();
        $this->assign('act', $request->controller());
        return $this->fetch('edit');
    }

    /**
     * 删除
     * @param $id
     */
    public function del($id)
    {
        $complaint = Complaint::get($id);

        //判断模型是否存在
        if(empty($complaint))
        {
            $this->error('要修改的'.$this->mould->name.'不存在');
        }else{
            $complaint ->delete();
            $this->success('删除'.$this->mould->name.'成功！',url('index/'.$this->mould->table.'s/index'));
        }
        $this->assign('title','删除'.$this->mould->name.'-'.$this->title);
        $request = Request::instance();
        $this->assign('act', $request->controller());
        return $this->fetch();
    }

}
