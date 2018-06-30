<?php
namespace app\portal\controller;

use app\portal\model\HelpCenterModel;
// use app\admin\model\RouteModel;
use cmf\controller\AdminBaseController;
use think\Db;

/**
 * 帮助中心分类表
 */
class AdminHelpCenterController extends AdminBaseController
{
    public function _initialize()
    {
        parent::_initialize();

        $this->db = Db::name('help_center');
        $this->md = new HelpCenterModel;
        $this->assign('flag', '帮助分类');
    }

    /**
     * 帮助分类列表
     * @adminMenu(
     *     'name'   => '帮助分类',
     *     'parent' => 'portal/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 2,
     *     'icon'   => '',
     *     'remark' => '帮助分类列表',
     *     'param'  => ''
     * )
     */
    public function index()
    {

        $param             = $this->request->param();
        $param['parentId'] = (isset($param['parentId']) && is_numeric($param['parentId'])) ? intval($param['parentId']) : null;

        $categoryTree = $this->md->categoryTableTree($param);
        $helps        = $this->md->getFirstCate($param['parentId']);

        $this->assign('category_tree', $categoryTree);
        $this->assign('helps', $helps);

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
        $parentId       = $this->request->param('parent', 0, 'intval');
        $categoriesTree = $this->md->categoryTree($parentId);

        // $themeModel        = new ThemeModel();
        // $listThemeFiles    = $themeModel->getActionThemeFiles('portal/List/index');
        // $articleThemeFiles = $themeModel->getActionThemeFiles('portal/Article/index');

        $this->assign('parentId', $parentId);
        $this->assign('categories_tree', $categoriesTree);
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
        // $data = $this->request->param();
        $data = $this->opp();

        $insid = $this->md->addCategory($data);

        if ($insid === false) {
            $this->error('添加失败!');
        }
        $this->success('添加成功!', url('edit', ['id' => $insid]));
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
        if ($id <= 0) {
            $this->error('操作错误');
        }

        $data           = $this->md->get($id);
        $data           = empty($data) ? $this->error('数据不存在') : $data->toArray();
        $categoriesTree = $this->md->categoryTree($data['parent_id'], $id);

        // 路由

        $this->assign('categories_tree', $categoriesTree);
        $this->assign('parentId', $data['parent_id']);
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
        $data   = $this->opp('edit');
        $result = $this->md->editCategory($data);
        if ($result === false) {
            $this->error('编辑失败 或 数据无变化!');
        }
        $this->success('编辑成功!', url('index'));
    }

    /**
     * c2c_admin_log($this->name .':'. $id,'删除'. $this->flag .'：'. $find['name']);
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
        $id = $this->request->param('id');
        //获取删除的内容
        $find = $this->md->where('id', $id)->value('name');
        if (empty($find)) {
            $this->error('分类不存在!');
        }
        //判断此分类有无子分类（不算被删除的子分类）
        $childCount = $this->md->where(['parent_id' => $id, 'delete_time' => 0])->count();
        if ($childCount > 0) {
            $this->error('此分类有子类无法删除!');
        }
        $catetypeCount = Db::name('help')->where('cate_id', $id)->count();
        if ($catetypeCount > 0) {
            $this->error('此分类有文章无法删除!');
        }

        $result = $this->md->where('id', $id)->update(['delete_time' => time()]);
        if ($result) {
            c2c_recycle_bin($this->name,$id,$find);
            $this->success('删除成功!');
        } else {
            $this->error('删除失败');
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
     * 添加&编辑共用
     * @param  string $valid [description]
     * @return [type]        [description]
     */
    public function opp($valid = 'add')
    {
        $data = array_map('trim', $this->request->post());
        $result = $this->validate($data, 'HelpCenter.' . $valid);
        if ($result !== true) {
            $this->error($result);
        }
        if (!empty($data['icon'])) {
            $data['icon'] = cmf_asset_relative_url($data['icon']);
        }
        // $data['is_hot'] = isset($data['is_hot']) ? $data['is_hot'] : 0;

        return $data;
    }
}
