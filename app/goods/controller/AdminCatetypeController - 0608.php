<?php
namespace app\goods\controller;

use app\goods\model\CatetypeModel;
use app\goods\model\FmwModel;
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
        $this->md2 = new FmwModel;
        $this->assign('flag', $this->flag);
    }

    // 前置操作里，不支持驼峰命名
    protected $beforeActionList = [
        'first' => ['only' => 'index,add,edit'],
    ];
    public function first($gameId = 0)
    {
        $this->gameId = $this->request->param('gameId/d', 0, 'intval');
        if ($this->gameId > 0) {
            $this->gameloft = $this->md2->getOne($this->gameId, 'a.code,b.name');
            $this->assign('gameloft', $this->gameloft);
        } else {
            $games = $this->md2->getNext2($gameId, 0, 0, false);
            if (empty($games)) {
                $this->error('请前去添加游戏', url('AdminCategory/add'));
            }
            $this->assign('games', $games);
        }
        $this->assign('gameId', $this->gameId);
    }

    /**
     * 游戏 & 类型
     * @adminMenu(
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
            $param = $this->request->param();

            $list = $this->md->getlist($param);
            $list->appends($param);

            $this->assign('list', $list->items());
            $this->assign('pager', $list->render());
        } else {
            $list = $this->db->alias('a')->field('a.*,b.name game_name,c.name AS type_name')
                ->join([
                    ['goods_category b', 'a.game_id=b.id', 'LEFT'],
                    ['goods_type c', 'a.type_id=c.id', 'LEFT'],
                ])->where($where)->select();
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
        // 类型select
        $typeArr = Db::name('goods_type')->field('id,name,code')->select()->toArray();
        $types   = c2c_get_options(0, '', $typeArr);

        // 默认code
        $codeloft = '';
        if (!empty($this->gameloft)) {
            $codeloft = $this->gameloft['code'] . '-' . $typeArr[0]['code'];
        }

        $this->assign('codeloft', $codeloft);
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

        $data = CatetypeModel::get($id);
        $data = empty($data) ? $this->error('数据不存在') : $data->toArray();
        // 类型select
        $typeArr = Db::name('goods_type')->field('id,name')->select()->toArray();
        $types   = c2c_get_options($data['type_id'], '', $typeArr);

        // if ($this->gameId=0 && $data['game_id']>0) {
        // }

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

        $result = $this->md->editCatetype($data, $id);
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

    public function ajaxGt()
    {
        $g = $this->request->get('g', 0);
        $t = $this->request->get('t', 0);
        if ($g == 0 || $t == 0) {
            $result = '';
        } else {
            $code1  = Db::name('goods_category_fmw')->where('id', $g)->value('code');
            $code2  = Db::name('goods_type')->where('id', $t)->value('code');
            $result = $code1 . '-' . $code2;
        }

        return $result;
    }
}
