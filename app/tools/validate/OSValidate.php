<?php
namespace app\tools\validate;

use think\DB;
use think\Validate;

/**
 * summary
 */
class OSValidate extends Validate
{
    protected $rule = [
        'status' => 'require|unique:order_status',
        'name' => 'require|unique:order_status',
    ];

    protected $message = [
        'status.require' => '对应订单状态不能为空！',
        'status.unique'  => '对应订单状态已存在！',
        'name.require'   => '名称不能为空',
        'name.unique'    => '名称已存在',
    ];

    protected $scene = [
        'add'  => [],
        'edit' => [],
    ];

    // protected function cs1($value, $rule, $data)
    // {
    //     $find = Db::name('order_status')->where('status', $value)->count();
    //     if ($find > 0) {return false;}return true;
    // }
    // protected function cs2($value, $rule, $data)
    // {
    //     $find = Db::name('order_status')->where(['id' => ['neq', $data['id']], 'status' => $value])->count();
    //     if ($find > 0) {return false;}return true;
    // }
}
