<?php
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class LangController extends AdminBaseController
{
    // private $db;
    public function _initialize()
    {
        parent::_initialize();

        $this->db = Db::name('language_set');
        $this->assign('flag','语言设置'); 
    }

    /**
     * 语言设置
     * @adminMenu(
     *     'name'   => '语言设置',
     *     'parent' => 'admin/Setting/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 3,
     *     'icon'   => '',
     *     'remark' => '语言设置',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $list = $this->db->select();

        $this->assign('list',$list);
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
      
        if (!empty($data['icon'])) {
            $data['icon'] = cmf_asset_relative_url($data['icon']);
        }
        $data['status'] = isset($data['status'])?$data['status']:0;

        $insid = $this->db->insertGetId($data);
        if (empty($insid)) {
            $this->error('添加失败');
        }
        $this->success('添加成功', url('index'));
        // $this->success('添加成功',url('edit',['id'=>$insid]));
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

        $data = $this->db->where('id',$id)->find();

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

        $data = $this->request->param();

        if (!empty($data['icon'])) {
            $data['icon'] = cmf_asset_relative_url($data['icon']);
        }
        $data['status'] = isset($data['status'])?$data['status']:0;

        $insid = $this->db->where('id',$id)->update($data);
        if (empty($insid)) {
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
        $id = $this->request->param('id',0,'intval');
        $name = $this->db->where('id',$id)->value('name');

        $result = $this->db->where('id',$id)->delete();
        if ($result) {
            c2c_admin_log('lang' .':'. $id,'删除语言模型：'.$name);
            $this->success(lang('DELETE_SUCCESS'));
        } else {
            $this->error(lang('DELETE_FAILED'));
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

}
