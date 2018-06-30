<?php
namespace app\user\validate;

use think\Validate;

class MemberValidate extends Validate
{
    protected $rule = [
        // 'member_sku'    => '',
        // 'type' => 'require',
        'username'   => 'require|chsDash|unique:member,username',
        'nickname'   => 'unique:member',
        'email'      => 'require|email|unique:member',
        'mobile'     => 'unique:member',
        // 'verifys' => '',
        'first_name' => 'checkName',
        'last_name'  => 'checkName',
        'birthday'   => 'number',
        // 'country' => '',
        // 'city' => '',
        // 'address' => '',
        // 'postcode' => '',
        // 'job' => '',
        // 'twich' => '',
        // 'forum' => '',
        // 'hobby' => '',
        // 'education' => '',
        // 'seller_described' => '',
        // 'seller_guarantee' => '',
        // 'seller_communication' => '',
    ];
    protected $message = [
        'username.require'     => '用户名不能为空!',
        'username.chsDash'     => '用户名只能是汉字、字母、数字和下划线_及破折号-!',
        'username.unique'      => '用户名已存在!',
        'nickname.unique'      => '昵称已存在!',
        'email.require'        => '邮箱不能为空!',
        'email.unique'         => '邮箱已存在!',
        'email.email'          => '邮箱格式不对!',
        'mobile.unique'        => '手机号已存在!',
        'first_name.checkName' => '姓格式不对!',
        'last_name.checkName'  => '名字格式不对!',
        'birthday.number'      => '生日格式不对!',
    ];

    protected $scene = [
        'postadd' => ['username','nickname','email','mobile'],
        'infoadd' => ['first_name','last_name','birthday'],
    ];

    // 验证name 格式
    protected function checkName($value, $rule, $data)
    {
        $pattern = "/^[\x{4e00}-\x{9fa5}A-Za-z0-9_.]+$/u";//UTF-8汉字/字母/数字/下划线/点正则表达式
        if (preg_match($pattern, $value)) {
            return true;
        } else {
            return false;
        }
    }
}
