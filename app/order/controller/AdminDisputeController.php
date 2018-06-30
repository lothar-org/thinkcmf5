<?php
namespace app\order\controller;

use cmf\controller\AdminBaseController;
use think\Db;

/**
 * 订单投诉
 */
class AdminDisputeController extends AdminBaseController
{
    protected $name = 'order_dispute';
    protected $flag = '订单投诉';
    // private $db;
    public function _initialize()
    {
        parent::_initialize();

        $this->db = Db::name($this->name);
        // $this->md = new DisputeModel;
        $this->assign('flag', $this->flag);
    }

    /**
     * 订单投诉列表
     * @adminMenu(
     *     'name'   => '订单投诉',
     *     'parent' => 'order/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 5,
     *     'icon'   => '',
     *     'remark' => '订单投诉列表',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $param = $this->request->param();
        $where = [];
        $list  = $this->db->field('id,order_id,order_title,buyer_id,seller_id,ip,create_time,update_time,deal_id,status')->where($where)->order('create_time DESC')->paginate(25);
        // $list->appends($param);

        $message_status = config('message_status');
        $this->assign('statusV', c2c_get_status_set(0,$message_status));
        $this->assign('message_status', $message_status);
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
        $statusV = c2c_get_status_set(0, 'message_status');

        $this->assign('statusV', $statusV);
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
        $post = $this->opp();
        $post['create_time'] = time();
        $post['ip']          = get_client_ip();
        // $post['buyer_id']    = cmf_get_current_admin_id();

        $insid = $this->db->insertGetId($post);
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

        $puts = $this->db->where('id', $id)->find();

        $statusV = c2c_get_status_set($puts['status'], 'message_status');

        $this->assign('statusV', $statusV);
        $this->assign($puts);
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

        $post = $this->opp('edit');
        $post['update_time'] = time();

        $result = $this->db->where('id', $id)->update($post);
        if (empty($result)) {
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
        $param = $this->request->param();

        if (isset($param['id'])) {
            $id = $this->request->param('id', 0, 'intval');
            // $find = $this->db->field('name')->where('id',$id)->find();
            $result = $this->db->where('id', $id)->delete();
            if ($result) {
                // c2c_admin_log($this->name .':'. $id,'删除'. $this->flag .'：'. $find['name']);
                $this->success(lang('DELETE_SUCCESS'));
            }
        }

        if (isset($param['ids'])) {
            $ids    = $this->request->param('ids/a');
            $result = $this->db->where(['id' => ['in', $ids]])->delete();
            if ($result) {
                // c2c_admin_log($this->name, '批量删除'. $this->flag .'：'. implode(',', $ids));
                $this->success('删除成功');
            }
        }

        // $this->error(lang('DELETE_FAILED'));
        $this->error('删除失败');
    }

    /**
     * addPost、editPost共用
     * @param  string $valid [验证场景]
     * @return [type]        [description]
     */
    public function opp($valid='add')
    {
        $data   = $this->request->param();
        $post   = $data['puts'];
        // $result = $this->validate($post, 'Dispute.'.$valid);
        // if ($result !== true) {
        //     $this->error($result);
        // }
        $post['description'] = $_POST['description'];//获取原始数据

        return $post;
    }
}
