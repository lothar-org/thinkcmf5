<?php
namespace app\tools\validate;

use think\Db;
use think\Validate;

class VerifyValidate extends Validate
{
    protected $rule = [
        // 'id|模型ID'    => 'require',
        'id'   => 'require',
        'name' => 'require|chsDash|unique:verify',
        'code' => 'require|alphaDash|unique:verify',
        // 'icon' => 'require',
    ];
    protected $message = [
        'id.require'     => '模型ID不能为空!',
        'name.require'   => '名称不能为空!',
        'name.chsDash'   => '名称格式不对!',
        'name.unique'    => '名称已存在!',
        'code.require'   => '唯一识别码不能为空!',
        'code.alphaDash' => '唯一识别码只能是字母和数字，下划线_及破折号-',
        'code.unique'    => '唯一识别码已存在!',
    ];

    protected $scene = [
        'add'  => [],
        'edit' => [],
    ];

    // private $db;
    // public function __construct()
    // {
    //     parent::__construct();
    //     $this->db = Db::name('verify');
    // }

    // // 自定义 name验证
    // protected function checkName($value, $rule, $data)
    // {
    //     $find = $this->db->where('name', $value)->count();
    //     if ($find > 0) {
    //         return false;
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
