<?php
namespace app\user\controller;

use app\user\model\UserModel;
use cmf\controller\HomeBaseController;

class IndexController extends HomeBaseController
{

    /**
     * 前台用户首页(公开)
     */
    public function index()
    {

        // $this->assign($user->toArray());
        // $this->assign('user',$user);
        // return $this->fetch(":index");
    }

}
