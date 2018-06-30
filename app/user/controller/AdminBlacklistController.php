<?php
namespace app\user\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class AdminBlacklistController extends AdminBaseController
{
    protected $name = 'member_blacklist';
    protected $flag = '用户拉黑';

    public function _initialize()
    {
        parent::_initialize();

        $this->db = Db::name($this->name);
        $this->assign('flag', $this->flag);
    }

    /**
     * 用户黑名单
     * @adminMenu(
     *     'name'   => '用户黑名单',
     *     'parent' => 'user/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 9,
     *     'icon'   => '',
     *     'remark' => '用户黑名单',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        echo '建设中……';die;
        $keyword = $this->request->param('keyword','');
        $where = [];
        if (!empty($keyword)) {
            $where['reason'] = ['like',"%$keyword%"];
        }
        $list = $this->db->where($where)->order('create_time DESC')->select();

        $this->assign('list',$list);
        return $this->fetch();
    }
}
