<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/5/6
 * Time: 15:58
 */
namespace app\admin\validate;

use think\Validate;

class Card extends Validate
{
    protected $rule = [
        'imgurl'  =>  'require',
        'name' =>  'require',
        'job' =>  'require',
        'phone' =>  'require|number',
        'address' =>  'require',
    ];

    protected $message = [
        'imgurl'  =>  '图片必须上传',
        'name' =>  '名字必须输入',
        'job' =>  '职务必须输入',
        'address' =>  '职务必须输入',
        'phone.require'  =>  '手机必须输入',
        'phone.number'  =>  '手机号只能是数字',
    ];

    protected $scene = [
        'add'   =>  ['name','email'],
        'edit'  =>  ['email'],
    ];
}