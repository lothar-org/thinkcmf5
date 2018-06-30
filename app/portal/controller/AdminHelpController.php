<?php
namespace app\portal\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use app\portal\model\HelpModel;
use app\portal\model\HelpCenterModel;

/**
 * 帮助中心表
 */
class AdminHelpController extends AdminBaseController
{
    public function _initialize()
    {
        parent::_initialize();

        $this->db = Db::name('help');
        $this->md = new HelpModel;
        $this->assign('flag','帮助管理'); 
    }

    /**
     * 帮助列表
     * @adminMenu(
     *     'name'   => '帮助管理',
     *     'parent' => 'portal/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 1,
     *     'icon'   => '',
     *     'remark' => '帮助列表',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $param = $this->request->param();
        $categoryId = $this->request->param('category', 0, 'intval');

        $data        = $this->md->getlist($param);
        $data->appends($param);

        $cateModel = new HelpCenterModel();
        $categoryTree        = $cateModel->getFirstCate($categoryId);

        $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('list', $data->items());
        $this->assign('category_tree', $categoryTree);
        $this->assign('category', $categoryId);
        $this->assign('pager', $data->render());


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
        $cateModel = new HelpCenterModel();
        $categoryTree        = $cateModel->categoryTree();
        $this->assign('categories_tree',$categoryTree);
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
        $data['user_id'] = cmf_get_current_admin_id();

        $result = $this->md->handleGo($data);
        if (empty($result)) {
            $this->error('添加失败');
        }
        $this->success('添加成功',url('edit',['id'=>$this->md->id]));
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
        $id = $this->request->param('id',0,'intval');
        if (empty($id)) {
            $this->error('请求非法');
        }
        $data = $this->db->where('id',$id)->find();
        if (empty($data)) {
            $this->error('数据非法');
        }
        $cateModel = new HelpCenterModel();
        $categoryTree        = $cateModel->categoryTree();

        $this->assign('categories_tree',$categoryTree);
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
        $id = $this->request->param('id',0,'intval');
        if (empty($id)) {
            $this->error('数据非法!');
        }
        $data = $this->opp('edit');

        $result = $this->md->handleGo($data,$id);
        if (empty($result)) {
            $this->error('编辑失败 或 数据无变化');
        }
        $this->success('编辑成功',url('index'));
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
            $id = $this->request->param('id',0,'intval');
            $find = $this->db->field('title')->where('id',$id)->find();
            $result = $this->db->where('id',$id)->delete();
            if ($result) {
                c2c_admin_log($this->name .':'. $id,'删除'. $this->flag .'：'. $find['title']);
                $this->success(lang('DELETE_SUCCESS'));
            } else {
                $this->error(lang('DELETE_FAILED'));
            }
        }

        if (isset($param['ids'])) {
            $ids    = $this->request->param('ids/a');
            $result = $this->db->where(['id' => ['in', $ids]])->delete();
            if ($result) {
                c2c_admin_log($this->name, '批量删除'. $this->flag .'：'. implode(',', $ids));
                $this->success('删除成功');
            }
        }

        $this->error('删除失败');
    }

    /**
     * add&edit 
     * @param  string $valid [description]
     * @param  array  $data  [description]
     * @return [type]        [description]
     */
    public function opp($valid='add', $data=[])
    {
        $data = $this->request->post();
        $data['title'] = trim($data['title']);
        $result = $this->validate($data,'Help.'.$valid);
        if(true !== $result){
            $this->error($result);
        }
        $data['content'] = isset($_POST['content'])?$_POST['content']:'';// 原样获取，避免框架过滤
        $data['is_rec'] = isset($data['is_rec'])?$data['is_rec']:0;

        return $data;
    }
}