<?php
namespace app\goods\validate;

use think\Validate;
use think\Db;


class CouponValidate extends Validate
{
    protected $rule = [
        'name'      => 'require|checkName',
        'code'      => 'require|alphaDash|checkCode',
        'attain'    => 'number|checkAttain',
        'reduce|优惠' => 'require|float',
    ];
    protected $message = [
        'name.require'       => '折扣标题必填',
        'code.require'       => '折扣码必填',
        'code.alphaDash'     => '折扣码必须是字母和数字，下划线_及破折号-',
        'code.checkCode'     => '折扣码已存在',
        'attain.number'      => '满值必须为数字型',
        'attain.checkAttain' => '优惠值不得超过满值',
    ];
    protected $scene = [
        'add'  => ['name','code'=>'require|alphaDash|checkCode','attain'=>'number|checkAttain','reduce'=>'require|float'],
        'edit' => ['name'=>'require','code'=>'require|alphaDash'],
    ];

    // 自定义验证
    protected function checkName($value)
    {
        $find = Db::name('coupon')->where('name',$value)->count();
        if ($find>0) {
            return false;
        }
        return true;
    }
    protected function checkCode($value)
    {
        $find = Db::name('coupon')->where('code',$value)->count();
        if ($find>0) {
            return false;
        }
        return true;
    }
    protected function checkAttain($value, $rule, $data)
    {
        if ($value > 0 && $value < $data['reduce']) {
            return false;
        }
        return true;
    }
}
