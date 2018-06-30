<?php
namespace app\tools\validate;

use think\Db;
use think\Validate;

/**
 * summary
 */
class PaymentValidate extends Validate
{
    protected $rule = [
        // 'id|模型ID'    => 'require',
        'id'      => 'require',
        'name'    => 'require|chsDash|unique:payment',
        'code'    => 'require|alphaDash|unique:payment',
        'account' => 'require',
        'pkey'    => 'require',
    ];
    protected $message = [
        'id.require'      => '模型ID不能为空!',
        'name.require'    => '名称不能为空!',
        'name.chsDash'    => '名称格式不对!',
        'name.unique'     => '名称已存在!',
        'code.require'    => '唯一标识符不能为空!',
        'code.alphaDash'  => '唯一标识符格式不对!',
        'code.unique'     => '唯一标识符已存在!',
        'account'         => '账户不能为空！',
        'pkey'            => '密钥不能为空！',
    ];

    protected $scene = [
        'add'   => [],
        'edit'  => [],
        // 'add'  => ['name' => ['require|chsDash|checkName'], 'code' => ['require|alphaDash|checkCode'], 'account', 'pkey'],
        // 'edit' => ['name' => ['require|chsDash|checkName2'], 'code' => ['require|alphaDash|checkCode2'], 'account', 'pkey'],
    ];

    // public function __construct()
    // {
    //     parent::__construct();
    //     $this->db = Db::name('payment');
    // }

    // // 自定义 name验证
    // protected function checkName($value, $rule, $data)
    // {
    //     $find = $this->db->where('name', $value)->count();
    //     if ($find > 0) {
    //         return '名称已存在！';
    //     }
    //     return true;
    // }
    // protected function checkName2($value, $rule, $data)
    // {
    //     $find = $this->db->where(['id' => ['neq', $data['id']], 'name' => $value])->count();
    //     if ($find > 0) {
    //         return false;
    //     }
    //     return true;
    // }

    // // 自定义 CODE验证
    // protected function checkCode($value, $rule, $data)
    // {
    //     $find = $this->db->where('code', $value)->count();
    //     if ($find > 0) {
    //         return false;
    //     }
    //     return true;
    // }
    // protected function checkCode2($value, $rule, $data)
    // {
    //     $find = $this->db->where(['id' => ['neq', $data['id']], 'code' => $value])->count();
    //     if ($find > 0) {
    //         return false;
    //     }
    //     return true;
    // }
}
