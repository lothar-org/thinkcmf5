<?php
namespace app\goods\validate;

// use app\admin\model\RouteModel;
use think\Db;
use think\Validate;

class CatetypeValidate extends Validate
{
    protected $rule = [
        'game_id' => 'require',
        'type_id' => 'require',
        'title'   => 'require',
        // 'code'    => 'require|alphaDash|checkAlias|checkCode|checkCode2',
        'code'    => 'require|alphaDash|checkAlias|unique:goods_catetype',
    ];
    protected $message = [
        'game_id.require' => '游戏不能为空',
        'type_id.require' => '类型不能为空',
        'title.require'   => '标题不能为空',
        'code.require'    => 'CODE不能为空',
        'code.alphaDash'  => 'CODE只能是字母和数字，下划线_及破折号-',
        'code.unique'     => 'CODE已存在',
    ];

    protected $scene = [
        // 'add'  => ['game_id','type_id','title','code'=>'require|alphaDash|checkAlias|unique'],
        // 'edit' => [],
    ];

    // private $db;
    // public function __construct()
    // {
    //     parent::__construct();
    //     $this->db = Db::name('goods_catetype');
    // }

    // // 自定义验证规则

    // // 自定义 CODE验证
    // // 不存在重复，这个同id,path一样具有唯一性
    // protected function checkAlias($value, $rule, $data)
    // {
    //     if (empty($value)) {
    //         return true; //结束验证
    //     }

    //     if (preg_match("/^\d+$/", $value)) {
    //         return "CODE不能为纯数字!";
    //     }

    //     // $routeModel = new RouteModel();
    //     // if (isset($data['id']) && $data['id'] > 0) {
    //     //     $fullUrl = $routeModel->buildFullUrl('portal/List/index', ['id' => $data['id']]);
    //     // } else {
    //     //     $fullUrl = $routeModel->getFullUrlByUrl($data['code']);
    //     // }
    //     // if ($routeModel->exists($value, $fullUrl)) {
    //     //     return "CODE已经存在!";
    //     // }
    //     return true;
    // }
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
