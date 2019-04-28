<?php
namespace app\index\controller;

use think\Request;
use app\admin\model\Sysinfo;
use app\admin\model\Article;
use app\admin\model\Category;
use app\admin\model\Cart;
use app\admin\model\Member;


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
    public function indexArtList($type = 2, $cid = 0, $order = 'update,desc', $limit = '0,10')
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
                    $data[$k]['thumb'] = $this->http.$data[$k]['thumb'];
                    $data[$k]['cid'] = $val['cid'];
                    $data[$k]['update'] = date("Y-m-d H:i:s", $val['update']);
                    $data[$k]['info'] = htmltotext($val['body'],200);
                    $data[$k]['specs'] = $val['specs'];
                    $data[$k]['unit'] = $val['unit'];
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

    /**文章详情页
     * @param int $id
     */
    public function artItem($id=0)
    {
        try
        {
            $art  = Article::get($id);
            $art['click'] = $art['click'] + 1;
            $art->save();

            if(empty($art))
            {
                $re = array(
                    'status'=>0,
                    'data'=>'数据为空'
                );
            }else{
                $data = array();
                $data['id'] = $art['id'];
                $data['title'] = $art['title'];
                $data['thumb'] = empty($art['thumb']) ? '/theme/images/nopic.jpg':$art['thumb'];
                $data['thumb'] = $this->http.$data['thumb'];
                $data['cid'] = $art['cid'];
                $data['update'] = date("Y-m-d H:i:s", $art['update']);
                $data['info'] = htmltotext($art['body'],200);
                $data['body'] = $art['body'];
                $data['click'] = $art['click'];
                $data['keywords'] = $art['keywords'];
                $data['description'] = $art['description'];
                $data['author'] = $art['author'];
                $data['specs'] = $art['specs'];
                $data['unit'] = $art['unit'];
                $data['cid'] = $art['cid'];

                $re = array(
                    'status'=>1,
                    'data'=>$data
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

    //  /index/index/artlist/cid/4/page/1/order/update,desc/site/10
    //  /index/index/artlist/cid/4/page/1/order/update,desc
    //  /index/index/artlist/cid/4/page/1
    //  /index/index/artlist/cid/4

    /**文章列表
     * @param int $cid   栏目id
     * @param int $page  分页页码
     * @param string $order  排序
     * @param int $site  每页数量
     */
    public function artlist($cid = 0, $page=1, $order = 'update,desc', $size=10)
    {
        try
        {
            $cid = empty($cid) ? 0:$cid;
            $page = empty($page) ? 1:$page;
            $order = empty($order) ? 'update,desc':$order;
            $size = empty($size) ? 10:$size;

            $cate = new Category;
            $catearr = Category::all();
            $ids = $cate->getAllChild($catearr,$cid);
            $ids = empty($catearr) ? '':$cid.','.implode(',',$ids);


            $arti = new Article;
            $limit[0] = ($page-1)*$size;
            $limit[1] = $size;
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
                    $data[$k]['thumb'] = $this->http.$data[$k]['thumb'];
                    $data[$k]['cid'] = $val['cid'];
                    $data[$k]['specs'] = $val['specs'];
                    $data[$k]['unit'] = $val['unit'];
                    $data[$k]['update'] = date("Y-m-d H:i:s", $val['update']);
                    $data[$k]['info'] = htmltotext($val['body'],200);
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

    //  /index/index/searchlist/key/机油/cid/4/page/1/order/update,desc/site/10
    //  /index/index/searchlist/key/机油/cid/4/page/1/order/update,desc
    //  /index/index/searchlist/key/机油/cid/4/page/1
    //  /index/index/searchlist/key/机油/cid/4
    //  /index/index/searchlist/key/机油

    /** 搜索列表
     * @param string $key  搜索词
     * @param int $cid   栏目id
     * @param int $page  分页页码
     * @param string $order  排序
     * @param int $size  每页数量
     */
    public function searchlist($key, $cid = 0, $page=1, $order = 'update,desc', $size=10)
    {
        $cid = empty($cid) ? 0:$cid;
        $page = empty($page) ? 1:$page;
        $order = empty($order) ? 'update,desc':$order;
        $size = empty($size) ? 10:$size;
        try
        {
            $cate = new Category;
            $catearr = Category::all();
            $ids = $cate->getAllChild($catearr,$cid);
            $ids = empty($catearr) ? '':$cid.','.implode(',',$ids);

            $arti = new Article;
            $limit[] = ($page-1)*$size;
            $limit[] = $size;
            $order = explode(',',$order);
            $art = $arti->where('title','like','%'.$key.'%')->whereIn('cid', $ids)->order($order[0], $order[1])->limit($limit[0], $limit[1])->select();

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
                    $data[$k]['thumb'] = $this->http.$data[$k]['thumb'];
                    $data[$k]['cid'] = $val['cid'];
                    $data[$k]['specs'] = $val['specs'];
                    $data[$k]['unit'] = $val['unit'];
                    $data[$k]['update'] = date("Y-m-d H:i:s", $val['update']);
                    $data[$k]['info'] = htmltotext($val['body'],200);
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

    //  /index/Index/cateList/cid/1/limit/0,10/order/update,desc
    //  /index/Index/cateList/cid/1/limit/0,10
    //  /index/Index/cateList/cid/1

    /**获取下级分类
     * @param $cid
     * @param string $limit
     * @param string $order
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cateList($cid=0, $limit = '0,10', $order = 'update,desc')
    {
        try
        {
            $cid = empty($cid) ? 0:$cid;
            $limit = empty($limit) ? '0,10':$limit;
            $order = empty($order) ? 'update,desc':$order;

            $cate = new Category;
            $limit = explode(',',$limit);
            $order = explode(',',$order);
            $list = $cate->where('pid', $cid)->order($order[0], $order[1])->limit($limit[0], $limit[1])->select();

            if(empty($list))
            {
                $re = array(
                    'status'=>0,
                    'data'=>'数据为空'
                );
            }else{
                $data = array();
                foreach ($list as $k=>$val)
                {
                    $data[$k]['id'] = $val['id'];
                    $data[$k]['name'] = $val['name'];
                    $data[$k]['img'] = empty($val['img']) ? '/theme/images/nopic.jpg':$val['img'];
                    $data[$k]['img'] = $this->http.$data[$k]['img'];
                    $data[$k]['keywords'] = $val['keywords'];
                    $data[$k]['description'] = $val['description'];
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

    //  /index/Index/category/id/8

    /**获取栏目内容
     * @param $id
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function category($id)
    {
        try
        {
            $cate = new Category;
            $cate = $cate->where('id',$id)->find();
            if(empty($cate))
            {
                $re = array(
                    'status'=>0,
                    'data'=>'数据为空'
                );
            }else{
                $data = array();
                //$data['id'] = $cate['id'];
                $data['name'] = $cate['name'];
                $data['img'] = empty($cate['img']) ? '/theme/images/nopic.jpg':$cate['img'];
                $data['img'] = $this->http.$data['img'];
                $data['keywords'] = $cate['keywords'];
                $data['description'] = $cate['description'];
                $data['content'] = $cate['content'];

                $re = array(
                    'status'=>1,
                    'data'=>$data
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

    protected function check()
    {
        return 2;
    }

    // /index/Index/addCart
    //  post 表单项  ///
    //  num  数量
    //  unit 单位
    //  specs 规格
    //  pid  文章id
    /**添加到购物车
     * @return string
     */
    public function addCart()
    {
        $mid = $this->check();

        $data=array(
            'num'=> Request::instance()->post('num'),
            'unit'=> Request::instance()->post('unit'),
            'specs'=> Request::instance()->post('specs'),
            'pid'=> Request::instance()->post('pid')
        );
        /*
        $data=array(
            'num'=> '4',
            'unit'=> '单位',
            'specs'=> '规格',
            'pid'=> '1',
        );*/

        $result = $this->validate($data,'admin/Cart');
        if(true !== $result){
            // 验证失败 输出错误信息
            $re = array(
                'status'=>-1,
                'msg'=>$result
            );
        }else{
            $data['update'] = time();
            $data['mid'] = $mid;
            $cart = new Cart;
            $cart->allowField(true)->data($data)->save();

            $re = array(
                'status'=>1,
                'msg'=>'添加成功'
            );
        }
        return json_encode($re);
    }


    //  /index/index/cartlist

    /**获取会员购物车列表
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cartlist()
    {
        $mid = $this->check();
        $cart = new Cart;
        $list = $cart->where('mid',$mid)->order('update','desc')->select();

        if(!empty($list))
        {
            $data =array();
            foreach ($list as $k=>$val)
            {
                $data[$k]['id'] = $val['id'];
                $data[$k]['pid'] = $val['pid'];
                $data[$k]['num'] = $val['num'];
                $data[$k]['unit'] = $val['unit'];
                $data[$k]['specs'] = $val['specs'];
            }

            $re = array(
                'status'=>1,
                'data'=>array(
                    'list'=>$data
                )
            );
        }else{
            $re = array(
                'status'=>0,
                'data'=>'数据为空'
            );
        }
        return json_encode($re);
    }


    //  /index/index/cartdel/id/9
    /** 删除购物车项
     * @param int $id
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cartdel($id=0)
    {
        $mid = $this->check();
        $cart = new Cart;
        $list = $cart->where('mid',$mid)->where('id',$id)->find();

        if(!empty($list))
        {
            $list->delete();
            $re = array(
                'status'=>1,
                'data'=>'删除成功！'
            );
        }else{
            $re = array(
                'status'=>0,
                'data'=>'数据为空'
            );
        }
        return json_encode($re);
    }


    public function login()
    {
        if (Request::instance()->isPost())
        {
            $user  =  Request::instance()->post('account');
            $password  =   Request::instance()->post('password');
            $password = md5($password);


            $member = Member::where('user',$user)->where('password',$password)->find();
            if(empty($member))
            {
                $re = array(
                    'status'=>0,
                    'data'=>'数据为空'
                );
            }else{
                $verif = randomkeys(6);
                $member->verif = $verif;
                $member->save();

                $data = array();
                $data['account'] = $user;
                $data['verif'] = $verif;
                $re = array(
                    'status'=>1,
                    'data'=>$data
                );
            }
            return json_encode($re);
        }

    }

}
