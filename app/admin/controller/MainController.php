<?php
namespace app\admin\controller;

use cmf\controller\AdminBaseController;

class MainController extends AdminBaseController
{
    /**
     * 后台欢迎页
     */
    public function index()
    {
        /*邮箱设置*/
        $smtpSetting = cmf_get_option('smtp_setting');
        $this->assign('has_smtp_setting', empty($smtpSetting) ? false : true);

        return $this->fetch();
    }
}