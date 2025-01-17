<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use think\Session;

use app\admin\model\Mould;



function check()
{
	$user = Session::get('user');
	if(empty($user))
	{
		//echo url('index/index/login');exit;
		header('location:' . url('admin/index/login'));exit;
	}
}

//获取左侧菜单
function getLeftMenu()
{
    $mould = Mould::where('sort','>', 0)->where('ishide', 0)->select();
    return $mould;
}


/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 * @param string  $name 字符串
 * @param integer $type 转换类型
 * @param bool    $ucfirst 首字母是否大写（驼峰规则）
 * @return string
 */
function parseName($name, $type = 0, $ucfirst = true)
{
    if ($type) {
        $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return strtoupper($match[1]);
        }, $name);
        return $ucfirst ? ucfirst($name) : lcfirst($name);
    } else {
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}


function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true)
{
 if(function_exists("mb_substr")){
 if($suffix)
  return mb_substr($str, $start, $length, $charset)."...";
 else
  return mb_substr($str, $start, $length, $charset);
 }
 elseif(function_exists('iconv_substr')) {
 if($suffix)
  return iconv_substr($str,$start,$length,$charset)."...";
 else
  return iconv_substr($str,$start,$length,$charset);
 }
 $re['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
 $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
 $re['gbk']  = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
 $re['big5']  = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
 preg_match_all($re[$charset], $str, $match);
 $slice = join("",array_slice($match[0], $start, $length));
 if($suffix) return $slice."…";
 return $slice;
}


function time_tran($the_time){
	//$now_time = date("Y-m-d H:i:s",time()+8*60*60);
   	//$now_time = strtotime($now_time);
	$now_time = time();
   	$show_time = $the_time;
   	$dur = $now_time - $show_time;
	if($dur < 0){
		return $the_time;
   	}elseif($dur < 60){
		return $dur.'秒前';
	}elseif($dur < 3600){
		return floor($dur/60).'分钟前';
	}elseif($dur < 86400){
		return floor($dur/3600).'小时前';
	}elseif($dur < 259200){//3天内
        return floor($dur/86400).'天前';
	}else{
        return date('n月j日',$the_time);
    }
}


function makeradio($arr,$name, $class ,$value = -1)
{
    $carr =array(
        array('rdio-default','radioDefault'),
        array('rdio-primary','radioPrimary'),
        array('rdio-warning','radioWarning'),
        array('rdio-success','radioSuccess'),
        array('rdio-danger','radioDanger'),
    );
    $html = '';
    $i=0;
    foreach ($arr as $key=>$val)
    {
        $checked = '';
        if($i == 0 && $value < 0)
        {
            $checked = 'checked';
        }elseif($value == $key){
            $checked = 'checked';
        }
        $html.= '<div class="rdio '.$carr[$i][0].' '.$class.'">
                  <input type="radio" name="'.$name.'" value="'.$key.'" id="'.$carr[$i][1].'" '.$checked.'>
                  <label for="'.$carr[$i][1].'">'.$val.'</label>
             </div>';
        $i++;
    }
    return $html;
}



//初始化类目文章列表
function getCateArtList($cateart)
{
    $data = array();
    foreach ($cateart as $k=>$item) {
        $data[$k]['update'] = time_tran($item['update']);
        $match = array();
        preg_match_all('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png|jpeg))\"?.+>/isU',$item['body'],$match);
        foreach ($match[1] as $key=>$val)
        {
            $match[1][$key] = str_replace('"',"",$val);
        }
        $data[$k]['cid'] = $item['cid'];
        $data[$k]['imgs'] = $match[1];
        $data[$k]['imgs_num'] = count($match[1]);
        $data[$k]['title'] = $item['title'];
        $data[$k]['click'] = $item['click'];
        $data[$k]['id'] = $item['id'];
        $data[$k]['url'] = '/web/index/cartdetail/id/'.$item['id'];
    }
    return $data;
}

//生成唯一订单号
function makeorder()
{
    $order_id_main = date('YmdHis') . rand(10000000,99999999);
    $order_id_len = strlen($order_id_main);
    $order_id_sum = 0;
    for($i=0; $i<$order_id_len; $i++){
        $order_id_sum += (int)(substr($order_id_main,$i,1));
    }
    $osn = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);
    return $osn;
}

/**
 * 过滤html标签
 * @param string $string
 * @param int $length
 * @return string
 */

function htmltotext($string, $length) {
    $string=strip_tags($string);
    $string = preg_replace("/\t/","",$string); //使用正则表达式替换内容，如：空格，换行，并将替换为空。
    $string = preg_replace("/\r\n/","",$string);
    $string = preg_replace("/\r/","",$string);
    $string = preg_replace("/\n/","",$string);
    $string = preg_replace("/ /","",$string);
    $string = preg_replace("/  /","",$string);  //匹配html中的空格
    $string = preg_replace("/&nbsp;/","",$string);

    preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $string, $info);
    $j = 0;
    $wordscut = '';
    for($i=0; $i<count($info[0]); $i++) {
        $wordscut .= $info[0][$i];
        $j = ord($info[0][$i]) > 127 ? $j + 2 : $j + 1;
        if ($j > $length - 3) {
            return $wordscut;
        }
    }
    return join('', $info[0]);
}


//获取远程图片大小
function getsize($url,$user='',$pw='')
{
    // start output buffering
    ob_start();
    // initialize curl with given uri
    $ch = curl_init($url); // make sure we get the header
    curl_setopt($ch, CURLOPT_HEADER, 1); // make it a http HEAD request
    curl_setopt($ch, CURLOPT_NOBODY, 1); // if auth is needed, do it here
    if (!empty($user) && !empty($pw))
    {
        $headers = array('Authorization: Basic ' . base64_encode($user.':'.$pw));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    $okay = curl_exec($ch);
    curl_close($ch); // get the output buffer
    $head = ob_get_contents(); // clean the output buffer and return to previous // buffer settings
    ob_end_clean();  // gets you the numeric value from the Content-Length // field in the http header
    $regex = '/Content-Length:\s([0-9].+?)\s/';
    $count = preg_match($regex, $head, $matches);  // if there was a Content-Length field, its value // will now be in $matches[1]
    if (isset($matches[1]))
    {
        $size = $matches[1];
    }
    else
    {
        $size = '0';
    }

    return $size;
}


function randomkeys($length){
    $key = '';
    $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
    for($i=0;$i<$length;$i++)
    {
        $key .= $pattern{mt_rand(0,35)};    //生成php随机数
    }
    return $key;
}
