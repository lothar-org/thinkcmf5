<?php
namespace app\index\controller;

use think\Controller;

class AjaxController extends Controller
{
    /*
    * 消息定时获取
    * 可使用 socket 实时获取新消息
    */
    public function news()
    {
        $tpl = c2c_get_news('',true);
        // $tpl = str_replace('__STATIC__', '/static', $tpl);

        echo $tpl;exit;
        // return $tpl;
    }

    public function name()
    {
        return [];
    }
}