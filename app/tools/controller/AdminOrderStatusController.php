<?php
namespace app\tools\controller;

use app\tools\model\OrderStatusModel;
use cmf\controller\AdminBaseController;
use think\Db;

/**
 * 订单状态模型管理
 * 模型例子,这里使用模型处理了
 */
class AdminOrderStatusController extends AdminBaseController
{
    protected $name = 'order_status';
    protected $flag = '订单状态模型';

    // 前置操作里，不支持驼峰命名
    protected $beforeActionList = [
        'first'  => ['except' => 'addpost,editpost'],
        'second' => ['only' => 'index,add,edit'],
        'three'  => ['only' => 'index,addpost,editpost'],
    ];

    // public function _initialize()
    // {
    //     parent::_initialize();
    //     $this->db = Db::name('order_status');
    //     $this->md = new OrderStatusModel;
    //     $this->assign('flag','订单状态模型');
    // }
    public function first()
    {
        $this->db = Db::name($this->name);
    }
    public function second()
    {
        $this->assign('flag', $this->flag);
    }
    public function three()
    {
        $this->md = new OrderStatusModel;
        // $this->md = model('OrderStatus');
    }

    /**
     * 订单状态模型管理
     * @adminMenu(
     *     'name'   => '订单状态模型',
     *     'parent' => 'tools/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 9,
     *     'icon'   => '',
     *     'remark' => '订单状态模型',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        // $param = $this->request->param();
        // $list = $this->md->getlist($param);
        // $list->appends($param);
        $list = $this->md->getlist();
// dump($list->toArray());die;

        $this->assign('list', $list->items());
        // $this->assign('pager', $list->render());
        return $this->fetch();
    }

    /**
     * 添加
     * @adminMenu(
     *     'name'   => '添加',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        $this->assign('orderStatus', c2c_get_status_set());
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
        $result = $this->validate($data,'OS.add');
        if ($result !== true) {
            $this->error($result);
        }

        $result = $this->md->handleGo($data);//1成功 0失败
        if (empty($result)) {
            $this->error('添加失败');
        }
        $this->success('添加成功', url('edit', ['id' => $this->md->id]));
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
        $id = $this->request->param('id', 0, 'intval');

        $data = $this->db->where('id', $id)->find();

        $this->assign($data);
        $this->assign('orderStatus', c2c_get_status_set($data['status']));
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
        $id = $this->request->param('id', 0, 'intval');
        if (empty($id)) {
            $this->error('数据非法!');
        }
        $data = $this->request->param();
        $result = $this->validate($data,'OS.edit');
        if ($result !== true) {
            $this->error($result);
        }

        $insid = $this->md->handleGo($data, $id);//1成功 0失败
        if (empty($insid)) {
            $this->error('编辑失败 或 数据无变化');
        }
        $this->success('编辑成功', url('index'));
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
        $id   = $this->request->param('id', 0, 'intval');
        $name = $this->db->where('id', $id)->value('name');

        $result = $this->db->where('id', $id)->delete();
        if ($result) {
            c2c_admin_log($this->name .':'. $id, '删除'. $this->flag .'：'. $name);
            $this->success(lang('DELETE_SUCCESS'));
        } else {
            $this->error(lang('DELETE_FAILED'));
        }
    }
}
