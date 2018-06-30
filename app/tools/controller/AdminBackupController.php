<?php
namespace app\tools\controller;

use cmf\controller\AdminBaseController;

class AdminBackupController extends AdminBaseController
{
    /**
     * 数据库操作
     * 移至 admin/SqlController
     * adminMenu(
     *     'name'   => '数据库管理',
     *     'parent' => 'tools/AdminIndex/default',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 210,
     *     'icon'   => '',
     *     'remark' => '数据库管理：备份还原、优化、查询、转换等操作',
     *     'param'  => ''
     * )
     */
    public function index()
    {

    }

}
