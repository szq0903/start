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
use app\index\model\Field;
use app\index\model\Mould;
use app\index\model\Category;
use lib\Form;
use lib\Tree;

class Categorys extends Controller
{
    public $title='SEOCRM管理系统';
    public $mould;
    public $field;

    public function _initialize()
    {
        check();
        $this->assign('menu', getLeftMenu());
        //初始化模型
        $this->mould= Mould::get(['table'=>'category']);
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
        $sort = new Category();
        $list = array();
        $sort->getTree(0 ,$list);


        foreach ($list as $key=>$val)
        {
            //处理列表图标显示
            if($val['icon'] <>'')
            {
                $list[$key]['icon'] = "<div class=\"thmb-prev\">
                  			<a href=\"{$val['icon']}\" class=\"fa fa-file-image-o\" data-rel=\"prettyPhoto\"></a>
							</div>";
            }
            $list[$key]['edit'] = url('index/'.$this->mould->table.'s/edit',['id'=>$val['id']]);
            $list[$key]['del'] = url('index/'.$this->mould->table.'s/del',['id'=>$val['id']]);
        }



        // 把数据赋值给模板变量list
        $this->assign('list', $list);
        $this->assign('field', $this->field);

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
            $category          = new Category();
            foreach ($this->field as $val)
            {
                $category->$val['fieldname'] = Request::instance()->post($val['fieldname']);
            }
            $category->update = time();
            $category->save();
            $this->success('添加成功！');
        }


        //处理select
        $category1 = array();
        $psort = new Category();
        $psort->getTree(0,$category1);
        $carr = array('0'=>'顶级栏目');
        foreach ($category1 as $val)
        {
            $carr[$val['id']] = $val['name'];
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
            if($val['fieldname'] == 'pid')//处理栏目id
            {
                $val['vdefault'] = $carr;
                $arr['html'] = $form->fieldToForm($val,'form-control','','0');
            }elseif ($val['fieldname'] == 'icon')
            {
                $arr['html'] = $form->fieldToForm($val,'form-control','icon');
            }else{
                $arr['html'] = $form->fieldToForm($val,'form-control');
            }


            $arr['itemname'] = $val['itemname'];
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
        $category = Category::get($id);

        //判断模型是否存在
        if(empty($category))
        {
            $this->error('要修改的'.$this->mould->name.'不存在');
        }

        //是否为提交表单
        if (Request::instance()->isPost())
        {
            foreach ($this->field as $val)
            {
                $category->$val['fieldname'] = Request::instance()->post($val['fieldname']);
            }
            $category->save();
            $this->success('修改成功！');
        }

        //处理select
        $category1 = array();
        $psort = new Category();
        $psort->getTree(0,$category1);
        $carr = array('0'=>'顶级栏目');
        foreach ($category1 as $val)
        {
            $carr[$val['id']] = $val['name'];
        }

        //处理字段显示
        $form = new Form();
        $formhtml = array();
        foreach ($this->field as $val)
        {
            $val['vdefault'] = $category[$val['fieldname']];

            if($val['ishide'] ==1)//隐藏时跳过本次
            {
                continue;
            }
            if($val['fieldname'] == 'pid')//处理栏目id
            {
                $val['vdefault'] = $carr;
                $arr['html'] = $form->fieldToForm($val,'form-control','',$category->getData('pid'));
            }elseif ($val['fieldname'] == 'icon')
            {
                $arr['html'] = $form->fieldToForm($val,'form-control','icon');
            }else{
                $arr['html'] = $form->fieldToForm($val,'form-control');
            }

            $arr['itemname'] = $val['itemname'];
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
        $category = Category::get($id);

        //判断模型是否存在
        if(empty($category))
        {
            $this->error('要修改的'.$this->mould->name.'不存在');
        }else{
            $category ->delete();
            $this->success('删除'.$this->mould->name.'成功！',url('index/'.$this->mould->table.'s/index'));
        }
        $this->assign('title','删除'.$this->mould->name.'-'.$this->title);
        $request = Request::instance();
        $this->assign('act', $request->controller());
        return $this->fetch();
    }

    public function addimg($f) {

        if(!empty(request() -> file($f)))
        {
            $file = request() -> file($f);
        }

        if(!isset($file))
        {
            // 上传失败获取错误信息
            echo "{\"code\":-1, \"error\":\"Invalid file format\"}";
            exit;
        }

        // 移动到框架应用根目录/public/uploads/ 目录下
        $file->validate(['size'=>1024*1024*2,'ext'=>'jpg,png,gif']);
        $info = $file->rule('md5')->move(ROOT_PATH . 'public' . DS . 'uploads'. DS .'images');

        if($info){
            $re =array(
                'code'=> 0,
                'message'=> '上传成功',
                'data'=>DS ."public" . DS . 'uploads'. DS .'images' . DS .$info->getSaveName()
            );
            echo json_encode($re);
        }else{
            // 上传失败获取错误信息
            echo "{\"code\":-1, \"error\":\"Invalid file format\"}";
        }
    }

}
