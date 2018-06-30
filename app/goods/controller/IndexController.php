<?php
namespace app\goods\controller;

use cmf\controller\HomeBaseController;
// use think\Db;

/**
 * 首页
 */
class IndexController extends HomeBaseController
{
    public function _empty(){
        // $this->redirect(url('goods/index/index'));//空方法处理
        $atom = request()->action();
        $this->error($atom.'方法不存在！',url('goods/Index/index'));
    }

    public function _initialize()
    {
        parent::_initialize();
    }

    function index()
    {
        // echo strlen(cmf_generate_user_token(1));
        // $bros  = new \Browser();
        // $token = cmf_generate_user_token(1, $bros->getPlatform());
        
        $this->assign('crumbs','面包屑导航');
        return $this->fetch();
    }

    function test()
    {
        $param = $this->request->param();
        echo 'test1';
        dump($param);
    }
}
