<?php
namespace app\portal\validate;

use think\Validate;

class HelpValidate extends Validate
{
    protected $rule = [
        'title' => 'require|unique:help,title',
    ];
    protected $message = [
        'title.require' => '请填写标题',
        'title.unique' => '标题已存在',
    ];

    protected $scene = [
       // 'add'  => ['user_login,user_pass,user_email'],
       // 'edit' => ['user_login,user_email'],
    ];
}
