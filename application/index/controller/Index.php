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
            $data['update'] = time();
            $data['mid'] = $mid;
            $item = Cart::get(['pid'=>$data['pid'], 'mid'=>$mid]);
            if(empty($item))
            {
                $cart = new Cart;
                $cart->allowField(true)->data($data)->save();
                $re = array(
                    'status'=>1,
                    'msg'=>'添加成功'
                );
            }else{
                $item['num'] = $item['num'] + $data['num'];
                $item->save();
                $re = array(
                    'status'=>1,
                    'msg'=>'修改成功'
                );
            }

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
                'msg'=>'修改成功'
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
    public function register()
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
        $member = Member::where('id',$mid)->find();

        if(empty($member))
        {

            $re = array(
                'status'=>0,
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

            $login = LoginLog::where('mid', $member['id'])->order('update', 'desc')->find();

            if(empty($login))
            {
                $data['latelogin'] = date("Y-m-d H:i:s", $member['update']);
            }else{
                $data['latelogin'] = date("Y-m-d H:i:s", $login['update']);
            }

            $re = array(
                'status'=>1,
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

            if(empty($art))
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
        $order = Order::get(['id'=>$id, 'mid'=>$mid]);

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
    //  /index/index/browseLogList/uid/1/verif/page/1/order/update,desc/size/10
    //  /index/index/browseLogList/uid/1/verif/page/1/order/update,desc
    //  /index/index/browseLogList/uid/1/verif/page/1
    //  /index/index/browseLogList/uid/1/verif/
    public function browseLogList($uid, $verif, $page=1, $order = 'update,desc', $size=10)
    {
        $mid = $this->check($uid, $verif);

        $limit[0] = ($page-1)*$size;
        $limit[1] = $size;
        $order = explode(',',$order);
        $loglist = new BrowseLog;
        $req = $loglist->field('*,COUNT(*) as size')->where('mid',$mid)->group('aid')->order($order[0], $order[1])->limit($limit[0], $limit[1])->select();

        if(empty($req))
        {
            $re = array(
                'status'=>0,
                'data'=>'数据为空'
            );
        }else{
            $data = array();
            foreach ($req as $k=>$val)
            {
                $data[$k]['aid'] = $val['aid'];
                $data[$k]['mid'] = $val['mid'];
                $data[$k]['size'] = $val['size'];
            }
            $re = array(
                'status'=>1,
                'data'=>$data
            );
        }
        return json_encode($re);
    }

    //分享二维码
    public function shareQqrcode($uid, $verif)
    {
        $mid = $this->check($uid, $verif);
    }

    //查看数据列表
    public function  record($uid, $verif)
    {
        $mid = $this->check($uid, $verif);

        
    }


    //我的名片
    //名片编辑
}
