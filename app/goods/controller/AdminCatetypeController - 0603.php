<?php
// namespace app\goods\controller;

use app\goods\model\CatetypeModel;
use app\goods\model\GoodsCategoryModel;
use cmf\controller\AdminBaseController;
use think\Db;

/**
 * 这里 必须明确 游戏和类型
 */
class AdminCatetypeController extends AdminBaseController
{
    protected $name = 'goods_catetype';
    protected $flag = '游戏&类型';
    // private $db;
    public function _initialize()
    {
        parent::_initialize();

        $this->db  = Db::name($this->name);
        $this->md  = new CatetypeModel;
        $this->md2 = new GoodsCategoryModel;
        $this->assign('flag', $this->flag);
    }

    // 前置操作里，不支持驼峰命名
    protected $beforeActionList = [
        'first' => ['only' => 'index,add,edit'],
    ];
    public function first()
    {
        $this->gameId = $this->request->param('gameId', 0, 'intval');
        if ($this->gameId > 0) {
            $gameloft = cache('gameloft');
            if (is_array($gameloft)) {
                if (empty($gameloft[$this->gameId])) {
                    $gameloft = array_merge($gameloft,[$this->gameId=>$this->md2->where('id',$this->gameId)->value('name')]);
                    cache('gameloft',$gameloft,60);
                }
            } else {
                $gameloft[$this->gameId] = $this->md2->where('id',$this->gameId)->value('name');
                cache('gameloft',$gameloft,60);
            }
            $this->assign('gameName', $gameloft[$this->gameId]);
        } else {
            $games = $this->md2->getFirstCate();
            $this->assign('games', $games);

        }
        $this->assign('gameId', $this->gameId);
    }

    /**
     * 游戏 & 类型
     * @adminMenuNull(
     *     'name'   => '游戏 & 类型',
     *     'parent' => 'goods/AdminGoods/default',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 100,
     *     'icon'   => '',
     *     'remark' => '游戏&类型关联模型',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $where = [];
        if (!empty($this->gameId)) {
            $where['game_id'] = $this->gameId;
        }

        if (empty($this->gameId)) {
            $param   = $this->request->param();

            $list = $this->md->getlist($param);
            $list->appends($param);

            cache('gameloft',null);
            $this->assign('list', $list->items());
            $this->assign('pager', $list->render());
        } else {
            $list = $this->db->where($where)->select();
            $this->assign('list', $list);
        }

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
        $types = Db::name('goods_type')->field('id,name')->select()->toArray();
        $types = c2c_get_options(0, '', $types);
        if (empty($this->gameId)) {
            cache('gameloft',null);
        }

        $this->assign('types', $types);
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
        $data = $this->opp();

        $insid = $this->md->addCatetype($data);
        if (empty($insid)) {
            $this->error('添加失败');
        }
        $this->success('添加成功', url('index'));
        // $this->success('添加成功', url('edit', ['id' => $insid]));
    }

    /**
     * [opp 共用]
     * @param  string $valid [description]
     * @param  array  $data  [description]
     * @return [type]        [description]
     */
    public function opp($valid='add',$data=[])
    {
        $data   = $this->request->param();
        // if (!empty($data['icon'])) {
        //     $data['icon'] = cmf_asset_relative_url($data['icon']);
        // }
        $result = $this->validate($data, 'Catetype.'.$valid);
        if ($result !== true) {
            $this->error($result);
        }
        // code开启路由 这个在模型层中处理

        return $data;
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
        $id     = $this->request->param('id', 0, 'intval');

        $data = $this->db->where('id', $id)->find();
        $types = Db::name('goods_type')->field('id,name')->select()->toArray();
        $types = c2c_get_options($data['type_id'], '', $types);
        if (empty($this->gameId)) {
            cache('gameloft',null);
        }

        $this->assign($data);
        $this->assign('types', $types);
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
        $data = $this->opp('edit');

        $result = $this->md->editCatetype($data,$id);
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
        $id   = $this->request->param('id', 0, 'intval');
        $name = $this->db->where('id', $id)->value('name');
        cache('gameloft',null);
        $result = $this->db->where('id', $id)->delete();
        if ($result) {
            c2c_admin_log($this->name .':'. $id, '删除' . $this->flag . '：' . $name);
            $this->success(lang('DELETE_SUCCESS'));
        } else {
            $this->error(lang('DELETE_FAILED'));
        }
    }
}
