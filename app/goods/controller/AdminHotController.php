<?php
namespace app\goods\controller;

use cmf\controller\AdminBaseController;
// use think\Db;

class AdminHotController extends AdminBaseController
{
    /**
     * 推荐热搜词
     * @adminMenu(
     *     'name'   => '热搜词',
     *     'parent' => 'goods/AdminGoods/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 13,
     *     'icon'   => '',
     *     'remark' => '推荐热搜词',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        echo '可以根据用户搜索习惯来建立热门标签。';
        return $this->fetch();
    }
}
