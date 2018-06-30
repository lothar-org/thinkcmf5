<?php
namespace app\tools\controller;

use cmf\controller\AdminBaseController;

class AdminCrontabController extends AdminBaseController
{
    /**
     * 定时任务设置
     * @adminMenu(
     *     'name'   => '定时任务',
     *     'parent' => 'tools/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 200,
     *     'icon'   => '',
     *     'remark' => '定时任务管理',
     *     'param'  => ''
     * )
     */
    public function setting()
    {
        if ($this->request->isPost()) {
            $this->success('保存成功');
        } else {
            return $this->fetch();
        }
    }

}
