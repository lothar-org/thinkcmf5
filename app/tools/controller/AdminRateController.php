<?php
namespace app\tools\controller;

use cmf\controller\AdminBaseController;
use think\Db;

/**
 * 认证模型管理
 */
class AdminRateController extends AdminBaseController
{
    protected $name = 'rate';
    protected $flag = '货币模型';
    // private $db;
    public function _initialize()
    {
        parent::_initialize();

        $this->db = Db::name($this->name);
        $this->assign('flag',$this->flag); 
    }

    /**
     * 货币模型管理
     * @adminMenu(
     *     'name'   => '货币模型',
     *     'parent' => 'tools/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 5,
     *     'icon'   => '',
     *     'remark' => '货币模型',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $keyword = $this->request->param('keyword','');
        $where = [];
        if (!empty($keyword)) {
            $where['name|code|symbol'] = ['like',"%$keyword%"];
        }
        $list = $this->db->where($where)->order('list_order')->select();

        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * 
     * @adminMenu(
     *     'name'   => '添加',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        
        return $this->fetch();
    }

    /**
     * 
     * @adminMenu(
     *     'name'   => '添加提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        $data = $this->request->param();
        $result = $this->validate($data,'Rate.add');
        if ($result !== true) {
            $this->error($result);
        }
        $data['status'] = isset($data['status'])?$data['status']:0;

        $insid = $this->db->insertGetId($data);
        if (empty($insid)) {
            $this->error('添加失败');
        }
        $this->success('添加成功',url('edit',['id'=>$insid]));
    }

    /**
     * 
     * @adminMenu(
     *     'name'   => '编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id = $this->request->param('id',0,'intval');

        $data = $this->db->where('id',$id)->find();

        $this->assign($data);
        return $this->fetch();
    }

    /**
     * 
     * @adminMenu(
     *     'name'   => '编辑提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        $id = $this->request->param('id',0,'intval');
        if (empty($id)) {
            $this->error('数据非法!');
        }

        $data = $this->request->param();
        $result = $this->validate($data,'Rate.edit');
        if ($result !== true) {
            $this->error($result);
        }
        $data['status'] = isset($data['status'])?$data['status']:0;

        $insid = $this->db->where('id',$id)->update($data);
        if (empty($insid)) {
            $this->error('编辑失败 或 数据无变化');
        }
        $this->success('编辑成功',url('index'));
    }

    /**
     * 
     * @adminMenu(
     *     'name'   => '删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $id = $this->request->param('id',0,'intval');
        $name = $this->db->where('id',$id)->value('name');

        $result = $this->db->where('id',$id)->delete();
        if ($result) {
            c2c_admin_log($this->name .':'. $id, '删除'. $this->flag .'：'.$name);
            $this->success(lang('DELETE_SUCCESS'));
        } else {
            $this->error(lang('DELETE_FAILED'));
        }
    }

    /**
     * 
     * @adminMenu(
     *     'name'   => '排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '',
     *     'param'  => ''
     * )
     */
    public function listOrder()
    {
        parent::listOrders($this->db);
        $this->success("排序更新成功！", '');
    }
}
