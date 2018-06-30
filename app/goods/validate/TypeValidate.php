<?php
namespace app\goods\validate;

// use app\admin\model\RouteModel;
use think\Db;
use think\Validate;

class TypeValidate extends Validate
{
    protected $rule = [
        'name' => 'require|checkName|checkName2',
        'code' => 'require|alphaDash|codes|unique:goods_type',
    ];
    protected $message = [
        'name.require'    => '类型名称不能为空',
        'name.checkName'  => '名称已存在!',
        'name.checkName2' => '名称重复!',
        'code.require'    => 'CODE不能为空',
        'code.alphaDash'  => 'CODE只能是字母和数字，下划线_及破折号-',
        'code.codes'      => 'CODE格式不对',
        'code.unique'     => 'CODE已存在!',
    ];

    protected $scene = [
        // 'add'  => ['name' => 'require|checkName', 'code' => 'require|alphaDash|codes|checkCode'],
        // 'edit' => ['name' => ['require', 'checkName2'], 'code' => ['require', 'alphaDash', 'codes', 'checkCode2']],
    ];

    // private $db;
    public function __construct()
    {
        parent::__construct();
        $this->db = Db::name('goods_type');
    }

    // 自定义验证规则
    // 自定义 name验证
    // protected function names($value)
    // {
    //     if (preg_match('/[\x80-\xff]/', $value)) {
    //         return '名称不能是汉字';
    //     }
    //     return true;
    // }
    protected function checkName($value, $rule, $data)
    {
        $find = $this->db->where('name', $value)->count();
        if ($find > 0) {
            return false;
        }
        return true;
    }
    protected function checkName2($value, $rule, $data)
    {
        $find = $this->db->where(['id' => ['neq', $data['id']], 'name' => $value])->count();
        if ($find > 0) {
            return false;
        }
        return true;
    }

    // 自定义 CODE验证
    protected function codes($value)
    {
        if (empty($value)) {
            return true; //结束验证
        }
        if (preg_match("/^\d+$/", $value)) {
            return "CODE不能为纯数字!";
        }
        return true;
    }
    protected function checkCode($value, $rule, $data)
    {
        $find = $this->db->where('code', $value)->count();
        if ($find > 0) {
            return false;
        }
        return true;
    }
    protected function checkCode2($value, $rule, $data)
    {
        $find = $this->db->where(['id' => ['neq', $data['id']], 'code' => $value])->count();
        if ($find > 0) {
            return false;
        }
        return true;
    }
}
