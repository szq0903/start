<?php
namespace app\index\controller;

use app\admin\model\Sysinfo;
use app\admin\model\Article;
use app\admin\model\Category;

/* status  1为正常有数据  0为没有数据   -1 为错误
 * msg status为-1时有错误提示内容
 * data status为1时  是数据主体
 * */

class Index extends Base
{
    public $http;
	public function _initialize()
	{
        $this->http = 'http://'.$_SERVER['HTTP_HOST'];
        header("Access-Control-Allow-Origin: *");
	}

	public function index()
    {

    }

	public function indexabout()
    {
        $title = Sysinfo::get(['fieldname'=>'about']);
        $title = $title['val'];

        $companyPhoto = Sysinfo::get(['fieldname'=>'aboutimg']);
        $companyPhoto = $this->http.$companyPhoto['val'];

        $companyIntro = Sysinfo::get(['fieldname'=>'aboutcontent']);
        $companyIntro = $companyIntro['val'];


        $data = array(
            'status'=>1,
            'data'=>array(
                'title'=>$title,
                'companyPhoto'=>$companyPhoto,
                'companyIntro'=>$companyIntro
            )
        );
        return json_encode($data);
    }


    public function indexcontact()
    {
        $contact = Sysinfo::get(['fieldname'=>'contact']);
        $contact = $contact['val'];

        $contactname = Sysinfo::get(['fieldname'=>'contactname']);
        $contactname = $contactname['val'];

        $contacttel = Sysinfo::get(['fieldname'=>'contacttel']);
        $contacttel = $contacttel['val'];

        $contactemail = Sysinfo::get(['fieldname'=>'contactemail']);
        $contactemail = $contactemail['val'];

        $contactadd = Sysinfo::get(['fieldname'=>'contactadd']);
        $contactadd = $contactadd['val'];

        $data = array(
            'status'=>1,
            'data'=>array(
                'contact'=>$contact,
                'contactname'=>$contactname,
                'contacttel'=>$contacttel,
                'contactemail'=>$contactemail,
                'contactadd'=>$contactadd
            )
        );
        return json_encode($data);
    }

    //   /index/Index/indexArtList/type/1/cid/4/limit/1,5/order/id,asc
    //   /index/Index/indexArtList/type/1/cid/4/limit/1,5
    //   /index/Index/indexArtList/type/1/cid/4
    //   /index/Index/indexArtList/type/1
    /**获取文章列表
     * @param int $type  0为单页  1为新闻 2为产品 默认 0
     * @param int $cid  栏目id  默认 0
     * @param string $limit  范围 默认 '0,10'
     * @param string $order 排序  默认  'update,desc'
     */
    public function indexArtList($type = 1, $cid = 0, $order = 'update,desc', $limit = '0,10')
    {
        try
        {
            //获取分类id和他的下级id  ids
            if($cid == 0)
            {
                $wh = array('type'=> $type);
            }else{
                $wh = array('type'=> $type, 'id'=>$cid);
            }

            $cate = new Category;
            $catearr = Category::all($wh);


            $ids = $cate->getAllChild($catearr,$cid);

            $ids = empty($catearr) ? '':$cid.','.implode(',',$ids);

            $arti = new Article;

            $limit = explode(',',$limit);
            $order = explode(',',$order);
            $art = $arti->whereIn('cid', $ids)->order($order[0], $order[1])->limit($limit[0], $limit[1])->select();


            if(empty($art))
            {
                $re = array(
                    'status'=>0,
                    'data'=>'数据为空'
                );
            }else{
                $data = array();
                foreach ($art as $k=>$val)
                {
                    $data[$k]['id'] = $val['id'];
                    $data[$k]['title'] = $val['title'];
                    $data[$k]['thumb'] = empty($val['thumb']) ? '/theme/images/nopic.jpg':$val['thumb'];
                    $data[$k]['cid'] = $val['cid'];
                    $data[$k]['update'] = date("Y-m-d H:i:s", $val['update']);
                }
                $re = array(
                    'status'=>1,
                    'data'=>array(
                        'list'=>$data
                    )
                );
            }
        } //捕获异常
        catch(Exception $e)
        {
            $re = array(
                'status'=>-1,
                'msg'=>$e->getMessage()
            );
        }

        return json_encode($re);

    }



}
