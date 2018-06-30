<?php
namespace app\goods\controller;

use cmf\controller\AdminBaseController;
use app\goods\model\GoodsModel;
use think\Db;

/**
 * Class AdminGoodsController
 * @package app\goods\controller
 * @adminMenuRoot(
 *     'name'   =>'产品模块',
 *     'action' =>'default',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 30,
 *     'icon'   =>'product-hunt',
 *     'remark' =>'产品模块'
 * )
 */
class AdminGoodsController extends AdminBaseController
{
    protected $name = 'goods';
    protected $flag = '产品';
    // private $db;
    public function _initialize()
    {
        parent::_initialize();

        $this->db  = Db::name($this->name);
        $this->md  = new GoodsModel;
        $this->assign('flag', $this->flag);
    }

    /**
     * 产品管理
     * @adminMenu(
     *     'name'   => '产品管理',
     *     'parent' => 'goods/AdminGoods/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 1,
     *     'icon'   => '',
     *     'remark' => '产品管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $list = [];

        $this->assign('list',$list);
        return $this->fetch();
    }

    public function addPre()
    {
        $typeArr = Db::name('goods_type')->field('id,name')->select()->toArray();
        $types   = c2c_get_options(0, '', $typeArr);
        $games = model('Fmw')->getNext2(0, 0, 0);
// dump($types);die;
        $this->assign('types', $types);
        $this->assign('games', $games);
        $this->assign('action','addpre');
        return $this->fetch();
    }

    /**
     *
     * @adminMenu(
     *     'name'   => '添加',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        $param = $this->request->param();
        $type = $this->request->param('type0',0);
        $fmwId = getFmwId($this->request->param())[0];
        if (!empty($type) || !empty($fmwId)) {
            dump($type);
            dump($fmwId);die;
        }
        $this->assign('statusV',c2c_get_status_set(0,'goods_status'));
        return $this->fetch();
    }

    /**
     *
     * @adminMenu(
     *     'name'   => '添加提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        // $goods = $this->request->param('goods/a');
        // $info = $this->request->param('info/a');

        // $result = true;
        // Db::startTrans();
        // try {
        //     $insid = $this->db->insertGetId($goods);
        //     Db::name('goods_info')->insert($info);
        //     Db::commit();
        // } catch (\Exception $e) {
        //     Db::rollback();
        //     $result = false;
        // }




        // $data = $this->opp();
        // $insid = $this->md->addGoods($data);

        if (empty($insid)) {
            $this->error('添加失败');
        }
        // c2c_admin_log($this->name .':'. $insid, '添加' . $this->flag . '：' . $data['code']);
        $this->success('添加成功', url('index'));
        // $this->success('添加成功', url('edit', ['id' => $insid]));
    }

    /**
     *
     * @adminMenu(
     *     'name'   => '编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');
        if (empty($id)) {
            $this->error('非法操作');
        }

        $data = $this->md->get($id);
        $data = empty($data) ? $this->error('数据不存在') : $data->toArray();

        $this->assign($data);
        return $this->fetch();
    }

    /**
     *
     * @adminMenu(
     *     'name'   => '编辑提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        $id = $this->request->param('id', 0, 'intval');
        if (empty($id)) {
            $this->error('数据非法!');
        }
        // $goods = $this->request->param('goods/a');
        // $info = $this->request->param('info/a');

        // $result = true;
        // Db::startTrans();
        // try {
        //     $insid = $this->db->insertGetId($goods);
        //     Db::name('goods_info')->insert($info);
        //     Db::commit();
        // } catch (\Exception $e) {
        //     Db::rollback();
        //     $result = false;
        // }




        // $data = $this->opp('edit');
        // $result = $this->md->editGoods($data, $id);


        if (empty($result)) {
            $this->error('编辑失败 或 数据无变化');
        }
        $this->success('编辑成功', url('index'));
    }

    /**
     *
     * @adminMenu(
     *     'name'   => '删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $id     = $this->request->param('id', 0, 'intval');
        $name   = $this->db->where('id', $id)->value('name');
        $result = $this->db->where('id', $id)->delete();
        if ($result) {
            c2c_admin_log($this->name .':'. $id, '删除' . $this->flag . '：' . $name);
            $this->success(lang('DELETE_SUCCESS'));
        } else {
            $this->error(lang('DELETE_FAILED'));
        }
    }

    /**
     * [opp 共用]
     * @param  string $valid [description]
     * @param  array  $data  [description]
     * @return [type]        [description]
     */
    public function opp($valid = 'add', $data = [])
    {
        $data = $this->request->param();
        // if (!empty($data['icon'])) {
        //     $data['icon'] = cmf_asset_relative_url($data['icon']);
        // }
        $result = $this->validate($data, 'Catetype.' . $valid);
        if ($result !== true) {
            $this->error($result);
        }
        // code开启路由 在模型层中处理
        $data['slide_id'] = 1;
// dump($data);die;
        return $data;
    }
}
