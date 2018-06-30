<?php
namespace app\tools\controller;

use cmf\controller\AdminBaseController;

/**
 * Class AdminIndexController
 * @package app\tools\controller
 * @adminMenuRoot(
 *     'name'   =>'工具箱',
 *     'action' =>'default',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 70,
 *     'icon'   =>'gear',
 *     'remark' =>'工具箱'
 * )
 */
class AdminIndexController extends AdminBaseController
{
    /**
     * 工具
     * @adminMenu(
     *     'name'   => '工具',
     *     'parent' => 'tools/AdminIndex/default',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 1,
     *     'icon'   => '',
     *     'remark' => '工具',
     *     'param'  => ''
     * )
     */
    public function index()
    {

    }
}
