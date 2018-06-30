<?php 
namespace app\index\controller;
use think\Controller;
use think\Request;

/**
 * 空控制器定义
 * 这个位置任意放，但不可置于禁止访问的模块
 */
class ErrorController extends Controller
{
    /**
     * [_empty description]
     * 这个要写在每一个控制器中
     * @return [type] [description]
     */
    public function _empty(){
        // $this->redirect(url('goods/index/index'));//空方法处理
        $atom = $request->action();
        $this->error($atom.'方法不存在！',url('goods/Index/index'));
    }

    /**
     * [index description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function index(Request $request)
    {
        // $this->redirect(url('home/index/index'));//空控制器重定向
        // 带提示
        $atom = $request->controller();
        $this->error($atom.'控制器不存在！',url('goods/Index/index'));
    }
}
?>