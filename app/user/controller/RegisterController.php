<?php
namespace app\user\controller;

use cmf\controller\HomeBaseController;

// use think\Validate;
// use app\user\model\UserModel;

/**
 * 前台用户注册
 */
class RegisterController extends HomeBaseController
{
    public function index()
    {
        $redirect = $this->request->post("redirect");
        if (empty($redirect)) {
            $redirect = $this->request->server('HTTP_REFERER');
        } else {
            $redirect = base64_decode($redirect);
        }
        session('login_http_referer', $redirect);
        if (cmf_is_user_login()) {
            //已经登录时直接跳到首页
            return redirect($this->request->root() . '/');
        } else {
            return $this->fetch(":register");
        }
    }

    /**
     * [doLogin 登录提交,ajax实现时请自行修改]
     * 业务要求
     *     验证码验证
     *     是否短信验证？
     *     数据格式验证
     *     验证失败5次，禁止注册15分钟
     * @return [type] [description]
     */
    public function doRegister()
    {

    }

}
