<?php
namespace app\user\controller;

use cmf\controller\AdminBaseController;
use think\Db;

/**
 * 站内信由 member_message 和 msg_tpl 拼成
 */
class AdminMessageController extends AdminBaseController
{
    protected $name = 'member_message';
    protected $flag = '站内信';
    private $db;

    public function _initialize()
    {
        parent::_initialize();

        $this->db  = Db::name($this->name);
        $this->db2 = Db::name('msg_tpl');
        $this->assign('flag', $this->flag);
    }

    // 前置操作里，不支持驼峰命名
    protected $beforeActionList = [
        'first' => ['only' => 'index,add'],
        // 'second' => ['only' => 'add'],
    ];
    public function first($groupId = 0)
    {
        $groups = Db::name('member_group')->field('id,name')->where('status', 1)->order('list_order')->select()->toArray();
        $this->assign('groups', c2c_get_options($groupId, ['请选择', 0], $groups));
    }

    /**
     * 站内信
     * @adminMenu(
     *     'name'   => '站内信',
     *     'parent' => 'user/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 3,
     *     'icon'   => 'commenting',
     *     'remark' => '站内信',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $params  = $this->request->param();
        $type    = $this->request->param('type', 0, 'intval');
        $keyword = $this->request->param('keyword', '');
        // $field1 = 'id,type,msg_tpl_id,status,is_saved,offer_id,order_id,from_id,to_id';//全部为数值型
        // $field2 = 'id,create_time,from_name,to_name,ip,subject,content';//字符

        $where = [];
        if (!empty($type)) {
            $where['a.type'] = intval($type);
        }
        if (!empty($keyword)) {
            $where['b.subject'] = ['like', "%$keyword%"];
        }
        $list = $this->db->alias('a')
            ->field('a.id,a.type,a.status,a.from_id,a.to_id,b.create_time,b.from_name,b.to_name,b.ip,b.subject')
            ->where($where)
            ->join([
                ['__MSG_TPL__ b', 'a.msg_tpl_id=b.id'],
            ])->order('create_time DESC')
            ->paginate(25);
        // ->fetchSql(true)->select();
        // dump($list);die;

        $statusV = config('message_status');
        $typeV   = config('message_type');

        $this->assign('types', c2c_get_status_set($type, 'message_type'));
        $this->assign('typeV', config('message_type'));
        $this->assign('statusV', config('message_status'));
        $this->assign('list', $list->items());
        $list->appends($params);
        // $list->fragment('AAA');
        // $list->appends($params)->fragment('AAA');
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

        $this->assign('typeV', c2c_get_status_set(3, 'message_type'));
        $this->assign('statusV', c2c_get_status_set(0, 'message_status'));
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
        // $post  = $this->opp();

        // 前置操作
        $post = $this->request->param();
        if (!isset($_POST['content'])) {
            $this->error('请输入内容！');
        }

        $groupUser = [];
        if (empty($post['groupId'])) {
            $uname  = $this->request->param('uname');
            $userId = c2c_get_uid($uname);
            if (empty($userId)) {
                $this->error('接收者不存在！');
            }
        } else {
            // 1、一次性批量消息
            // 2、当用户登录时生成消息
            $groupId = $post['groupId'];
            // $groupUser = Db::name('member_group_user')->where('group_id', $groupId)->column('member_id');
            $groupUser = model('Group')->getUsers($groupId, 'uname');
            // dump($groupUser);die;
        }

        $post['subject'] = trim($post['subject']);
        // 验证
        $result = $this->validate($post, 'Message.add');
        if ($result !== true) {
            $this->error($result);
        }

        // 后置操作
        // unset($post['uname']);
        $msg = [
            'type'     => $post['type'],
            'group_id' => $post['groupId'],
            'status'   => $post['status'],
            'from_id'  => cmf_get_current_admin_id(),//session('ADMIN_ID');
        ];
        // tpl
        $content = $_POST['content']; // 原样获取，避免框架过滤
        $tpl     = [
            'create_time' => time(),
            'from_name'   => session('name'),
            'ip'          => get_client_ip(0, true),
            'subject'     => $post['subject'],
        ];

        // 组优先原则
        if (empty($groupUser)) {
            $msg['to_id']   = $userId;
            $tpl['to_name'] = $uname;
            $tpl['content'] = $content;
        } else {
            // 批量消息
            // 如果模板采用了替换原则{name}
            $replace = '';
            // if (empty($replace)) {
            // } else {
            //     foreach ($groupUser as $key => $row) {
            //         $msg[] = array_push($msg_base,['to_id'=>$key]);
            //         $tpl[] = array_push($tpl_base,['content'=>str_replace('{name}',$row,$_POST['content'])]);
            //     }
            // }
        }

        $result = true;
        Db::startTrans();
        try {
            if (empty($groupUser)) {
                $tplid             = $this->db2->insertGetId($tpl);
                $msg['msg_tpl_id'] = $tplid;
                $this->db->insert($msg);
            } else {
                if (empty($replace)) {
                    $tpl['content']    = $content;
                    $msg['msg_tpl_id'] = $this->db2->insertGetId($tpl);
                    foreach ($groupUser as $key => $row) {
                        $msg['to_id'] = $key;
                        // dump($msg);die;
                        $this->db->insert($msg);
                    }
                    // $this->db2->insertAll($tpl);
                } else {
                    foreach ($groupUser as $key => $row) {
                        $msg['to_id']      = $key;
                        $tpl['content']    = str_replace('{name}', $row, $_POST['content']);
                        $msg['msg_tpl_id'] = $this->db2->insertGetId($tpl);
                        $this->db->insert($msg);
                    }
                }
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $result = false;
        }

        if ($result === false) {
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

        $data = $this->db->alias('a')->field('a.*,b.*')->where('a.id', $id)->join('__MSG_TPL__ b', 'a.msg_tpl_id=b.id')->find();
        // dump($data);die;

        $this->first($data['group_id']);
        $this->assign('typeV', c2c_get_status_set($data['type'], 'message_type'));
        $this->assign('statusV', c2c_get_status_set($data['status'], 'message_status'));
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

        // $post = $this->opp('edit');
        // $insid = $this->db->where('id', $id)->update($post);

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
            $id         = intval($param['id']);
            $msg_tpl_id = $this->db->where('id', $id)->value('msg_tpl_id');
            $name       = $this->db2->where('id', $msg_tpl_id)->value('subject');

            $result = $this->db->where('id', $id)->delete();
            if ($result) {
                c2c_admin_log($this->name . ':' . $id, '删除' . $this->flag . '：' . $name);
                $this->success(lang('DELETE_SUCCESS'));
            } else {
                $this->error(lang('DELETE_FAILED'));
            }
        }

        if (isset($param['ids'])) {
            $ids    = $this->request->param('ids/a');
            $result = $this->db->where(['id' => ['in', $ids]])->delete();
            if ($result) {
                c2c_admin_log($this->name . ':' . $id, '批量删除' . $this->flag . '：' . implode(',', $ids));
                $this->success('删除成功');
            }
        }
    }

    /**
     * 批量操作。状态：未读、已读、已处理
     * @adminMenu(
     *     'name'   => '批量操作',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '',
     *     'param'  => ''
     * )
     */
    public function opChange($type = '', $value = '')
    {
        $param = $this->request->param();
        if (isset($param['ids']) && isset($param['op'])) {
            $ids = $this->request->param('ids/a');
            $this->db->where(['id' => ['in', $ids]])->update(['status' => $param['op']]);
            $this->success("操作成功！", '');
        }
    }

    // 共用操作
    public function opp($valid = 'add')
    {
        // // 前置操作
        // $post            = $this->request->param();
        // if (empty($post['groupId'])) {

        // }
        // $uname  = $this->request->param('uname');
        // $userId = c2c_get_uid($uname);
        // if (empty($userId)) {
        //     $this->error('接收者不存在！');
        // }
        // // 验证
        // $result          = $this->validate($post, 'Message.' . $valid);
        // if ($result !== true) {
        //     $this->error($result);
        // }
        // // 后置操作
        // unset($post['uname']);

        // $msg = [
        //     'from_id'   => cmf_get_current_admin_id(),
        //     'to_id' => $userId,
        // ];
        // $tpl = [
        //     'ip' => get_client_ip(0,true),
        //     'create_time' => time(),
        //     'content' => isset($_POST['content'])?$_POST['content']:'',// 原样获取，避免框架过滤
        // ];

        // return [$msg,$tpl];
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

    public function ajaxName()
    {

    }

}
