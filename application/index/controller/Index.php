<?php
namespace app\index\controller;


use think\Request;
use think\Db;
use app\admin\model\Sysinfo;
use app\admin\model\Article;
use app\admin\model\Category;
use app\admin\model\Cart;
use app\admin\model\Member;
use app\admin\model\LoginLog;
use app\admin\model\Order;
use app\admin\model\Orderitem;
use app\admin\model\BrowseLog;
use app\admin\model\Card;



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

    //  /index/index/artlist/cid/4/page/1/order/update,desc/size/10
    //  /index/index/artlist/cid/4/page/1/order/update,desc
    //  /index/index/artlist/cid/4/page/1
    //  /index/index/artlist/cid/4

    /**文章列表
     * @param int $cid   栏目id
     * @param int $page  分页页码
     * @param string $order  排序
     * @param int $size  每页数量
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

    //  /index/index/searchlist/key/机油/cid/4/page/1/order/update,desc/size/10
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

    protected function check($uid, $verif)
    {
        $member = Member::where('id',$uid)->where('verif',$verif)->find();
        if(empty($member))
        {
            $re = array(
                'status'=>0,
                'data'=>'会员登陆失败'
            );
            return json_encode($re);
        }else{
            return $member['id'];
        }

    }

    protected function checkinfo($uid)
    {
        $member = Member::where('id',$uid)->find();

        $data = array();
        $data['name'] = $member['name'];
        $data['phone'] = $member['phone'];
        $data['email'] = $member['email'];
        $data['user'] = $member['user'];

        $result = $this->validate($data,'admin/Member.check');

        if(true !== $result){
            // 验证失败 输出错误信息
            $re = false;
        }else{
            $re = true;
        }
        return $re;
    }





    // /index/Index/addCart/uid/2/verif/q1ledf
    //  post 表单项  ///
    //  num  数量
    //  unit 单位
    //  specs 规格
    //  pid  文章id

    /**添加到购物车
     * @return string
     */
    public function addCart($uid, $verif)
    {
        $mid = $this->check($uid, $verif);
        $isInfo = $this->checkinfo($uid);
        if($isInfo)
        {
            $data=array(
                'num'=> Request::instance()->post('num'),
                'unit'=> Request::instance()->post('unit'),
                'specs'=> Request::instance()->post('specs'),
                'pid'=> Request::instance()->post('pid')
            );

            $result = $this->validate($data,'admin/Cart');
            if(true !== $result){
                // 验证失败 输出错误信息
                $re = array(
                    'status'=>-1,
                    'isInfo'=>$isInfo,
                    'msg'=>$result
                );
            }else{
                $data['update'] = time();
                $data['mid'] = $mid;
                $item = Cart::get(['pid'=>$data['pid'], 'mid'=>$mid]);
                if(empty($item))
                {
                    $cart = new Cart;
                    $cart->allowField(true)->data($data)->save();
                    $re = array(
                        'status'=>1,
                        'isInfo'=>$isInfo,
                        'msg'=>'添加成功'
                    );
                }else{
                    $item['num'] = $item['num'] + $data['num'];
                    $item->save();
                    $re = array(
                        'status'=>1,
                        'isInfo'=>$isInfo,
                        'msg'=>'修改成功'
                    );
                }

            }
        }else{
            $re = array(
                'status'=>-1,
                'isInfo'=>$isInfo,
                'msg'=>'信息不完整'
            );
        }

        return json_encode($re);
    }


    //  /index/index/cartlist/uid/2/verif/q1ledf

    /**获取会员购物车列表
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cartlist($uid, $verif)
    {
        $mid = $this->check($uid, $verif);
        $isInfo = $this->checkinfo($mid);


        $cart = new Cart;
        $list = $cart->where('mid',$mid)->order('update','desc')->select();

        if(!empty($list))
        {
            $data =array();
            foreach ($list as $k=>$val)
            {
                $data[$k]['id'] = $val['id'];
                $data[$k]['pid'] = $val->getData('pid');
                $data[$k]['pname'] = $val['pid'];
                $data[$k]['num'] = $val['num'];
                $data[$k]['unit'] = $val['unit'];
                $data[$k]['specs'] = $val['specs'];
            }
            $re = array(
                 'status'=>1,
                 'isInfo'=>$isInfo,
                 'data'=>array(
                     'list'=>$data
                 ),
            );
        }else{
            $re = array(
                'status'=>0,
                'isInfo'=>$isInfo,
                'data'=>'数据为空',

            );
        }
        return json_encode($re);
    }


    //  /index/index/cartdel/uid/2/verif/q1ledf/id/9
    /** 删除购物车项
     * @param int $id
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cartdel($uid, $verif,$id=0)
    {
        $mid = $this->check($uid, $verif);
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

    //  /index/index/cartedit/uid/1/verif/q1ledf/id/1/num/8
    /**修改购物车数量
     * @param $uid
     * @param $verif
     * @param int $id
     * @param $num
     * @return string
     * @throws \think\exception\DbException
     */
    public function cartedit($uid, $verif,$id=0,$num)
    {
        $mid = $this->check($uid, $verif);
        $cart = Cart::get(['id'=>$id,'mid'=>$mid]);
        if(empty($cart))
        {
            $re = array(
                'status'=>0,
                'data'=>'数据为空'
            );
        }else{
            if($num == 0)
            {
                $cart->delete();
            }else{
                $cart['num'] = $num;
                $cart->save();
            }
            $re = array(
                'status'=>1,
                'data'=>'修改成功！'
            );

        }
        return json_encode($re);
    }


    //  /index/index/addOrderByCart/uid/1/verif/q1ledf/
    //  post content-type 为 application/json
    //  status   状态
    //  msg   出错信息
    //  data  Cart id数组

    //购物车添加到订单
    public function addOrderByCart($uid, $verif){
        $mid = $this->check($uid, $verif);

        //获取post json 数据
        $json = file_get_contents('php://input');

        $re = json_decode($json, true);


        if($re['status'] == 0)
        {
            $re = array(
                'status'=>0,
                'msg'=>$re['msg']
            );
        }else{
            if(is_array($re['data']))
            {
                //添加订单
                $order = new Order;
                $order['ordernum'] = makeorder();
                $order['mid'] = $mid;
                $order->update = time();
                $order->save();
                $order->id;

                foreach ($re['data'] as $val)
                {
                    $cart = Cart::get(['id'=>$val,'mid'=>$mid]);
                    if(!empty($cart))
                    {
                        //添加订单项目
                        $Orderitem = new Orderitem;
                        $Orderitem['oid'] = $order->id;
                        $Orderitem['pid'] = $cart->getData('pid');
                        $Orderitem['specs'] =$cart->getData('specs');
                        $Orderitem['unit'] = $cart->getData('unit');
                        $Orderitem['num'] = $cart->getData('num');
                        $Orderitem->update = time();
                        $Orderitem->save();

                        //删除购物车项
                        $cart->delete();
                    }

                }
                $re = array(
                    'status'=>1,
                    'data'=>'修改成功！'
                );
            }else{
                $re = array(
                    'status'=>0,
                    'msg'=>'没有项目可添加'
                );
            }
        }
        return json_encode($re);
    }

    // /index/Index/addOrderByPro/uid/2/verif/q1ledf
    //  post 表单项  ///
    //  num  数量
    //  unit 单位
    //  specs 规格
    //  pid  文章id

    /** 产品下单页 直接添加订单
     * @param $uid
     * @param $verif
     * @return string
     */
    public function addOrderByPro($uid, $verif)
    {
        $mid = $this->check($uid, $verif);

        $data=array(
            'num'=> Request::instance()->post('num'),
            'unit'=> Request::instance()->post('unit'),
            'specs'=> Request::instance()->post('specs'),
            'pid'=> Request::instance()->post('pid')
        );


        $result = $this->validate($data,'admin/Cart');

        if(true !== $result){
            // 验证失败 输出错误信息
            $re = array(
                'status'=>-1,
                'msg'=>$result
            );
        }else{
            //添加订单
            $order = new Order;
            $order['ordernum'] = makeorder();
            $order['mid'] = $mid;
            $order->update = time();
            $order->save();

            //添加订单项
            $Orderitem = new Orderitem;
            $data['update'] = time();
            $data['oid'] = $order->id;
            $Orderitem->allowField(true)->data($data)->save();

            $re = array(
                'status'=>1,
                'msg'=>'下单成功'
            );
        }
        return json_encode($re);
    }



    //  /index/index/login/
    //  POST 提交数据
    //  account  用户名  password  密码

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
                    'data'=>'用户名密码错误'
                );
            }else{
                $verif = randomkeys(6);
                $member->verif = $verif;
                $member->save();

                //添加登陆记录
                $login = new LoginLog;
                $login['mid'] = $member['id'];
                $login['update'] = time();
                $login->save();

                $data = array();
                $data['uid'] = $member['id'];
                $data['verif'] = $verif;
                $re = array(
                    'status'=>1,
                    'data'=>$data
                );
            }
            return json_encode($re);
        }
    }

    //  /index/index/register

    //  POST 提交数据
    //  account  用户名
    //  password  密码
    //  phone  手机号
    //  email  邮箱
    //  name  姓名

    /**注册用户
     * @return string
     */
    public function register($pid=0)
    {
        if (Request::instance()->isPost())
        {
            $data=array(
                'user'=> Request::instance()->post('account'),
                'password'=> Request::instance()->post('password'),
                'password_confirm'=> Request::instance()->post('password_confirm'),
                'phone'=> Request::instance()->post('phone'),
                'email'=> Request::instance()->post('email'),
                'name' => Request::instance()->post('name')
            );
            $me = Member::get(['user'=>$data['user']]);

            if(empty($me))
            {
                $result = $this->validate($data,'admin/Member');

                if(true !== $result){
                    // 验证失败 输出错误信息
                    $re = array(
                        'status'=>-1,
                        'msg'=>$result
                    );
                }else{
                    $data['password'] = md5($data['password']);

                    $data['update'] = time();
                    $member = new Member;
                    $member->allowField(true)->data($data)->save();

                    $re = array(
                        'status'=>1,
                        'msg'=>'添加成功'
                    );
                }
            }else{
                $re = array(
                    'status'=>-1,
                    'msg'=>'用户名已存在'
                );
            }
            return json_encode($re);
        }
    }

    /**完善用户
     * @return string
     */
    public function perfect($uid, $verif)
    {
        if (Request::instance()->isPost())
        {
            $data=array(
                'user'=> Request::instance()->post('account'),
                'password'=> Request::instance()->post('password'),
                'password_confirm'=> Request::instance()->post('password_confirm'),
                'phone'=> Request::instance()->post('phone'),
                'email'=> Request::instance()->post('email'),
                'name' => Request::instance()->post('name')
            );
            $me = Member::get(['user'=>$data['user']]);

            if(empty($me))
            {
                $result = $this->validate($data,'admin/Member');

                if(true !== $result){
                    // 验证失败 输出错误信息
                    $re = array(
                        'status'=>-1,
                        'msg'=>$result
                    );
                }else{
                    $data['password'] = md5($data['password']);

                    $data['update'] = time();
                    $mid = $this->check($uid, $verif);
                    $member = Member::get($mid);
                    $member->allowField(true)->data($data)->save();

                    $re = array(
                        'status'=>1,
                        'msg'=>'添加成功'
                    );
                }
            }else{
                $re = array(
                    'status'=>-1,
                    'msg'=>'用户名已存在'
                );
            }
            return json_encode($re);
        }
    }

    // /index/Index/member/uid/1/verif/q1ledf

    /**会员中心信息
     * @param $uid
     * @param $verif
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function member($uid, $verif)
    {
        $mid = $this->check($uid, $verif);
        $isInfo = $this->checkinfo($mid);
        $member = Member::where('id',$mid)->find();

        if(empty($member))
        {
            $re = array(
                'status'=>0,
                'isInfo'=>$isInfo,
                'data'=>'用户名密码错误'
            );
        }else{
            $data = array();
            $data['uid'] = $member['id'];
            $data['headimgurl'] = empty($member['headimgurl']) ? '/theme/images/headimg.png':$member['headimgurl'];
            if(!strstr($data['headimgurl'],'http'))
            {
                $data['headimgurl'] = $this->http.$data['headimgurl'];
            }

            $data['levelid'] = $member['levelid'];
            $data['name'] = $member['name'];
            $data['email'] = $member['email'];
            $data['phone'] = $member['phone'];
            $data['nickname'] = $member['nickname'];
            $data['city'] = $member['city'];
            $data['isShow'] =$member->getData('levelid') == 1 ? true : false ;
            $login = LoginLog::where('mid', $member['id'])->order('update', 'desc')->find();

            if(empty($login))
            {
                $data['latelogin'] = date("Y-m-d H:i:s", $member['update']);
            }else{
                $data['latelogin'] = date("Y-m-d H:i:s", $login['update']);
            }

            $re = array(
                'status'=>1,
                'isInfo'=>$isInfo,
                'data'=>$data
            );
        }
        return json_encode($re);
    }

    // /index/Index/orderlist/uid/1/verif/q1ledf/page/1/order/update,desc
    // /index/Index/orderlist/uid/1/verif/q1ledf/page/1
    // /index/Index/orderlist/uid/1/verif/q1ledf

    /**我的订单
     * @param $uid
     * @param $verif
     * @param int $page
     * @param string $order
     * @param int $size
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function orderlist($uid, $verif, $page=1, $order = 'update,desc', $size=10)
    {
        $mid = $this->check($uid, $verif);
        try
        {
            $page = empty($page) ? 1:$page;
            $order = empty($order) ? 'update,desc':$order;
            $size = empty($size) ? 10:$size;

            $or = new Order;
            $limit[0] = ($page-1)*$size;
            $limit[1] = $size;
            $order = explode(',',$order);
            $list = $or->where('mid', $mid)->order($order[0], $order[1])->limit($limit[0], $limit[1])->select();

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
                    $data[$k]['ordernum'] = $val['ordernum'];
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

    // /index/index/orderItem/uid/1/verif/q1ledf/id/8

    /** 订单详情
     * @param $uid
     * @param $verif
     * @param int $id
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function orderItem($uid, $verif,$id=0)
    {
        $mid = $this->check($uid, $verif);
        //查出订单
        $order = Order::get(['id'=>$id]);

        if(empty($order))
        {
            $re = array(
                'status'=>0,
                'data'=>'数据为空'
            );
        }else{
            $data = array();

            $data['id'] = $order['id'];
            $data['ordernum'] = $order['ordernum'];
            $data['update'] = date("Y-m-d H:i:s", $order['update']);
            $data['list'] = array();

            //查出订单项
            $list = Orderitem::where('oid',$order['id'])->order('update', 'desc')->select();

            if(!empty($list))
            {
                foreach ($list as $k=>$val)
                {
                    $data['list'][$k]['pname'] = $val['pid'];
                    $data['list'][$k]['pid'] = $val->getData('pid');
                    $data['list'][$k]['specs'] = $val['specs'];
                    $data['list'][$k]['unit'] = $val['unit'];
                    $data['list'][$k]['num'] = $val['num'];
                }
            }
            $re = array(
                'status'=>1,
                'data'=>$data
            );
        }
        return json_encode($re);
    }


    //订单删除 - 联系管理员 删除

    //浏览记录
    //  /index/index/browseLogList/uid/1/verif/ymado7/page/1/order/update,desc/size/10
    //  /index/index/browseLogList/uid/1/verif/ymado7/page/1/order/update,desc
    //  /index/index/browseLogList/uid/1/verif/ymado7/page/1
    //  /index/index/browseLogList/uid/1/verif/ymado7
    /** 浏览记录
     * @param $uid
     * @param $verif
     * @param int $page
     * @param string $order
     * @param int $size
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function browseLogList($uid, $verif, $page=1, $order = 'update,desc', $size=10)
    {
        $mid = $this->check($uid, $verif);

        $limit[0] = ($page-1)*$size;
        $limit[1] = $size;
        $order = explode(',',$order);

        //查找下级用户
        $me = new Member();
        $mlist = $me->where('parentid',$mid)->order($order[0], $order[1])->limit($limit[0], $limit[1])->select();


        if(empty($mlist))
        {
            $re = array(
                'status'=>0,
                'data'=>'数据为空'
            );
        }else{
            $data = array();
            $blog = new BrowseLog;
            foreach ($mlist as $k=>$val)
            {
                //查找最近的浏览记录
                $bl = $blog->where('mid',$val['id'])->order('update','desc')->find();
                if(empty($bl))
                {
                    $data[$k]['latesttime'] = '没有查看产品';
                }else{
                    $data[$k]['latesttime'] = date("Y-m-d H:i:s", $bl['update']);
                }
                $data[$k]['name'] = empty($val['name']) ? $val['nickname']:$val['name'];
                $data[$k]['mid'] = $val['id'];
            }
            $re = array(
                'status'=>1,
                'data'=>$data
            );
        }
        return json_encode($re);
    }

    //浏览记录
    //  /index/index/browseLogItem/uid/1/verif/ymado7/mid/41/page/1/order/update,desc/size/10
    //  /index/index/browseLogItem/uid/1/verif/ymado7/mid/41/page/1/order/update,desc
    //  /index/index/browseLogItem/uid/1/verif/ymado7/mid/41/page/1
    //  /index/index/browseLogItem/uid/1/verif/ymado7/mid/41

    /** 浏览记录详情
     * @param $uid
     * @param $verif
     * @param $mid
     * @param int $page
     * @param string $order
     * @param int $size
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function browseLogItem($uid, $verif,$mid, $page=1, $order = 'update,desc', $size=10)
    {
        $pid = $this->check($uid, $verif);

        $limit[0] = ($page-1)*$size;
        $limit[1] = $size;
        $order = explode(',',$order);

        $me = Member::get(['parentid'=>$pid, 'id'=>$mid]);
        if(empty($me))
        {
            $re = array(
                'status'=>0,
                'data'=>'用户为空'
            );
        }else{
            $blog = new BrowseLog;
            $list = $blog->where('mid',$me['id'])->order($order[0], $order[1])->limit($limit[0], $limit[1])->select();
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
                    $data[$k]['title'] = $val['aid'];
                    $data[$k]['aid'] = $val->getData('aid');
                    $data[$k]['update'] = date("Y-m-d H:i:s", $val['update']);
                }
                $re = array(
                    'status'=>1,
                    'data'=>$data,
                    'name'=>  empty($me['name']) ? $me['nickname']:$me['name']
                );
            }
        }
        return json_encode($re);
    }


    //分享二维码
    public function shareQqrcode($uid, $verif)
    {
        $mid = $this->check($uid, $verif);
    }

    //  /index/Index/record/uid/41/verif/t4wz8s/page/1/order/update,desc

    /**  查看数据列表
     * @param $uid
     * @param $verif
     * @param int $page
     * @param string $order
     * @param int $size
     * @param int $times
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function  record($uid, $verif, $page=1, $order = 'update,desc', $size=10,$times = 0)
    {
        $mid = $this->check($uid, $verif);
        $limit[0] = ($page-1)*$size;
        $limit[1] = $size;
        $order = explode(',',$order);

        if($times != 0)
        {
            //得到给定时间当月的起止时间
            $beginThismonth=mktime(0,0,0,date('m',$times),1,date('Y',$times));
            $endThismonth=mktime(23,59,59,date('m',$times),date('d',$times)+1,date('Y',$times));
        }

        $me = new Member();
        $list = $me->field('*,(SELECT COUNT(*) FROM my_order WHERE my_member.id = my_order.mid) as num')->where('parentid',$mid)->order($order[0], $order[1])->limit($limit[0], $limit[1])->select();
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
                $data[$k]['num'] = $val['num'];
            }
            $re = array(
                'status'=>1,
                'data'=>$data
            );
        }
        return json_encode($re);
    }

    // /index/Index/recordItem/uid/41/verif/q1ledf/sonid/40/page/1/order/update,desc

    /** 查看数据详情
     * @param $uid
     * @param $verif
     * @param int $sonid
     * @param int $page
     * @param string $order
     * @param int $size
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function  recordItem($uid, $verif,$sonid=0, $page=1, $order = 'update,desc', $size=10)
    {
        $mid = $this->check($uid, $verif);

        $me = new Member();
        $da = $me->where('id',$sonid)->where('parentid',$mid)->find();
        if(empty($da))
        {
            $re = array(
                'status'=>0,
                'data'=>'下级用户不存在'
            );
        }else{
            $page = empty($page) ? 1:$page;
            $order = empty($order) ? 'update,desc':$order;
            $size = empty($size) ? 10:$size;

            $or = new Order;
            $limit[0] = ($page-1)*$size;
            $limit[1] = $size;
            $order = explode(',',$order);
            $list = $or->where('mid', $da['id'])->order($order[0], $order[1])->limit($limit[0], $limit[1])->select();

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
                    $data[$k]['ordernum'] = $val['ordernum'];
                    $data[$k]['update'] = date("Y-m-d H:i:s", $val['update']);
                }
                $re = array(
                    'status'=>1,
                    'data'=>array(
                        'list'=>$data
                    )
                );
            }
        }
        return json_encode($re);
    }


    // /index/Index/card/uid/41/verif/q1ledf
    /** 我的名片
     * @param $uid
     * @param $verif
     * @return string
     * @throws \think\exception\DbException
     */
    public function card($uid, $verif)
    {
        $mid = $this->check($uid, $verif);
        $temp = Card::get(['mid' => $mid]);

        if(empty($temp))
        {
            $re = array(
                'status'=>0,
                'data'=>'没有找到图片'
            );
        }else{
            $data = array();

            $data['imgurl'] = empty($temp['imgurl']) ? '/theme/images/headimg.png':$temp['imgurl'];
            if(!strstr($data['imgurl'],'http'))
            {
                $data['imgurl'] = $this->http.$data['imgurl'];
            }
            $data['name'] = $temp['name'];
            $data['phone'] = $temp['phone'];
            $data['job'] = $temp['job'];
            $data['address'] = $temp['address'];
            $re = array(
                'status'=>1,
                'data'=>$data
            );
        }

        return json_encode($re);
    }

    // /index/Index/cardEdit/uid/41/verif/q1ledf
    /** 名片编辑
     * @param $uid
     * @param $verif
     * @return string
     * @throws \think\exception\DbException
     */
    public function cardEdit($uid, $verif)
    {
        $mid = $this->check($uid, $verif);

        $data=array(
            'imgurl'=> Request::instance()->post('imgurl'),
            'name'=> Request::instance()->post('name'),
            'phone'=> Request::instance()->post('phone'),
            'job'=> Request::instance()->post('job'),
            'address'=> Request::instance()->post('address')
        );

        $result = $this->validate($data,'admin/Card');

        if(true !== $result){
            // 验证失败 输出错误信息
            $re = array(
                'status'=>-1,
                'msg'=>$result
            );
        }else{

            $temp = Card::get(['mid' => $mid]);
            if(empty($temp))
            {
                $temp = new Card;
            }

            $temp['imgurl'] = $data['imgurl'];
            $temp['name'] = $data['name'];
            $temp['phone'] = $data['phone'];
            $temp['job'] = $data['job'];
            $temp['address'] = $data['address'];

            $temp['mid'] = $mid;
            $temp->update = time();
            $temp->save();

            $re = array(
                'status'=>1,
                'data'=>'修改成功'
            );
        }
        return json_encode($re);
    }



    //  /index/index/upImages/uid/1/verif/q1ledf/f/file
    /** 上传图片
     * @param $uid
     * @param $verif
     * @param $f
     * @return string
     */
    public function upImages($uid, $verif,$f){

        $mid = $this->check($uid, $verif);

        if(!empty(request() -> file($f)))
        {
            $file = request() -> file($f);
        }
        if(!isset($file))
        {
            // 上传失败获取错误信息
            $re = array(
                'status'=>0,
                'data'=>'没有找到图片'
            );
        }else{
            $file->validate(['size'=>1024*1024*2,'ext'=>'jpg,png,gif']);
            $info = $file->rule('date')->move(ROOT_PATH . 'public' . DS . 'uploads'. DS .'images');

            if($info){
                $re = array(
                    'status'=>1,
                    'data'=>array(
                        'url'=> '/public/uploads/images/' . $info->getSaveName()
                    )
                );

            }else{
                // 上传失败获取错误信息
                $re = array(
                    'status'=>-1,
                    'msg'=>$file->getError()
                );
            }
        }
        return json_encode($re);
    }

    public function upImagesBase64($uid, $verif){

        $mid = $this->check($uid, $verif);

        $base64 = Request::instance()->post('data');
        if(empty($base64))
        {
        // 上传失败获取错误信息
            $re = array(
                'status'=>0,
                'data'=>'没有收到图片'
            );
        }else{
            $img = $this->base64_upload($base64);
            if($img)
            {
                $re = array(
                    'status'=>1,
                    'data'=>$img
                );
            }else{
                $re = array(
                    'status'=>0,
                    'data'=>'没有图片返回'
                );
            }

        }
        return json_encode($re);

    }

    protected function base64_upload($base64) {

        $base64_image = str_replace(' ', '+', $base64);

        //post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)){


            //匹配成功
            $savename = date('Ymd') . DS . md5(microtime(true));

            if($result[2] == 'jpeg'){
                $image_name = $savename.'.jpg';
                //纯粹是看jpeg不爽才替换的
            }else{
                $image_name = $savename.'.'.$result[2];
            }

            $image_dir = ROOT_PATH . 'public' . DS . 'uploads'. DS .'images'. DS .date('Ymd');
            if(!file_exists($image_dir))
            {
                mkdir($image_dir, 0777, true);
            }
            $image_file = ROOT_PATH . 'public' . DS . 'uploads'. DS .'images'. DS .$image_name;

            //服务器文件存储路径
            if (file_put_contents($image_file, base64_decode(str_replace($result[1], '', $base64_image)))){
                $path = DS .'public' . DS . 'uploads'. DS .'images'. DS .$image_name;
                return str_replace(DS,'/',$path);

            }else{
                return false;
            }
        }else{

            return false;
        }
    }

}
