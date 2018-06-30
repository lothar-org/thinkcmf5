<?php
namespace app\analyze\controller;

use cmf\controller\AdminBaseController;
use think\Db;

/*
 * 订单模块
 */
class AdminOrderController extends AdminBaseController
{
    public function _initialize()
    {
        parent::_initialize();

        $this->db = Db::name('order');
        $this->assign('flag','订单统计'); 
    }

    /**
     * 订单统计
     * 订单分析图表
     * @adminMenu(
     *     'name'   => '订单统计',
     *     'parent' => 'analyze/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 1,
     *     'icon'   => '',
     *     'remark' => '订单统计/订单分析图表',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        
    }
}
