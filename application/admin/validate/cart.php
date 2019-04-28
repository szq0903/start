<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/4/27
 * Time: 15:56
 */

namespace app\admin\validate;

use think\Validate;

class Cart extends Validate
{
    protected $rule = [
        'num'  =>  'require|number',
        'unit' =>  'require',
        'specs' =>  'require',
        'pid' =>  'require|number',
    ];

    protected $message = [
        'num.require'  =>  '数量必须输入',
        'num.number'  =>  '数量只能是数字',
        'unit' =>  '单位必须输入',
        'specs' =>  '规格必须输入',
        'pid.require'  =>  '产品id请选择',
        'pid.number'  =>  '数量只能是数字',
    ];

    protected $scene = [
        'add'   =>  ['name','email'],
        'edit'  =>  ['email'],
    ];
}