<?php
namespace app\tools\controller;

use cmf\controller\AdminBaseController;

/**
 * 网站留言
 */
class AdminGuestbookController extends AdminBaseController
{
    /**
     * 网站留言管理
     * @adminMenu(
     *     'name'   => '网站留言',
     *     'parent' => 'tools/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 300,
     *     'icon'   => '',
     *     'remark' => '网站留言管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        
    }
}
