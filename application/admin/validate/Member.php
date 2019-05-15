<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/4/29
 * Time: 8:36
 */
namespace app\admin\validate;

use think\Validate;

class Member extends Validate
{
    protected $rule = [
        'user'  =>  'require',
        'password' =>  'require|alphaNum|confirm',
        'phone' =>  'require',
        'email' =>  'email',
        'name' => 'chs'
    ];

    protected $message = [
        'user.require'  =>  '用户名必须输入',
        'password'  =>  '密码不能为空',
        'password.alphaNum'  =>  '密码只能为字母和数字',
        'password.confirm'  =>  '两次密码值不一致',
        'phone' =>  '电话只能是数字',
        'email' =>  '邮箱格式不对',
        'name.chs'  =>  '姓名只能是汉字',
    ];

    protected $scene = [
        'check'   =>  ['name','phone','email','user']
    ];
}