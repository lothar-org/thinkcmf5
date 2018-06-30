<?php
namespace app\user\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class AdminGroupUserController extends AdminBaseController
{
    protected $name = 'member_group_user';
    protected $flag = '成员分配';

    public function _initialize()
    {
        parent::_initialize();

        $this->db = Db::name($this->name);
        $this->assign('flag', $this->flag);
    }

    /**
     * 组成员分配
     * @adminMenu_null(
     *     'name'   => '组成员分配',
     *     'parent' => 'user/AdminIndex/default1',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 20,
     *     'icon'   => '',
     *     'remark' => '组成员分配管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {

    }
}
