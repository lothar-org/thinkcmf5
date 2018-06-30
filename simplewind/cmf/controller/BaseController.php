<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace cmf\controller;

use think\Controller;
use think\Request;
use think\View;
use think\Config;

class BaseController extends Controller
{
    /**
     * 构造函数
     * @param Request $request Request对象
     * @access public
     */
    public function __construct(Request $request = null)
    {
        if (!cmf_is_installed() && $request->module() != 'install') {
            header('Location: ' . cmf_get_root() . '/?s=install');
            exit;
        }

        if (is_null($request)) {
            $request = Request::instance();
        }

        $this->request = $request;

        $this->_initializeView();
        $this->view = View::instance(Config::get('template'), Config::get('view_replace_str'));


        // 控制器初始化
        $this->_initialize();

        // 前置操作方法
        if ($this->beforeActionList) {
            foreach ($this->beforeActionList as $method => $options) {
                is_numeric($method) ?
                    $this->beforeAction($options) :
                    $this->beforeAction($method, $options);
            }
        }
    }


    // 初始化视图配置
    protected function _initializeView()
    {
    }

    /**
     *  排序 排序字段为list_orders数组 POST 排序字段为：list_order
     */
    protected function listOrders($model)
    {
        if (!is_object($model)) {
            return false;
        }

        $pk  = $model->getPk(); //获取主键名称
        $ids = $this->request->post("list_orders/a");

        if (!empty($ids)) {
            foreach ($ids as $key => $r) {
                $data['list_order'] = $r;
                $model->where([$pk => $key])->update($data);
            }
        }

        return true;
    }

    protected function deletes($model,$extra=[])
    {
        if (!is_object($model)) {
            return false;
        }
        $pk = $model->getPk();
        $param = $this->request->param();

        // if (isset($param['id'])) {
        //     $id = $this->request->param('id',0,'intval');
        //     $find = $this->db->field('deal_id,obj_id')->where($pk,$id)->find();
        //     $result = $this->db->where($pk,$id)->delete();
        //     if ($result) {
        //         c2c_admin_log($this->name .':'. $id, '删除'. $this->flag .'：from ID:'.$find['deal_id'].' to ID:'.$find['obj_id']);
        //         return true;
        //     } else {
        //         return false;
        //     }
        // }

        // if (isset($param['ids'])) {
        //     $ids    = $this->request->param('ids/a');
        //     $result = $this->db->where([$pk => ['in', $ids]])->delete();
        //     if ($result) {
        //         c2c_admin_log($this->name .':'. $id, '批量删除'. $this->flag .'：'. implode(',', $ids));
        //         return true;
        //     }
        // }
        return false;
    }


}