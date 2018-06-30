<?php
namespace app\user\validate;

use think\Validate;

class MessageValidate extends Validate
{
    protected $rule = [
        'subject' => 'require',
    ];
    protected $message = [
        'subject.require' => '主题不能为空',
    ];

    protected $scene = [
        'add'  => [],
        'edit' => [],
    ];
}