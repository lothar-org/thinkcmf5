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
        'first' => ['only' => 'index,add'],
    ];
    public function first($gameId=0)
    {
        $gameId = $gameId>0?$gameId:$this->request->param('gameId/d', 0, 'intval');
        if ($gameId > 0) {
            $this->gameloft = $this->md2->getOne($gameId, 'a.code,b.name');
            $this->assign('gameloft', $this->gameloft);
        } else {
            $games = $this->md2->getNext2(0, 0, 0, false);
            if (empty($games)) {
                $this->error('请前去添加游戏', url('AdminCategory/add'));
            }
            $this->assign('games', $games);
        }
        $this->gameId = $gameId;
        $this->assign('gameId', $gameId);
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
            $list = $this->db->alias('a')->field('a.*,b.name type_name,d.name AS game_name')
                ->join([
                    ['__GOODS_TYPE__ b', 'a.type_id=b.id', 'LEFT'],
                    ['goods_category_fmw c', 'a.game_id=c.id', 'LEFT'],
                    ['goods_category d', 'c.next_id=d.id'],
                ])->where($where)->fetchSql(false)->select();
            // dump($list);die;
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
// die;
        $insid = $this->md->addCatetype($data);
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

        $post = CatetypeModel::get($id);
        $post = empty($post) ? $this->error('数据不存在') : $post->toArray();
        // 游戏
        // if ($post['game_id'] > 0) {
        //     $gameloft = $this->md2->getOne($post['game_id'], 'b.name');
        //     $this->assign('gameloft', $gameloft);
        // }
        $this->first($post['game_id']);
        $games = $this->md2->getNext2(0, 0, 0, false);
        // 类型select
        $typeArr = Db::name('goods_type')->field('id,name')->select()->toArray();
        $types   = c2c_get_options($post['type_id'], '', $typeArr);

        // if ($this->gameId=0 && $post['game_id']>0) {
        // }

        $hots = Db::name('goods_hot_keyword')->field('id,name,url,count,is_rec,status')->where('catetype_id',$id)->select();
        // dump($hots);die;


        $this->assign('post',$post);
        $this->assign('games', $games);
        $this->assign('types', $types);
        $this->assign('hots', $hots);
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
        $id = $this->request->param('post.id', 0, 'intval');
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
        $name   = $this->db->where('id', $id)->value('title');
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
        $data = $this->request->param('post/a');
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

    public function ajaxAdv()
    {
        $t = $this->request->get('t',0);
        $result = '';
        return $result;
    }
}
