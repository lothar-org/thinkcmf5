<?php
namespace app\user\controller;

use app\user\model\CreditsModel;
use cmf\controller\AdminBaseController;
use think\Db;

class AdminCreditsController extends AdminBaseController
{
    protected $name = 'credits_range';
    protected $flag = '信用积分区间';

    public function _initialize()
    {
        parent::_initialize();

        $this->db = Db::name($this->name);
        $this->md = new CreditsModel;
        $this->assign('flag', $this->flag);
    }

    /**
     * 信用积分区间设定
     * @adminMenu(
     *     'name'   => '信用积分区间',
     *     'parent' => 'user/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 5,
     *     'icon'   => '',
     *     'remark' => '信用积分区间设定',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $param = $this->request->param();

        $list = $this->md->getlist($param);

        $list->appends($param);
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
        $data = $this->opp();

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
            $find   = $this->db->field('name')->where('id', $id)->find();
            $result = $this->db->where('id', $id)->delete();
            if ($result) {
                c2c_admin_log($this->name . ':' . $id, '删除' . $this->flag . '：' . $find['name']);
                $this->success(lang('DELETE_SUCCESS'));
            } else {
                $this->error(lang('DELETE_FAILED'));
            }
        }

        if (isset($param['ids'])) {
            $ids    = $this->request->param('ids/a');
            $result = $this->db->where(['id' => ['in', $ids]])->delete();
            if ($result) {
                c2c_admin_log($this->name, '批量删除' . $this->flag . '：' . implode(',', $ids));
                $this->success('删除成功');
            }
        }

        $this->error('删除失败');
    }

    /**
     * 共用
     * @param  [type] $data  [description]
     * @param  string $valid [description]
     * @return [type]        [description]
     */
    public function opp($valid = 'add', $data = [])
    {
        // $data = $this->request->param();
        $data = $this->request->post();

        /*数据验证*/
        $rule = [
            'name|等级名称'         => 'require|chsDash|max:15|unique:credits_range,name',
            'start_range|区间开始值' => 'require|number|max:11|unique:credits_range',
            'end_range|区间结束值'   => 'require|number|max:11|unique:credits_range',
            // 'icon|小图标'=>'require',
        ];
        $message = [
            'name.require'        => '名称不能为空',
            'name.chsDash'        => '名称只能是汉字、字母、数字和下划线_及破折号-',
            'name.max'            => '名称最大长度为15个字符',
            'name.unique'         => '名称已有不能乱改！',
            'start_range.require' => '区间开始值不能为空',
            'start_range.number'  => '区间开始值只能为数字',
            'start_range.max'     => '区间开始值最大长度为11个字符',
            'start_range.unique'  => '区间开始值已有不能乱改！',
            'end_range.require'   => '区间结束值不能为空',
            'end_range.number'    => '区间结束值只能为数字',
            'end_range.max'       => '区间结束值最大长度为11个字符',
            'end_range.unique'    => '区间结束值已有不能乱改！',
        ];
        // $batch = false;
        // $callback = function(){};
        $result = $this->validate($data, $rule, $message);
        if (true !== $result) {
            $this->error($result);
        }
        if ($data['start_range'] >= $data['end_range']) {
            $this->error('区间结束值不能小于区间开始值');
        }

        if ($valid == 'add') {
            $maxEnd = $this->db->max('end_range');
            if ($data['start_range'] < $maxEnd) {
                $this->error('开始值不能比已有最大结束值小');
            }
            if ($data['start_range'] != ($maxEnd + 1)) {
                $this->error('开始值应该比已有的最大结束值大1');
            }
            $find = $this->db->where('name', $data['name'])->count();
            if ($find > 0) {

                $this->error('等级名称已存在');
            }
        } else {
            // $start = $this->db->where([
            //     'start_range'=>['gt',$data['start_range']],
            // ])->value('start_range');
            // $end = $this->db->where([
            //     'end_range'=>['>',$data['end_range']],
            // ])->value('end_range');

            $find = $this->db->where(['id' => ['neq', $data['id']], 'name' => $data['name']])->count();
            if ($find > 0) {
                $this->error('名称已有不能乱改');
            }
        }

        return $data;
    }
}
