<?php
namespace app\user\validate;

use think\Validate;

class PaymentValidate extends Validate
{
    protected $rule = [
        // 'member_id' => 'require',
        'account' => 'require',
    ];
    protected $message = [
        'account.require' => '付款账号不能为空',
    ];

    protected $scene = [
        'add'  => [],
        'edit' => [],
    ];
}
