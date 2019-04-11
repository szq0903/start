<?php
namespace app\admin\controller;

use think\Cache;
use think\Log;
/**
 * 用户管理控制器
 * @author myeoa
 * @email  6731834@163.com
 * @date 2017年6月15日 上午11:07:56
 */
class Clear extends Base
{

	public function _initialize()
	{
        //调用父类的构造函数
        parent::_initialize();
	}


	public function index() {
		Cache::clear();
		Log::clear();
		$this->success('清理成功');
	}

}
