<?php
namespace app\goods\validate;

// use app\admin\model\RouteModel;
use think\Db;
use think\Validate;

/**
 * 产品分类验证
 * name不在这里验证
 */
class CategoryValidate extends Validate
{
    protected $rule = [
        'name|名称' => 'require',
        'code'    => 'require|alphaDash|codes||unique:goods_category_fmw',
        // 'fmw_path' => 'require|checkFpa|checkFpa2',
        // 'path' => 'require|checkPath|checkPath2',
    ];
    protected $message = [
        'code.require'   => 'CODE码不能为空',
        'code.alphaDash' => 'CODE码只能是字母和数字，下划线_及破折号-',
        'code.codes'     => 'CODE不能为纯数字',
        'code.unique'    => 'CODE已存在!',
    ];

    protected $scene = [
        // 'add'  => ['code' => 'require|alphaDash|codes|checkCode','fmw_path'=>'require|checkFpa','path'=>'require|checkPath'],
        // 'add'  => ['name','code' => 'require|alphaDash|codes|checkCode'],
        // 'edit' => ['name','code' => ['require', 'alphaDash', 'codes', 'checkCode2']],
    ];

    // private $db;
    public function __construct()
    {
        parent::__construct();
        $this->db = Db::name('goods_category_fmw');
    }

    // 自定义验证规则

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
    // 检测 fmw_path
    protected function checkFpa($value)
    {
        $find = $this->db->where('fmw_path', $value)->count();
        if ($find > 0) {
            return '已存在！';
        } else {
            return true;
        }
    }
    // 检测 path
    protected function checkPath($value)
    {
        $find = $this->db->where('path', $value)->count();
        if ($find > 0) {
            return '已存在！';
        } else {
            return true;
        }
    }
}
