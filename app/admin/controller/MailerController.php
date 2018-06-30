<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2028 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 915273691@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use think\Validate;

class MailerController extends AdminBaseController
{
    /**
     * 邮箱配置
     * @adminMenu(
     *     'name'   => '邮箱配置',
     *     'parent' => 'admin/Setting/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '邮箱配置',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $where   = [];
        $keyword = $this->request->param('keyword', '');
        if (!empty($keyword)) {
            $where['from_name|from|username'] = ['like', "%$keyword%"];
        }
        $list = Db::name('email_set')->where($where)->select();

        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 邮箱配置添加
     * @adminMenu(
     *     'name'   => '邮箱配置添加',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 1,
     *     'icon'   => '',
     *     'remark' => '邮箱配置添加',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        return $this->fetch();
    }

    /**
     * 邮箱配置提交添加
     * @adminMenu(
     *     'name'   => '邮箱配置提交添加',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '邮箱配置提交添加',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        $post = array_map('trim', $this->request->param());
        if (in_array('', $post) && !empty($post['smtpsecure'])) {
            $this->error("不能留空！");
        }
        $post['port'] = empty($post['port']) ? 25 : intval($post['port']);

        $insid = Db::name('email_set')->insert($post);

        if (empty($insid)) {
            $this->error('添加失败！');
        }
        $this->opset($post, $insid);
        $this->success('添加成功', url('index'));
        // $this->success('添加成功', url('edit', ['id' => $insid]));
    }

    /**
     * 邮箱配置编辑
     * @adminMenu(
     *     'name'   => '邮箱配置编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 2,
     *     'icon'   => '',
     *     'remark' => '邮箱配置编辑',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');

        $email = Db::name('email_set')->where('id', $id)->find();

        $this->assign($email);
        return $this->fetch();
    }

    /**
     * 邮箱配置编辑
     * @adminMenu(
     *     'name'   => '邮箱配置提交编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '邮箱配置提交保存',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        $id = $this->request->param('id', 0, 'intval');
        if (empty($id)) {
            $this->error('非法操作！');
        }
        $post = array_map('trim', $this->request->param());
        if (in_array('', $post) && !empty($post['smtpsecure'])) {
            $this->error("不能留空！");
        }
        $post['port'] = empty($post['port']) ? 25 : intval($post['port']);

        $result = Db::name('email_set')->where('id', $id)->update($post);

        if (empty($result)) {
            $this->error('编辑失败！');
        }
        $this->opset($post, $id);
        $this->success('编辑成功', url('index'));
    }

    /**
     * 邮箱配置删除
     * @adminMenu(
     *     'name'   => '邮箱配置删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '邮箱配置删除',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $id = $this->request->param('id/d', 0, 'intval');
        if ($id == 0) {
            $this->error('数据不合法！');
        }
        $result = Db::name('email_set')->where('id', $id)->delete();
        if (empty($result)) {
            $this->error('删除失败');
        }
        $this->opset(['status' => 0], $id);
        $this->success('删除成功');
    }

    /**
     * 邮件模板
     * @adminMenu(
     *     'name'   => '邮件模板',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 15,
     *     'icon'   => '',
     *     'remark' => '邮件模板',
     *     'param'  => ''
     * )
     */
    public function template()
    {
        $allowedTemplateKeys = ['verification_code'];
        $templateKey         = $this->request->param('template_key');

        if (empty($templateKey) || !in_array($templateKey, $allowedTemplateKeys)) {
            $this->error('非法请求！');
        }

        $template = cmf_get_option('email_template_' . $templateKey);
        $this->assign($template);
        return $this->fetch('template_verification_code');
    }

    /**
     * 邮件模板提交
     * @adminMenu(
     *     'name'   => '邮件模板提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '邮件模板提交',
     *     'param'  => ''
     * )
     */
    public function templatePost()
    {
        $allowedTemplateKeys = ['verification_code'];
        $templateKey         = $this->request->param('template_key');

        if (empty($templateKey) || !in_array($templateKey, $allowedTemplateKeys)) {
            $this->error('非法请求！');
        }

        $data = $this->request->param();

        unset($data['template_key']);

        cmf_set_option('email_template_' . $templateKey, $data);

        $this->success("保存成功");
    }

    /**
     * 邮件发送测试
     * @adminMenu(
     *     'name'   => '邮件发送测试',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '邮件发送测试',
     *     'param'  => ''
     * )
     */
    public function sendone()
    {
        if ($this->request->isPost()) {
            $validate = new Validate([
                'to'      => 'require|email',
                'subject' => 'require',
                'content' => 'require',
            ]);
            $validate->message([
                'to.require'      => '收件箱不能为空！',
                'to.email'        => '收件箱格式不正确！',
                'subject.require' => '标题不能为空！',
                'content.require' => '内容不能为空！',
            ]);

            $data = $this->request->param();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            $result = cmf_send_email($data['to'], $data['subject'], $data['content']);
            if ($result && empty($result['error'])) {
                $this->success('发送成功');
            } else {
                $this->error('发送失败：' . $result['message']);
            }
        } else {
            return $this->fetch();
        }
    }

    /**
     * 邮件批量发送
     * 解决多个发件箱，每个发件箱每日500条限制的问题
     * @adminMenu(
     *     'name'   => '邮件批量发送',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 5,
     *     'icon'   => '',
     *     'remark' => '',
     *     'param'  => ''
     * )
     */
    public function sendbatch()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            if (!empty($data['to_group'])) {
                $uuu = model('user/Group')->getUsers($data['to_group'], 'email');
                // dump($uuu);die;
            } elseif (!empty($data['to'])) {
                $uuu = trim($data['to']);
                if (strpos($uuu, ',')) {
                    $uuu = explode(',', $uuu);
                }
            } else {
                $this->error('收件箱不能为空！');
            }

            /*
             * 批量发送核心
             * c2c_get_emailset();
             */
            $count = $max = count($uuu);
            if ($count < 1) $this->error('请填写收件箱！');

            $sets  = c2c_get_emailset();
            $db_e  = Db::name('email_set');
            $start = 0;
            $step = 0;
            foreach ($sets as $r) {
                $remain = $r['upper_limit'] - $r['used_limit'];
                //跳出本次循环
                if ($remain < 1) continue;
                if ($count > $remain) {
                    $uuu_part = array_slice($uuu, $start, $remain, true);
                    $start    = $start + $remain;
                    $uuu      = array_slice($uuu, $start + 1, $max, true);
                    $result   = c2c_send_email($uuu_part, $data['subject'], $data['content'], $r);
                    if ($result && $result['error'] == 1) {
                        $this->error('发送失败：' . $result['message']);
                    }
                    $db_e->where('id', $r['id'])->inc('used_limit', $remain);
                    $count = $count - $remain;
                    $step++;
                } else {
                    $result = c2c_send_email($uuu, $data['subject'], $data['content'], $r);
                    $db_e->where('id', $r['id'])->inc('used_limit', $count);
                    $count = $count - $remain;
                    $step++;
                    break; //终止循环
                }
                // $this->opset();
            }
            if ($step > 0) cache('smtp_setting', null);

            // 发送结果
            if ($result && empty($result['error'])) {
                $this->success('发送成功');
            } else {
                $this->error('发送失败：' . $result['message']);
            }
        } else {
            $groups = model('user/Group')->getGroupSet();
            $this->assign('groups', $groups);
            return $this->fetch();
        }
    }

    // 配置变化时重设缓存。s_option表的smtp_setting不再更新。 !=与<>的区别？
    public function opset($set, $id)
    {
        $where = 'status=1 AND (upper_limit-used_limit)>0';
        if ($set['status'] == 0) {
            $where .= ' AND id != ' . $id;
        }
        $sets = Db::name('email_set')->where($where)->column('*', 'id');
        cache('smtp_setting', $sets);
        // cmf_set_option('smtp_setting', $set);
    }

    public function test()
    {
        $sets = Db::name('email_set')->where('status', 1)->column('*', 'id');
        dump($sets);
        $smtp_setting = cache('smtp_setting');
        dump($smtp_setting);
        $smtp_setting = c2c_get_emailset(true);
        dump($smtp_setting);die;

        // $emailSetting = cmf_get_option('smtp_setting');
        // dump($emailSetting);die;
        // dump(c2c_send_mail('645936998@qq.com','test','发送成功'));die;
        // $this->assign($emailSetting);
    }
}
