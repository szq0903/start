<?php
namespace app\admin\controller;

use think\Request;
use app\admin\model\Sysinfo;
use app\admin\model\Field;
use lib\Form;


/**
 * 用户管理控制器
 * @author myeoa
 * @email  6731834@163.com
 * @date 2017年6月15日 上午11:07:56
 */
class Sysinfos extends Base
{

	public function _initialize()
	{
	    //调用父类的构造函数
        parent::_initialize();

	    /*
		check();
        $this->assign('menu', getLeftMenu());

        $this->title = Config::get("project_name");

        $this->inputlist = config('inputlist');
        $this->assign('inputlist',$this->inputlist);
	    */
	}

	/**
	 * 系统设置
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function index() {

		$sysinfo = Sysinfo::all(['type'=>'系统配置']);

		//是否为提交表单
		if (Request::instance()->isPost())
		{

            foreach ($sysinfo as $val)
            {
                $value = Request::instance()->post($val['fieldname']);
                $info = Sysinfo::get(['fieldname'=>$val['fieldname']]);
                $info->val = $value;
                $info->save();
            }

			$this->success('修改成功！');

		}

        $form = new Form();
        $formhtml = array();
        foreach ($sysinfo as $val)
        {
            if($val['ishide'] ==1)//隐藏时跳过本次
            {
                continue;
            }
            $val['vdefault'] = $val['val'];
            $arr['html'] = $form->fieldToForm($val,'form-control');
            $arr['itemname'] = $val['itemname'];

            $formhtml[] = $arr;
        }
        $this->assign('formhtml',$formhtml);



		$this->assign('title','修改系统配置-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
	}

	//配置列表
	public function indexlist()
    {

        // 查询数据集
        $list = Sysinfo::order('rank')->select();

        // 把数据赋值给模板变量list
        $this->assign('list', $list);


        //数据类型
        $this->assign('inputlist',$this->inputlist);

        //获取当当前控制器
        $request = Request::instance();
        $this->assign('act', $request->controller());
        $this->assign('title','字段管理-'.$this->title);
        return $this->fetch();
    }

    /**
     * 添加字段
     * @return \think\mixed
     */
    public function add() {

        //是否为提交表单
        if (Request::instance()->isPost())
        {
            if(!empty(Request::instance()->post('itemname')))
            {
                //检测字段名是否重复
                $this->checkFieldname(Request::instance()->post('fieldname'));

                $dtype=trim(Request::instance()->post('dtype'));
                $maxlength=trim(Request::instance()->post('maxlength'));
                $maxlength=$maxlength >$this->inputlist[$dtype]['length'] ?   $this->inputlist[$dtype]['length']:$maxlength;

                $sysinfo           = new Sysinfo();

                $sysinfo->type      = Request::instance()->post('type');
                $sysinfo->rank      = Request::instance()->post('rank');
                $sysinfo->itemname  = Request::instance()->post('itemname');
                $sysinfo->fieldname = Request::instance()->post('fieldname');
                $sysinfo->dtype     = Request::instance()->post('dtype');
                $sysinfo->vdefault  = Request::instance()->post('vdefault');
                $sysinfo->maxlength = $maxlength;
                $sysinfo->islist    = 0;
                $sysinfo->ishide    = 0;
                //$sysinfo->value  ;

                $sysinfo->save();

                $this->success('添加成功！');
            }else{
                $this->error('字段名不能为空');
            }
        }

        //为添加字段做准备
        $this->assign('title','配置项-'.$this->title);
        $request = Request::instance();
        $this->assign('act', $request->controller());

        return $this->fetch('edit');
    }


    public function edit($id) {

        $sysinfo= Sysinfo::get($id);

        //判断配置是否存在
        if(empty($sysinfo))
        {
            $this->error('要修改的配置不存在');
        }

        //是否为提交表单
        if (Request::instance()->isPost())
        {
            //字段名不能为空
            if(!empty(Request::instance()->post('itemname')))
            {

                //检测字段名是否重复
                $this->checkFieldname(Request::instance()->post('fieldname'));

                $dtype=trim(Request::instance()->post('dtype'));
                $maxlength=trim(Request::instance()->post('maxlength'));
                $maxlength=$maxlength >$this->inputlist[$dtype]['length'] ?   $this->inputlist[$dtype]['length']:$maxlength;

                $sysinfo->type      = Request::instance()->post('type');
                $sysinfo->rank      = Request::instance()->post('rank');
                $sysinfo->itemname  = Request::instance()->post('itemname');
                $sysinfo->fieldname = Request::instance()->post('fieldname');
                $sysinfo->dtype     = Request::instance()->post('dtype');
                $sysinfo->vdefault  = Request::instance()->post('vdefault');
                $sysinfo->maxlength = $maxlength;
                $sysinfo->islist    = 0;
                $sysinfo->ishide    = 0;
                //$sysinfo->value  ;

                $sysinfo->save();

                $this->success('修改成功！');
            }else{
                $this->error('字段名不能为空！');
            }
        }


        $this->assign('temp',$sysinfo);
        $this->assign('title','修改配置-'.$this->title);
        $request = Request::instance();
        $this->assign('act', $request->controller());

        return $this->fetch();
    }

    /**
     * 删除配置
     * @param unknown $id
     * @return \think\mixed
     */
    public function del($id) {

        $sysinfo= Sysinfo::get($id);

        //判断模型是否存在
        if(empty($sysinfo))
        {
            $this->error('要修改的模型不存在');
        }else{

            $sysinfo ->delete();
            $this->success('删除配置成功！');
        }
        $this->assign('title','删除配置-'.$this->title);
        $request = Request::instance();
        $this->assign('act', $request->controller());
        return $this->fetch();
    }

    //检测字段名是否重复
    public function checkFieldname($name)
    {
        //检测字段名是否重复
        $info= Sysinfo::get(["fieldname"=>$name]);
        //判断字段名是否存在
        if(!empty($info))
        {
            $this->error('字段名重复');
        }
    }

}
