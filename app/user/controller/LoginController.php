<?php
namespace app\user\controller;

use cmf\controller\HomeBaseController;
// use app\user\model\UserModel;
// use think\Validate;
use think\Db;

class LoginController extends HomeBaseController
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
            return $this->fetch(":login");
        }
    }

    /**
     * [doLogin 登录提交,ajax实现时请自行修改]
     * 业务要求
     *     第三方登录
     *     验证码验证
     *     身份验证
     *     其它终端登录的操作
     *     验证失败5次，锁定15分钟
     *     频繁登录15次，锁定1小时
     *     记住密码
     *     登录日志
     * @return [type] [description]
     */
    public function doLogin()
    {
        $user = $this->request->param();
        if (empty($user['username'])) {
            $this->error('用户名丢失');
        }

        $mq     = Db::name('member');
        $result = $mq->where('username', $user['username'])->find();

        if (!empty($result)) {
            $cpr = cmf_compare_password($user['password'], $result['password']);
            if ($cpr) {
                //拉黑判断。
                if ($result['status'] == 0) {
                    $this->error('你已被拉黑，请联系管理员');
                }
                // 全局session
                session('user', $result);
                $data = [
                    'login_time' => time(),
                    'login_ip'   => get_client_ip(0, true),
                ];
                $mq->where('id', $result['id'])->update($data);
                // $bros = new \Browser();
                // $token = cmf_generate_user_token($result['id'], $bros->getPlatform());
                $token = cmf_generate_user_token($result['id']);
                if (!empty($token)) {
                    session('token', $token);
                }
                $this->success('登录成功', url('goods/Index/index'));
            }
            $this->error('密码错误');
        }
        $this->error('用户数据不存在');
    }

    public function doLoginAdmin()
    {
        // 从后台过来体验
        $uid = $this->request->param('uid',0,'intval');
        if (empty($uid)) {
            $this->error('禁止授权登录');
        }
        $result = Db::name('member')->where(['id'=>$uid])->find();
        if (!empty($result)) {
            if ($result['status'] == 0) {
                $this->error('你已被拉黑，请联系管理员');
            }
            session('user', $result);
            // $bros = new \Browser();
            // $token = cmf_generate_user_token($result['id'], $bros->getPlatform());
            $token = cmf_generate_user_token($result['id']);
            if (!empty($token)) {
                session('token', $token);
            }
            $this->success('登录成功', url('goods/Index/index'));
        }
        $this->error('用户数据不存在');
    }

    /**
     * 找回密码
     */
    public function findPassword()
    {
        return $this->fetch(':find_pwd');
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        session('user', null); //只有前台用户退出
        // cookie('user',null);
        return redirect($this->request->root() . "/");
    }
}
