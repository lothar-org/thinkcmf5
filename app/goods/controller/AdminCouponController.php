<?php
namespace app\goods\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class AdminCouponController extends AdminBaseController
{
    protected $name = 'coupon';
    protected $flag = '折扣码';
    // private $db;
    public function _initialize()
    {
        parent::_initialize();

        $this->db  = Db::name($this->name);
        $this->db2 = Db::name('coupon_relation');
        $this->db3 = Db::name('member_coupon');
        $this->assign('flag', $this->flag);
    }

    /**
     * 产品折扣码
     * @adminMenu(
     *     'name'   => '产品折扣码',
     *     'parent' => 'goods/AdminGoods/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 9,
     *     'icon'   => '',
     *     'remark' => '产品折扣码,优惠券',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $list = $this->db->select();

        $this->assign('list', $list);
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
        $data = [
            'start_time' => time(),
            'end_time'   => time(),
            'is_repeat'  => 1,
            'status'     => 1,
        ];
        $this->assign($data);
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
        $data   = $this->request->param();
        $result = $this->validate($data, 'Coupon.add');
        if ($result !== true) {
            $this->error($result);
        }
        $data['start_time']  = strtotime($data['start_time']);
        $data['end_time']    = strtotime($data['end_time']);
        $data['create_time'] = time();

        $insid = $this->db->insertGetId($data);
        if (empty($insid)) {
            $this->error('添加失败');
        }
        $this->success('添加成功', url('index'));
        // $this->success('添加成功', url('edit', ['id' => $insid]));
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

        $data   = $this->request->param();
        $result = $this->validate($data, 'Coupon.edit');
        if ($result !== true) {
            $this->error($result);
        }
        $data['start_time']  = strtotime($data['start_time']);
        $data['end_time']    = strtotime($data['end_time']);
        $data['update_time'] = time();
        $data['is_repeat']   = isset($data['is_repeat']) ? $data['is_repeat'] : 0;

        $insid = $this->db->where('id', $id)->update($data);
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
            c2c_admin_log($this->name .':'. $id, '删除' . $this->flag . '：' . $name);
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
