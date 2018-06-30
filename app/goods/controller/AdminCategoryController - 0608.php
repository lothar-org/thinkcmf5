<?php
// namespace app\goods\controller;

use app\goods\model\GoodsCategoryModel;
use cmf\controller\AdminBaseController;
use think\Db;

// use app\admin\model\RouteModel;
// use app\admin\model\ThemeModel;

/**
 * 类型是首要条件
 * 多对多共用原则：同一类别同一游戏同一级别。
 */
class AdminCategoryController extends AdminBaseController
{
    protected $name  = 'goods_category';
    protected $flag  = '产品分类';
    protected $types = ['游戏', '物品分类']; //类别

    public function _initialize()
    {
        parent::_initialize();

        $this->db = Db::name($this->name);
        $this->db2 = Db::name('goods_category_fmw');
        $this->md = new GoodsCategoryModel;
        $this->assign('flag', $this->flag);
    }

    // 前置操作里，不支持驼峰命名
    protected $beforeActionList = [
        'first' => ['only' => 'index,add'],
        'second' => ['only' => 'add'],
    ];
    public function first()
    {
        $param  = $this->request->param();
        $gameId = $this->request->param('gameId', 0);
        $gameId = is_numeric($gameId) ? intval($gameId) : null;
        $path = '0';
        if ($gameId > 0) {
            $path .= '-'.$gameId;
            $second = isset($param['second']) ? intval($param['second']) : 0;
            if ($second>0) {
                $path .= '-'.$second;
                $third = isset($param['third']) ? intval($param['third']) : 0;
                if ($third>0) {
                    $path .= '-'.$third;
                } else {
                    $path .= '-0';
                }
            } else {
                $path .= '-0';
            }
        }
        $parent = $this->request->param('parent');
        if (!empty($parent)) {
            $path    = $this->db2->where('next_id', $parent)->value('path');
        }
        $this->param = $param;
        $this->path = $path;
        $this->parent = $parent;
    }
    public function second($preId=0,$nextId=0)
    {
        if (empty($preId) && isset($this->parent)) {
            $preId = $this->parent;
        } elseif (empty($preId) && empty($this->parent)) {
            $preId = $this->request->param('p',0);
        }

        /**
         * 前提条件：类别、游戏
         * 游戏作为顶级，是一对多关系，不存在多对多关系
         * 与该分类平级的分类的下一级所有数据。
         * Ⅰ 先找到pre_id，再通过[in(pre_id) and type]找到pre_id的所有下一级next_id，通过next_id找name。要查3次，建议子查询。
         * Ⅱ fmw的id和path都是惟一的
         * Ⅲ 可能导致效率降低，可用子查询先查path，再查level。0-2-3 => a.type and (a.level=3 and b.path like '%0-2-3-')
         * 
         */

        $pid = $this->db2->where('next_id',$preId)->value('pre_id');
        if ($pid>0) {
            
        }


        // 联表
        // $dooms = $this->md->getChild($preId);

        // 通过level查到所有数据
        // $level = $this->db->where('id',$preId)->value('level');
        // if ($level>1) {
        //     $dooms = $this->md->getFirstCate($nextId,['从库中筛选',0],['level'=>$level+1]);
        //     $this->assign('dooms',$dooms);
        //     return $dooms;
        // }
    }

    function test()
    {
        $res = Db::name('goods_category_fmw')->alias('a')
            ->field('*')
            ->join('__GOODS_CATEGORY__ b','a.next_id=b.id')
            ->where('')
            ->order('a.list_order')
            ->fetchSql(false)
            ->select();

        dump($res);
    }

    /**
     * 产品分类
     * @adminMenu(
     *     'name'   => '产品分类',
     *     'parent' => 'goods/AdminGoods/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 3,
     *     'icon'   => '',
     *     'remark' => '产品分类：游戏、服/区、',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $param = $this->param;
        $type   = $this->request->param('type/d', 0, 'intval');

        // dump($param);die;

        $list = $this->md->getlist($param);
        $list->appends($param);
        // dump($list);die;

        $this->op($this->path,['type'=>$type]);

        // $list = $this->md->getTreeArray($param['gameId'],$param);
        // $this->assign('list', $list);
        $this->assign('list', $list->items());
        $this->assign('pager', $list->render());
        $this->assign('types', $this->types);

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
        $type   = $this->request->param('type/d', 0, 'intval');
        $this->op($this->path, ['type' => $type]);

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
        // $data = $this->request->param();
        $data = $this->opp();

        $insid = $this->md->addCategory($data);

        if ($insid === false) {
            $this->error('添加失败!');
        }
        $this->success('添加成功', url('index'));
        // $this->success('添加成功!', url('edit', ['id' => $insid]));
    }

    /**
     * [op description]
     * @param  [type] $myId [description]
     * @param  [type] $data [$data['type']]
     * @return [type]       [description]
     */
    public function op($path, $data)
    {
        // dump($path);
        // die;
        $action = $this->request->action();
        $opt = $action == 'index' ? ['ALL', 0] : ['作为一级分类', 0];
        $typeV = c2c_get_status_set($data['type'], $this->types);

        $pathArr = explode('-', $path);
        // $pathArr = explode('-', strstr($path,'-',true));
        // array_pop($pathArr);
        // foreach ($pathArr as $key => $val) {
        // }

        $count = count($pathArr);
        // echo $count;die;
        // 如果是 edit方法，count多1才合理
        if ($action == 'edit') {
            switch ($count) {
                // case 1:
                case 2:
                    $this->assign('games', $this->md->getFirstCate(0, $opt));
                    break;
                case 3:
                    $this->assign('games', $this->md->getFirstCate($pathArr[1], $opt));
                    break;
                case 4:
                    $this->assign('games', $this->md->getFirstCate($pathArr[1], $opt));
                    $this->assign('seconds', c2c_get_options($pathArr[2], '请选择', $this->md->getChild($pathArr[1])));
                    break;
                case 5:
                    $this->assign('games', $this->md->getFirstCate($pathArr[1], $opt));
                    $this->assign('seconds', c2c_get_options($pathArr[2], '请选择', $this->md->getChild($pathArr[1])));
                    $this->assign('thirds', c2c_get_options($pathArr[3], '请选择', $this->md->getChild($pathArr[2])));
                    break;
            }
        } else {
            switch ($count) {
                case 1:
                    $this->assign('games', $this->md->getFirstCate(0, $opt));
                    break;
                case 2:
                    $this->assign('games', $this->md->getFirstCate($pathArr[1], $opt));
                    break;
                case 3:
                    $this->assign('games', $this->md->getFirstCate($pathArr[1], $opt));
                    $this->assign('seconds', c2c_get_options($pathArr[2], '请选择', $this->md->getChild($pathArr[1])));
                    break;
                case 4:
                    $this->assign('games', $this->md->getFirstCate($pathArr[1], $opt));
                    $this->assign('seconds', c2c_get_options($pathArr[2], '请选择', $this->md->getChild($pathArr[1])));
                    $this->assign('thirds', c2c_get_options($pathArr[3], '请选择', $this->md->getChild($pathArr[2])));
                    break;
            }
        }
        
        $this->assign('typeV', $typeV);

        // dump($games);die;
        // return [
        //     'type' => $type,
        //     'gameId'=> $gameId,
        // ];
    }

    /**
     * 新增&编辑提交共用
     * 禁止跨级别修改上级（移动）。level=1游戏
     */
    public function opp($valid = 'add')
    {
        // $data = $this->request->param();
        $data = $this->request->post();
        // 前置处理
        $data['name'] = trim($data['name']);
        $name         = strtolower($data['name']);
        $third        = isset($data['third']) ? $data['third'] : 0;
        $second       = isset($data['second']) ? $data['second'] : 0;
        $gameId       = isset($data['gameId']) ? $data['gameId'] : 0;

        if ($third > 0) {
            $preId = $third;
        } elseif ($second > 0) {
            $preId = $second;
        } elseif ($gameId > 0) {
            $preId = $gameId;
        } else {
            $preId = 0;
        }

        if ($preId > 0) {
            $valid = $valid . '2';
            // 如果更改上级，这个level就不是这么操作了，它的下级也会改变
            $data['level'] = $this->db->where('id', $preId)->value('level') + 1;
        } else {
            if (empty($data['code'])) {
                $data['code'] = preg_replace("/[^\x{4e00}-\x{9fa5}^0-9^A-Z^a-z]+/u", '-', $name);
                // cmf_strip_chars();
            } else {
                $data['code'] = trim($data['code']);
            }
        }
        // 数据验证
        $result = $this->validate($data, 'Category.' . $valid);
        if ($result !== true) {
            $this->error($result);
        }
        // 后置处理
        $data['gindex'] = substr($name, 0, 1);
        if (!preg_match('/[a-zA-Z]+/', $data['gindex'])) {
            $data['gindex'] = '#';
        }
        $data['is_hot'] = isset($data['is_hot']) ? $data['is_hot'] : 0;
        $data['pre_id'] = $preId;

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
        if ($id <= 0) {
            $this->error('操作错误');
        }

        $data = $this->md->get($id);
        $data = empty($data) ? $this->error('数据不存在') : $data->toArray();

        $fmw    = $this->db2->field('pre_id,path')->where('next_id', $id)->find();
        $this->second($fmw['pre_id']);
        $this->op($fmw['path'],$data);

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
        $data   = $this->opp('edit');
        $result = $this->md->editCategory($data);
        if ($result === false) {
            $this->error('编辑失败 或 数据无变化!');
        }
        $this->success('编辑成功!', url('index'));
    }

    /**
     * c2c_admin_log($this->name .':'. $id,'删除'. $this->flag .'：'. $find['name']);
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
        $id = $this->request->param('id');
        //获取删除的内容
        $find = $this->md->where('id', $id)->value('name');
        if (empty($find)) {
            $this->error('分类不存在!');
        }
        //判断此分类有无子分类（不算被删除的子分类）
        $childCount = $this->db2->where(['pre_id' => $id])->count();
        if ($childCount > 0) {
            $this->error('此分类有子类无法删除(包括回收站里)!');
        }
        $catetypeCount = Db::name('goods_catetype')->where('game_id', $id)->count();
        if ($catetypeCount > 0) {
            $this->error('此分类有产品无法删除!');
        }

        $result = $this->md->where('id', $id)->update(['delete_time' => time()]);
        if ($result) {
            c2c_recycle_bin($this->name, $id, $find);
            $this->success('删除成功!');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     *
     * @adminMenu(
     *     'name'   => '排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '',
     *     'param'  => ''
     * )
     */
    public function listOrder()
    {
        parent::listOrders($this->db);
        $this->success("排序更新成功！", '');
    }

    /*分类联动 这里我按层级建*/
    public function ajaxFirst()
    {
        $first  = $this->request->param('q', 0, 'intval');
        $sec   = $this->request->param('sec', '');
        $type   = $this->request->param('type', 0);
        $result = '';

        if (is_numeric($first)) {
            if (!($first ==0 && $sec=='second')) {
                $result = $this->md->getChild($first, ['type' => $type]);
            }
        }

        return $result;
        // return json_encode($data);
        // return '[{"id":1,"text":"League of Legends"},{"id":2,"text":"World of Warcraft EU"},{"id":7,"text":"CrossFire"},{"id":9,"text":"CSGO"},{"id":11,"text":"Rocket League"}]';
    }
}
