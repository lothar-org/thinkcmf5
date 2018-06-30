<?php
namespace app\analyze\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class AdminVisitController extends AdminBaseController
{
    public function _initialize()
    {
        parent::_initialize();

        $this->db = Db::name('visit_log');
        $this->assign('flag','访问记录'); 
    }

    /**
     * 访问记录
     * @adminMenu(
     *     'name'   => '访问记录',
     *     'parent' => 'analyze/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 5,
     *     'icon'   => '',
     *     'remark' => '访问记录',
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
        // 访问时间
        $start = empty($filter['start']) ? 0 : strtotime($filter['start']);
        $end   = empty($filter['end']) ? 0 : strtotime($filter['end']);
        if (!empty($start) && !empty($end)) {
            $where['last_time'] = [['>= time', $start], ['<= time', $end]];
        } else {
            if (!empty($start)) {
                $where['last_time'] = ['>= time', $start];
            }
            if (!empty($end)) {
                $where['last_time'] = ['<= time', $end];
            }
        }
        $list = $this->db->where($where)->order('last_time DESC')->select();

        $this->assign('list',$list);
        return $this->fetch();
    }
}
