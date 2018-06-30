<?php
namespace app\user\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class AdminPaymentController extends AdminBaseController
{
    protected $name = 'member_payment';
    protected $flag = '收款账户';
    private $db;
    
    public function _initialize()
    {
        parent::_initialize();

        $this->db = Db::name($this->name);
        $this->assign('flag', $this->flag);
    }

    /**
     * 收款账户设置
     * @adminMenu(
     *     'name'   => '收款账户',
     *     'parent' => 'user/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 7,
     *     'icon'   => '',
     *     'remark' => '收款账户',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        echo '建设中……';die;
        $keyword = $this->request->param('keyword', '');
        $where   = [];
        if (!empty($keyword)) {
            $where['b.username|b.email|b.mobile'] = ['like', "%$keyword%"];
        }
        $list = $this->db->alias('a')
            ->field('a.*,b.username')
            ->join('member b', 'a.member_id=b.id')
            ->where($where)->paginate();

        $this->assign('list', $list->items());
        $list->appends('keyword', $keyword);
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
        $data = $this->opp();

        $insid = $this->db->insertGetId($data);
        if (empty($insid)) {
            $this->error('添加失败');
        }
        $this->success('添加成功', url('index'));
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
        $data = $this->opp('edit');

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
        $param = $this->request->param();

        if (isset($param['id'])) {
            $id     = $this->request->param('id', 0, 'intval');
            $find   = $this->db->field('account')->where('id', $id)->find();
            $result = $this->db->where('id', $id)->delete();
            if ($result) {
                c2c_admin_log($this->name .':'. $id, '删除'. $this->flag .'：' . $find['account']);
                $this->success(lang('DELETE_SUCCESS'));
            } else {
                $this->error(lang('DELETE_FAILED'));
            }
        }

        if (isset($param['ids'])) {
            $ids    = $this->request->param('ids/a');
            $result = $this->db->where(['id' => ['in', $ids]])->delete();
            if ($result) {
                c2c_admin_log($this->name .':'. $id, '批量删除'. $this->flag .'：' . implode(',', $ids));
                $this->success('删除成功');
            }
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

    /**
     * 共用
     * @param  [type] $data  [description]
     * @param  string $valid [description]
     * @return [type]        [description]
     */
    public function opp($valid = 'add', $data = [])
    {
        $uname  = $this->request->param('uname');
        $userId = c2c_get_uid($uname);
        if (empty($userId)) {
            $this->error('用户不存在！');
        }
        $data              = $this->request->param();
        $data['member_id'] = $userId;
        $result            = $this->validate($data, 'Payment.'.$valid);
        if ($result !== true) {
            $this->error($result);
        }
        $data['create_time'] = time();
        // $data['ip']          = get_client_ip(0, true);
        unset($data['uname']);

        return $data;
    }

    public function uname()
    {
        $uname  = $this->request->param('uname');
        $userId = c2c_get_uid($uname);
        if (empty($userId)) {
            $this->error('用户不合法');
        }
        $this->success('用户合法。 uid=' . $userId);
    }
}
