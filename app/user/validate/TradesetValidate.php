<?php
namespace app\user\validate;

use think\Validate;

class TradesetValidate extends Validate
{
    protected $rule = [
        // 'member_id' => 'require',
        'url' => 'require',
    ];
    protected $message = [
        'url.require' => '交易URL不能为空',
    ];

    protected $scene = [
        'add'  => [],
        'edit' => [],
    ];
}
