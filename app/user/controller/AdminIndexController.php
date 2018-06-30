<?php
namespace app\user\controller;

use cmf\controller\AdminBaseController;
use think\Db;

/**
 * Class AdminIndexController
 * @package app\user\controller
 *
 * @adminMenuRoot(
 *     'name'   =>'用户管理',
 *     'action' =>'default',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 10,
 *     'icon'   =>'group',
 *     'remark' =>'用户管理'
 * )
 *
 * @adminMenuRoot(
 *     'name'   =>'用户组',
 *     'action' =>'default1',
 *     'parent' =>'user/AdminIndex/default',
 *     'display'=> true,
 *     'order'  => 2,
 *     'icon'   =>'',
 *     'remark' =>'用户组'
 * )
 */
class AdminIndexController extends AdminBaseController
{
    protected $name  = 'member';
    protected $flag  = '本站用户';
    protected $types = [1 => '买家', 2 => '卖家'];

    public function _initialize()
    {
        parent::_initialize();

        $this->member_status = [
            0 => lang('USER_STATUS_BLOCKED'),
            1 => lang('USER_STATUS_ACTIVATED'),
            2 => lang('USER_STATUS_UNVERIFIED'),
        ];

        $this->db = Db::name($this->name);
        $this->assign('flag', $this->flag);
    }

    /**
     * 后台本站用户列表
     * \app\analyze\controller\AdminUserController.php
     * @adminMenu(
     *     'name'   => '本站用户',
     *     'parent' => 'default1',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 5,
     *     'icon'   => '',
     *     'remark' => '本站用户',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $filter = input('request.');

        /**
         * 条件筛选
         */
        $where          = [];
        $keywordComplex = [];
        // 成员ID 考虑这个是唯一性，另做处理
        if (!empty($filter['uid'])) {
            $where  = ['id' => intval($filter['uid'])];
            $status = $type = $level = $source = '';
        } else {
            // 单值查询: 状态,用户类型,级别,来源
            $check = ['status', 'type', 'level', 'source'];
            foreach ($check as $row) {
                if (!empty($filter[$row])) {
                    $where[$row] = $$row = trim($filter[$row]);
                } else {
                    $$row = '';
                }
            }
            // 时间查询: 注册时间,最后登录时间
            $check = ['reg_time', 'login_time'];
            foreach ($check as $row) {
                $start = empty($filter['start_' . $row]) ? 0 : strtotime($filter['start_' . $row]);
                $end   = empty($filter['end_' . $row]) ? 0 : strtotime($filter['end_' . $row]);
                if (!empty($start) && !empty($end)) {
                    $where[$row] = [['>= time', $start], ['<= time', $end]];
                } else {
                    if (!empty($start)) {
                        $where[$row] = ['>= time', $start];
                    }
                    if (!empty($end)) {
                        $where[$row] = ['<= time', $end];
                    }
                }
            }
            // 区间查询: 账户余额,成功订单数,总评数,好评数,差评数,信用积分
            $check = ['balance', 'orders', 'reviews', 'positives', 'negatives', 'rating'];
            foreach ($check as $row) {
                $start = empty($filter['start_' . $row]) ? 0 : intval($filter['start_' . $row]);
                $end   = empty($filter['end_' . $row]) ? 0 : intval($filter['end_' . $row]);
                if (!empty($start) && !empty($end)) {
                    $where[$row] = [['>=', $start], ['<=', $end]];
                } else {
                    if (!empty($start)) {
                        $where[$row] = ['egt', $start];
                    }

                    if (!empty($end)) {
                        $where[$row] = ['elt', $end];
                    }

                }
            }
            // 更多
            // 关键词 这里是 OR条件，另做处理
            if (!empty($filter['keyword'])) {
                $keyword = trim($filter['keyword']);

                $keywordComplex['username|email|mobile|member_sku'] = ['like', "%$keyword%"];
            }
        }
// dump($where);die;

        /**
         * 排序
         * 需要把条件查询的参数带上
         */
        $order = [];

        // whereOr放前面，精准查询。
        $list = $this->db->whereOr($keywordComplex)->where($where)->order("reg_time DESC")->paginate(10);

        $levelOrg  = [1 => 1, 2 => 2, 3 => 3];
        $sourceOrg = ['Windows', 'Linux', 'ubuntu', 'Mac', 'Android', 'iOS', 'symbian', 'BlackBerry', 'YunOS'];
// dump(c2c_get_status_set($source,$sourceOrg,'请选择'));die;
        $this->assign('statusV', c2c_get_status_set($status, $this->member_status, '请选择'));
        $this->assign('types', c2c_get_status_set($type, $this->types, '请选择'));
        $this->assign('levels', c2c_get_status_set($level, $levelOrg, '请选择'));
        $this->assign('sources', c2c_get_status_set($source, $sourceOrg, '请选择'));
        $this->assign('member_status', $this->member_status);
        $this->assign('keyword_show', '用户名/昵称/邮箱/手机');

        $this->assign('list', $list);
        $this->assign('pager', $list->render()); //获取分页显示
        // 渲染模板输出
        return $this->fetch();
    }

    /**
     *
     * @adminMenu(
     *     'name'   => '本站用户添加',
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
        // 预设数据
        // $post = [
        //     'birthday' => 0,
        // ];
        $verifys = Db::name('verify')->where('status', 1)->order('list_order')->column('id,name,code');

        $this->assign('verifys', c2c_get_options(0, '', $verifys));
        $this->assign('sexs', c2c_get_status_set(0, 'member_sex'));
        $this->assign('types', c2c_get_status_set(0, $this->types));
        $this->assign('statusV', c2c_get_status_set(1, $this->member_status));
        // $this->assign('post',$post);
        return $this->fetch();
    }

    /**
     *
     * @adminMenu(
     *     'name'   => '本站用户添加提交',
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
        if ($this->request->isPost()) {
            $data             = $this->opp();
            // 基本数据
            $post             = $data[0];
            $post['reg_time'] = time();
            $post['reg_ip']   = get_client_ip(0, true);
            // $bros = \Browser();
            // $post['source']   = $bros->getPlatform();
            $post['source']   = 'admin:' . cmf_get_current_admin_id();//表名是管理员添加的
            // 更多数据
            $info = $data[1];

            $result = true;
            Db::startTrans();
            try {
                $insid             = $this->db->insertGetId($post);
                $info['member_id'] = $insid;
                Db::name('member_info')->insert($info);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $result = false;
            }

            if ($result === false) {
                $this->error('添加失败！');
            } else {
                // c2c_admin_log($this->name .':'. $id,'删除'. $this->flag .'：'. $find['name']);
                $this->success('添加成功！', url('edit', ['id' => $insid]));
            }
        }
    }

    /**
     *
     * @adminMenu(
     *     'name'   => '本站用户编辑',
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
        $id = $this->request->param('id/d', 0, 'intval');
        if ($id == 0) {
            $this->error('非法操作！');
        }

        $post = $this->db->alias('a')->join('member_info b', 'a.id=b.member_id')->where('a.id', $id)->find();
        if (empty($post)) {
            $this->error('数据不存在！');
        }

        // select多选如何解决已选中？ 定制化
        $verifys = Db::name('verify')->where('status', 1)->order('list_order')->column('id,name,code');
        $options = '';
        foreach ($verifys as $v) {
            $options .= '<option ' . (strpos($post['verifys'] . ',', $v['id'] . ',') !== false ? 'selected' : '') . ' value="' . $v['id'] . '">' . $v['name'] . '</option>';
        }
        // dump($options);
        // dump($verifys);die;

        // $this->assign('verifys', c2c_get_options($post['verifys'], '', $verifys));
        $this->assign('verifys', $options);
        $this->assign('sexs', c2c_get_status_set($post['sex'], 'member_sex'));
        $this->assign('types', c2c_get_status_set($post['type'], $this->types));
        $this->assign('statusV', c2c_get_status_set($post['status'], $this->member_status));
        $this->assign('post', $post);
        return $this->fetch();
    }

    /**
     * 本站用户编辑提交
     * @adminMenu(
     *     'name'   => '本站用户编辑提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户编辑提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        if ($this->request->isPost()) {
            $id = $this->request->param('post.id/d', 0, 'intval');
            if (empty($id)) {
                $this->error('数据非法!');
            }
            $data = $this->opp('add');
            $post = $data[0];
            // unset($post['username']);
            $info = $data[1];
            // dump($post);
            // dump($info);die;

            $result = true;
            Db::startTrans();
            try {
                // $this->db->where('id',$id)->update($post);
                $this->db->update($post);
                $count = Db::name('member_info')->where('member_id', $id)->count();
                if ($count > 0) {
                    Db::name('member_info')->where('member_id', $id)->update($info);
                } else {
                    $info['member_id'] = $id;
                    Db::name('member_info')->insert($info);
                }
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $result = false;
            }

            if ($result === false) {
                $this->error('更新失败 或 数据无变化！');
            } else {
                // c2c_admin_log($this->name .':'. $id,'删除'. $this->flag .'：'. $find['name']);
                $this->success('更新成功！', url('index', array('uid' => $id)));
            }
        }
    }

    /**
     * 本站用户删除
     * 无批量操作
     * @adminMenu(
     *     'name'   => '本站用户删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户删除',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');

        $insid = $this->db->where('id', $id)->delete();
        if (empty($insid)) {
            $this->error('删除失败');
        }
        $this->success('删除成功');
    }

    /**
     * 本站用户拉黑
     * @adminMenu(
     *     'name'   => '本站用户拉黑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户拉黑',
     *     'param'  => ''
     * )
     */
    public function ban()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            $result = $this->db->where(["id" => $id])->setField('status', 0);
            if ($result) {
                $this->success("会员拉黑成功！", "adminIndex/index");
            } else {
                $this->error('会员拉黑失败,会员不存在!');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    /**
     * 本站用户启用
     * @adminMenu(
     *     'name'   => '本站用户启用',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户启用',
     *     'param'  => ''
     * )
     */
    public function cancelBan()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            $this->db->where(["id" => $id])->setField('status', 1);
            $this->success("会员启用成功！", '');
        } else {
            $this->error('数据传入失败！');
        }
    }

    public function move()
    {

    }

    /**
     * 批量复制
     * 入组操作：批量、全部
     * @return [type] [description]
     */
    public function copy()
    {
        if ($this->request->isPost()) {
            $groupId   = $this->request->post('term_id', 0, 'intval');
            $idsString = $this->request->post('ids', '');
            $mq        = Db::name('member_group_user');

            $ids  = explode(',', $idsString);
            $olds = $mq->where('group_id', $groupId)->column('member_id');

            $sameIds = array_intersect($olds, $ids);
            $delIds  = array_diff($olds, $sameIds);
            $addIds  = array_diff($ids, $sameIds);

            $adds = [];
            foreach ($addIds as $cow) {
                $adds[] = [
                    'group_id'  => $groupId,
                    'member_id' => $cow,
                ];
            }

            $result = true;
            Db::startTrans();
            try {
                $mq->where(['group_id' => $groupId, 'member_id' => ['in', $delIds]])->delete();
                $mq->insertAll($adds);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $result = false;
            }
            if ($result === false) {
                $this->error('操作失败');
            }
            $this->success('操作成功');
        } else {
            $ids = $this->request->param();
            if (empty($ids)) {
                $this->error('请选择数据');
            }
            $terms_tree = Db::name('member_group')->field('id,name')->where('status', 1)->select()->toArray();
            $this->assign('terms_tree', c2c_get_options(0, '', $terms_tree));
            $this->assign('ids', implode(',', $ids));
            return $this->fetch();
        }
    }

    /**
     * 共用
     * @param  [type] $data  [description]
     * @param  string $valid [description]
     * @return [type]        [description]
     */
    public function opp($valid = 'add', $data = [])
    {
        // 获取
        $data = $this->request->param();
        // dump($data);die;

        // 预处理
        // post
        $post = $data['post'];
        $post            = array_map('trim', $post);
        $result = $this->validate($post, 'Member.post' . $valid);
        if ($result !== true) {
            $this->error($result);
        }
        $post['verifys'] = empty($post['verifys']) ? [] : $post['verifys'];
        // $post['verifys'] = json_encode($post['verifys']);
        // $post['verifys'] = join(',',$post['verifys']);
        $post['verifys'] = implode(',', $post['verifys']);

        $post['nickname'] = empty($post['nickname']) ? $post['username'] : $post['nickname'];
        if (!empty($post['password'])) {
            $post['password'] = cmf_password($post['password']);
        }
        if (!empty($post['avatar'])) {
            $post['avatar'] = cmf_asset_relative_url($post['avatar']);
        }
        // info
        $info = $data['info'];
        $result = $this->validate($info, 'Member.info' . $valid);
        if ($result !== true) {
            $this->error($result);
        }
        if (!empty($info['birthday'])) {
            $info['birthday'] = strtotime($info['birthday']);
        }
        if (!empty($info['banner'])) {
            $info['banner'] = cmf_asset_relative_url($info['banner']);
        }
        if (!empty($info['promote_banner'])) {
            $info['promote_banner'] = cmf_asset_relative_url($info['promote_banner']);
        }

        // dump($post);
        // dump($info);die;
        return [$post, $info];
    }
}
