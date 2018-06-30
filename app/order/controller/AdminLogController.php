<?php
namespace app\order\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class AdminLogController extends AdminBaseController
{
    protected $name = 'order_log';
    protected $flag = '订单日志';
    // private $db;
    public function _initialize()
    {
        parent::_initialize();

        $this->db = Db::name($this->name);
        // $this->md = new OrderLogModel;
        $this->assign('flag', $this->flag);
    }

    /**
     * 订单日志列表
     * @adminMenu(
     *     'name'   => '订单日志',
     *     'parent' => 'order/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 11,
     *     'icon'   => '',
     *     'remark' => '订单日志列表',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $param  = $this->request->param();
        $where  = [];
        $status = isset($param['status']) ? intval($param['status']) : '';
        if (is_numeric($status)) {
            $where['order_status'] = $status;
        }
        if (!empty($param['type'])) {
            $where['user_type'] = intval($param['type']);
        }
        $startTime = empty($param['start_time']) ? 0 : strtotime($param['start_time']);
        $endTime   = empty($param['end_time']) ? 0 : strtotime($param['end_time']);
        if (!empty($startTime) && !empty($endTime)) {
            $where['create_time'] = [['>= time', $startTime], ['<= time', $endTime]];
        } else {
            if (!empty($startTime)) {
                $where['create_time'] = ['>= time', $startTime];
            }
            if (!empty($endTime)) {
                $where['create_time'] = ['<= time', $endTime];
            }
        }
        if (!empty($param['oid'])) {
            $where['order_id'] = intval($param['oid']);
        }

        $list = $this->db->field('id,order_id,order_status,deal_id,user_type,create_time,ip')->where($where)->order('create_time DESC')->paginate(25);
        // $list->appends($param);

        $order_status = config('order_status');
        $user_type    = config('user_type');

        $this->assign('order_status', $order_status);
        $this->assign('statusV', c2c_get_status_set(0, $order_status));
        $this->assign('user_type', $user_type);
        $this->assign('types', c2c_get_status_set(0, $user_type));
        $this->assign('list', $list);
        $this->assign('pager', $list->render());
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
        $this->assign('statusV', c2c_get_status_set(0, 'order_status'));
        $this->assign('user_type', c2c_get_status_set(1, 'user_type'));
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
        $post = $this->request->param();
        // $result = $this->validate($post, 'Dispute.'.$valid);
        // if ($result !== true) {
        //     $this->error($result);
        // }
        $post['create_time'] = time();
        $post['ip']          = get_client_ip();
        // $post['deal_id']     = cmf_get_current_admin_id();



        // c2c_order_log($post);
        $insid = $this->db->insertGetId($post);
        if (empty($insid)) {
            $this->error('添加失败');
        }
        $this->success('添加成功', url('index'));
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
        $param = $this->request->param();

        if (isset($param['id'])) {
            $id     = $this->request->param('id', 0, 'intval');
            $result = $this->db->where('id', $id)->delete();
            if ($result) {
                $this->success('删除成功');
            }
        }

        if (isset($param['ids'])) {
            $ids    = $this->request->param('ids/a');
            $result = $this->db->where(['id' => ['in', $ids]])->delete();
            if ($result) {
                $this->success('删除成功');
            }
        }
        $this->error('删除失败');
    }
}
