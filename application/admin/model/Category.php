<?php
namespace app\admin\model;

class Category extends Basemodel
{

	//自定义初始化
	protected function initialize()
	{
		//需要调用`Model`的`initialize`方法
		parent::initialize();
		//TODO:自定义的初始化
	}

    public function getTree($pid, &$sort ,$str ='')
    {
        $list = parent::where('pid','=',$pid)->order('order')->select();
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


}
?>