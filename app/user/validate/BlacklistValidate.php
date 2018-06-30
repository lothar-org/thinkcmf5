<?php
namespace app\user\validate;

use think\Validate;

class BlacklistValidate extends Validate
{
    protected $rule = [
        'deal_id' => 'require',
    ];
    protected $message = [
        'deal_id.require' => '操作人不能为空',
    ];

    protected $scene = [
        'add'  => [],
        'edit' => [],
    ];
}
