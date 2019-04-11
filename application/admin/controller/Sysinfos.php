<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Config;
use app\admin\model\Sysinfo;
use app\admin\model\Field;
use lib\Form;


/**
 * 用户管理控制器
 * @author myeoa
 * @email  6731834@163.com
 * @date 2017年6月15日 上午11:07:56
 */
class Sysinfos extends Controller
{
	public $title;
    public $inputlist;

	public function _initialize()
	{
		check();
        $this->assign('menu', getLeftMenu());

        $this->title = Config::get("project_name");

        $this->inputlist = config('inputlist');
        $this->assign('inputlist',$this->inputlist);
	}

	/**
	 * 系统设置
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function index() {

		$sysinfo = Sysinfo::get(1);

		//判断系统配置是否存在
		if(empty($sysinfo))
		{
			$this->error('要修改的系统配置不存在');
		}

		//是否为提交表单
		if (Request::instance()->isPost())
		{

			$sysinfo->webname    = Request::instance()->post('webname');
			$sysinfo->site    	 = Request::instance()->post('site');
			$sysinfo->title      = Request::instance()->post('title');
			$sysinfo->keywords   = Request::instance()->post('keywords');
			$sysinfo->description= Request::instance()->post('description');
			$sysinfo->withdrawals= Request::instance()->post('withdrawals');
			$sysinfo->appid      = Request::instance()->post('appid');
			$sysinfo->appsecret  = Request::instance()->post('appsecret');
			$sysinfo->mchid      = Request::instance()->post('mchid');
			$sysinfo->apikey     = Request::instance()->post('apikey');
            $sysinfo->everyprice     = Request::instance()->post('everyprice');
            $sysinfo->stickprice     = Request::instance()->post('stickprice');
            $sysinfo->qcode     = Request::instance()->post('qcode');
            $sysinfo->er     = Request::instance()->post('er');
            $sysinfo->p_number = Request::instance()->post('p_number');
            $sysinfo->shopprice = Request::instance()->post('shopprice');
            $sysinfo->headline = Request::instance()->post('headline');
			$sysinfo->save();
			$this->success('修改成功！');

		}

        $form = new Form();

        $field = Field::get(['mid'=>1,'fieldname'=>'qcode']);
        $field['vdefault'] = $sysinfo['qcode'];
        $html['qcode'] = $form->fieldToForm($field,'form-control','qcode');


        $fielder = Field::get(['mid'=>1,'fieldname'=>'er']);
        $fielder['vdefault'] = $sysinfo['er'];
        $html['er'] = $form->fieldToForm($fielder,'form-control','er');



        $this->assign('html',$html);



        $this->assign('temp',$sysinfo);

		$this->assign('title','修改系统配置-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch('edit');
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
                $fieldname=Request::instance()->post('fieldname');
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
                $sysinfo->maxlength = Request::instance()->post('maxlength');
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
}
