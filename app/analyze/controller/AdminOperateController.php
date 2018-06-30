<?php
namespace app\analyze\controller;

use cmf\controller\AdminBaseController;
use think\Db;

/**
 * 后台操作日志
 */
class AdminOperateController extends AdminBaseController
{
    protected $name = 'admin_log';
    protected $flag = '操作日志';

    public function _initialize()
    {
        parent::_initialize();

        $this->db = Db::name($this->name);
        $this->assign('flag',$this->flag); 
    }

    /**
     * 操作日志
     * @adminMenu(
     *     'name'   => '操作日志',
     *     'parent' => 'analyze/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 7,
     *     'icon'   => '',
     *     'remark' => '后台操作日志',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $keyword = $this->request->param('keyword','');
        $where = [];
        if (!empty($keyword)) {
            $where['action|object'] = ['like',"%$keyword%"];
        }
        // if (!empty($create_time)) {
            
        // }
        $list = $this->db->where($where)->order('create_time DESC')->select();

        $this->assign('list',$list);
        return $this->fetch();
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

        $result = $this->db->where('id',$id)->delete();
        if ($result) {
            $this->success(lang('DELETE_SUCCESS'));
        } else {
            $this->error(lang('DELETE_FAILED'));
        }
    }
}
