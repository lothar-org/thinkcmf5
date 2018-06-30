<?php
namespace app\user\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class AdminGroupController extends AdminBaseController
{
    protected $name = 'member_group';
    protected $flag = '前台组';

    public function _initialize()
    {
        parent::_initialize();

        $this->db = Db::name($this->name);
        $this->assign('flag', $this->flag);
    }

    /**
     * 前台用户组管理
     * @adminMenu(
     *     'name'   => '前台用户组',
     *     'parent' => 'user/AdminIndex/default1',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 1,
     *     'icon'   => '',
     *     'remark' => '前台用户组管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $list = $this->db->order('list_order')->paginate();
// dump($list);
        // $list->appends($param);
        $this->assign('list', $list->items());
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
        $data = $this->request->param();
        // $data = $this->opp();
        $data['create_time'] = time();
        $insid               = $this->db->insertGetId($data);
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

        $data = $this->request->param();
        // $data = $this->opp('edit');
        $data['update_time'] = time();

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
        $mq    = Db::name('member_group_user');

        $ids = [];
        if (isset($param['id'])) {
            $ids[] = intval($param['id']);
        }
        if (isset($param['ids'])) {
            $ids = $param['ids'];
        }

        if (!empty($ids)) {
            foreach ($ids as $id) {
                $find     = $this->db->where('id', $id)->value('name');
                $findUser = $mq->where('group_id', $id)->count();
                $result   = true;
                if ($findUser > 0) {
                    Db::startTrans();
                    try {
                        $mq->where('group_id', $id)->delete(); //先删外键
                        $this->db->where('id', $id)->delete();
                        Db::commit();
                    } catch (\Exception $e) {
                        Db::rollback();
                        $result = false;
                    }
                } else {
                    $result = $this->db->where('id', $id)->delete();
                }
                if ($result) {
                    c2c_admin_log($this->name . ':' . $id, '删除' . $this->flag . '：' . $find);
                } else {
                    $this->error(lang('DELETE_FAILED'));
                }
            }
            // $result = $this->db->where(['id' => ['in', $ids]])->delete();
            $this->success(lang('DELETE_SUCCESS'));
        }
        $this->error('数据非法');
    }

    /**
     *
     * @adminMenu(
     *     'name'   => '组成员',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '',
     *     'param'  => ''
     * )
     */
    public function userList()
    {
        $groupId = $this->request->param("groupId", 0, 'intval');
        if (empty($groupId)) {
            $this->error("参数错误！");
        }
        // $join = [
        //     ['member_group b','a.group_id=b.id'],
        //     ['member c','a.member_id=c.id'],
        // ];
        // $list = Db::name('member_group_user')->alias('a')->field('a.id,b.name,c.username')->join($join)->where('a.group_id',$groupId)->paginate(20);

        $groupInfo = Db::name('member_group')->field('name')->where('id', $groupId)->find();
        $list      = Db::name('member_group_user')->alias('a')->field('a.id,b.username,b.email,b.mobile,b.balance,b.orders,b.reviews,b.negatives,b.rating,b.level,b.verifys,b.status')->join('member b', 'a.member_id=b.id')->where('a.group_id', $groupId)->paginate(20);

        $this->assign('groupId', $groupId);
        $this->assign('groupInfo', $groupInfo);
        $this->assign('member_status', config('member_status'));
        $this->assign('list', $list);
        $this->assign('pager', $list->render());

        return $this->fetch('userlist');
    }

    /**
     *
     * @adminMenu(
     *     'name'   => '添加组成员',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '',
     *     'param'  => ''
     * )
     */
    public function userAdd()
    {
        $groupId = $this->request->param("groupId", 0, 'intval');

        $this->assign('groupId', $groupId);
        return $this->fetch();
    }

    /**
     *
     * @adminMenu(
     *     'name'   => '组成员编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '',
     *     'param'  => ''
     * )
     */
    public function userAddPost()
    {
        if ($this->request->isPost()) {
            $groupId = $this->request->param("groupId", 0, 'intval');
            if (!$groupId) {
                $this->error("组不存在！");
            }
            $db_gu = Db::name('member_group_user');

            $ids = $this->request->param('memberId/a');
            if (!empty($ids)) {
                $del = $db_gu->where(['group_id' => $groupId])->delete();
                if ($del) {
                    // foreach ($ids as $id) {
                    //     $db_gu->insert(['group_id'=>$groupId,'member_id'=>$id]);
                    // }
                    foreach ($collection as $value) {
                        $temp[] = ['group_id' => $groupId, 'member_id' => $id];
                    }
                    $result = $db_gu->insertAll($temp);
                }

                $this->success("分配成功！");
            } else {
                //当没有数据时，清除当前组成员
                $db_gu->where(['groupId' => $groupId])->delete();
                $this->error("没有接收到数据，执行该组成员成功！");
            }
        }
    }

    /**
     * 组成员删除
     * @return [type] [description]
     */
    public function userDelete()
    {
        $mq = Db::name('member_group_user');

        $id = $this->request->param('id/d', 0);
        if (!empty($id)) {
            $result = $mq->where('id', $id)->delete();
            if ($result) {
                $this->success('删除成功');
            }
        }

        $ids = $this->request->param('ids/a');
        if (!empty($ids)) {
            $result = $mq->where('id', 'in', $ids)->delete();
            if ($result) {
                $this->success('删除成功');
            }
        }
        $this->error('删除失败');
    }

    /**
     * 添加&编辑共用
     * @param  string $valid [description]
     * @param  array  $data  [description]
     * @return [type]        [description]
     */
    public function opp($valid = 'add', $data = [])
    {
        // $data = $this->request->param();
        $data              = $this->request->post();
        
        // $result            = $this->validate($data, 'Group.' . $valid);
        // if ($result !== true) {
        //     $this->error($result);
        // }
        // $data['create_time'] = time();
        // $data['ip']          = get_client_ip(0, true);

        return $data;
    }
}
