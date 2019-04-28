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

    /**获取平级的栏目
     * @param $pid
     * @return bool|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getBrother($pid)
    {
        $cat = parent::where('id','=',$pid)->order('order')->select();
        if(empty($cat))
        {
            return false;
        }else{

            $list = parent::where('pid','=',$cat['id'])->order('order')->select();
            if(!empty($list))
            {
                return $list;
            }else{
                return false;
            }
        }
    }

    //递归获取所有的子分类的ID
    public function getAllChild($array,$id){
        $arr = array();
        foreach($array as $v){
            if($v['pid'] == $id){
                $arr[] = $v['id'];
                $arr = array_merge($arr,$this->getAllChild($array,$v['id']));
            }
        };
        return $arr;
    }


    //递归获取所有的子分类的ID
    public function getProTree($pid, &$sort ,$str ='')
    {
        $list = parent::where('pid','=',$pid)->where('type', 2)->order('order')->select();

        if(!empty($list))
        {
            foreach($list as $k=>$v)
            {
                if($str <> '')
                {
                    $v['name']=$str.'├─'.$v['name'];
                }
                $sort[] = $v;
                $this->getProTree($v->id,$sort,$str.'&nbsp;&nbsp;&nbsp;');
            }
        }
    }






    public function getTypeAttr($value)
    {
        $arr = array('单页','新闻','产品');
        if(empty($value))
        {
            return '单页';
        }else{
            return $arr[$value];
        }
    }


}
?>