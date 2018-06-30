<?php
namespace app\portal\validate;

use think\Validate;

class HelpCenterValidate extends Validate
{
    protected $rule = [
        'name' => 'require|unique:help_center,name',
    ];
    protected $message = [
        'name.require' => '请填写帮助分类名称',
        'name.unique' => '帮助分类名称已存在',
    ];

    protected $scene = [
       // 'add'  => ['user_login,user_pass,user_email'],
       // 'edit' => ['user_login,user_email'],
    ];
}
