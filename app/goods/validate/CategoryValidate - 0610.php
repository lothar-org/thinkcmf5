<?php
// namespace app\goods\validate;

// use app\admin\model\RouteModel;
use think\Db;
use think\Validate;

class CategoryValidate extends Validate
{
    protected $rule = [
        'name|分类名称'  => 'require|names|checkName|checkName2',
        'code|CODE码' => 'require|alphaDash|codes|checkCode|checkCode2',
    ];
    protected $message = [
        // 'name.require' => '分类名称不能为空',
        // 'code.alphaDash' => 'CODE只能是字母和数字，下划线_及破折号-',
    ];

    protected $scene = [
        'add'   => ['name' => ['require', 'names', 'checkName'], 'code' => ['require', 'alphaDash', 'codes', 'checkCode']],
        'edit'  => ['name' => ['require', 'names', 'checkName2'], 'code' => ['require', 'alphaDash', 'codes', 'checkCode2']],
        'add2'  => ['name' => ['require', 'names', 'checkName']],
        'edit2' => ['name' => ['require', 'names', 'checkName2']],
    ];

    // private $db;
    public function __construct()
    {
        parent::__construct();
        $this->db = Db::name('goods_category');
    }

    // 自定义验证规则
    // 自定义 name验证。
    // fmw里同级的不允许重复。但category里是不存在重复的。这个
    protected function names($value)
    {
        if (preg_match('/[\x80-\xff]/', $value)) {
            return '名称不能是汉字';
        }
        return true;
    }
    protected function checkName($value, $rule, $data)
    {
        if ($data['next_id']) return true;
        $find = $this->db->where('name', $value)->count();
        if ($find > 0) {
            return false;
        }
        return true;
    }
    protected function checkName2($value, $rule, $data)
    {
        if ($data['next_id']) return true;
        $find = $this->db->where(['id' => ['neq', $data['id']], 'name' => $value])->count();
        if ($find > 0) {
            return false;
        }
        return true;
    }

    // 自定义 CODE验证
    // 不存在重复，这个同id,path一样具有唯一性
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