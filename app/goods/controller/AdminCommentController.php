<?php
namespace app\goods\controller;

use app\goods\model\CommentModel;
use cmf\controller\AdminBaseController;
use think\Db;

class AdminCommentController extends AdminBaseController
{
    protected $name = 'goods_comment';
    protected $flag = '评价';
    // private $db;
    public function _initialize()
    {
        parent::_initialize();

        $this->db = Db::name($this->name);
        // $this->db2 = Db::name('member');
        // $this->db3 = Db::name('order');
        // $this->db4 = Db::name('goods');
        $this->md = new CommentModel;
        $this->assign('flag', $this->flag);
    }

    /**
     * 产品评价
     * @adminMenu(
     *     'name'   => '产品评价',
     *     'parent' => 'goods/AdminGoods/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 11,
     *     'icon'   => '',
     *     'remark' => '产品评价',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $param   = $this->request->param();

        $where = [];
        $status  = isset($param['status']) ? intval($param['status']) : '';
        if (is_numeric($status)) {
            $where['status'] = $status;
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
        if (!empty($param['uname'])) {
            $userId = c2c_get_uid($param['uname']);
            // if (empty($userId)) {
            //     $this->error('用户不合法');
            // }
            $where['from_id'] = $userId;
        }

        $list    = $this->db->where($where)->order('create_time DESC')->paginate();
        $list->appends($param);

        $comment_status = config('goods_comment_status');

        $this->assign('statusV', c2c_get_status_set($status, $comment_status));
        $this->assign('comment_status', $comment_status);
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
        $statusV = c2c_get_status_set(0, 'goods_comment_status');
        $levelV  = c2c_get_status_set(1, 'goods_comment_level');

        $this->assign('statusV', $statusV);
        $this->assign('levelV', $levelV);
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
        $post = $data['puts'];
        // $result = $this->validate($post, 'Comment.add');
        // if ($result !== true) {
        //     $this->error($result);
        // }
        $post['create_time'] = time();
        $post['ip'] = get_client_ip(0,true);
dump($post);die;
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

        $statusV = c2c_get_status_set($puts['status'], 'goods_comment_status');
        $levelV  = c2c_get_status_set($puts['level'], 'goods_comment_level');

        $this->assign('statusV', $statusV);
        $this->assign('levelV', $levelV);
        $this->assign('puts', $puts);
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
        $post = $data['puts'];
        // $result = $this->validate($post, 'Comment.edit');
        // if ($result !== true) {
        //     $this->error($result);
        // }
        $post['deal_id']   = cmf_get_current_admin_id();
dump($post);die;
        $insid = $this->db->where('id', $id)->update($post);
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
}
