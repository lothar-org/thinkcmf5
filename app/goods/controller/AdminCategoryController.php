<?php
namespace app\goods\controller;

// use app\goods\model\GoodsCategoryModel;
use app\goods\model\FmwModel;
use cmf\controller\AdminBaseController;
use think\Db;

/**
 * 类型是首要条件，但游戏按逻辑应该是不归它管的，这里就尴尬了
 * 多对多共用原则：同一类别同一游戏同一级别。
 * 联动获取fmw数据，pre_id<=>next_id找上下级关系。通过id或path或code确定
 * 被选中的是fmwId
 */
class AdminCategoryController extends AdminBaseController
{
    protected $name  = 'goods_category_fmw';
    protected $flag  = '产品分类';
    protected $types = ['游戏', '物品分类']; //类别

    public function _initialize()
    {
        parent::_initialize();

        $this->db  = Db::name($this->name);
        $this->db2 = Db::name('goods_category');
        $this->md  = new FmwModel;
        $this->assign('flag', $this->flag);
    }

    // 前置操作里，不支持驼峰命名
    protected $beforeActionList = [
        'first'  => ['only' => 'index,add'],
        'second' => ['only' => 'add'],
    ];
    public function first()
    {
        $param      = $this->request->param();
        $this->type = $this->request->param('type/d', 0, 'intval');
        // dump($this->type);
        // dump($param);
        // 针对多对多特殊处理
        $fmw = getFmwId($param);
        // dump($fmw);
        // die;
        $fmwId = $fmw[0];
        if ($fmwId > 0) {
            $path  = $this->db->where('id', $fmwId)->value('path');
            $count = $this->db->where('path', 'like', $path . '-%')->count();
            if ($count > 0) {
                $path .= '-0';
                array_push($fmw[1], 0);
            }
        } else {
            $path = '0';
        }
// dump($path);
        // die;
        $this->param    = $param;
        $this->path     = $path;
        $this->fmwId    = $fmwId;
        $this->selected = $fmw[1];
    }

    /**
     * 获取name的平行数据  与该分类平级的分类的下一级所有数据。
     * 前提条件：类别、游戏，无法从下级next_id直接找到上级pre_id
     * 游戏作为顶级，是一对多关系，不存在多对多关系
     * 从库中多选
     * @param  integer $fmwId    [关联ID]
     * @return [type]            [description]
     */
    public function second($fmwId = 0, $type = 0)
    {
        // if (empty($fmwId) && isset($this->fmwId)) {
        //     $fmwId = $this->fmwId;
        // }
        // if (!empty($fmwId)) {
        //     $dooms = $this->md->getSiblings($fmwId, $type, ['从库中筛选', 0]);
        //     $this->assign('dooms', $dooms);
        // }

        // $this->ajaxDooms();
    }

    public function test($value = '')
    {
        $a = $this->md->getSiblings(6);
        dump($a);
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

        // dump($param);die;

        $list = $this->md->getlist($param);
        $list->appends($param);
        // dump($list);die;

        $this->op($this->path, $this->type, $this->selected);

        // $list = $this->md->getTreeArray($param['gameId'],$param);
        // $this->assign('list', $list);
        $this->assign('list', $list->items());
        $this->assign('pager', $list->render());
        $this->assign('types', $this->types);
        $this->assign('action','index');

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
        $this->op($this->path, $this->type, $this->selected);

        $this->assign('action','add');
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

        $insid = $this->md->addFmw($data);

        if ($insid === false) {
            $this->error('添加失败!');
        }
        $this->success('添加成功', url('index'));
        // $this->success('添加成功!', url('edit', ['id' => $insid]));
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

        $data = $this->md->getOne($id);
        if (empty($data)) {
            $this->error('数据不存在');
        }
        // $fpa = $this->md->getParents($id);
        $fpa = explode('-', $data['fmw_path']);
// dump($fpa);die;
        $this->second($data['id'], $data['type']);
        $this->op($data['path'], $data['type'], $fpa);

        $this->assign($data);
        $this->assign('action','edit');

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
        $id = $this->request->param('fmw.id', 0, 'intval');
        if (empty($id)) {
            $this->error('数据非法!');
        }
        $data   = $this->opp('edit');
        $result = $this->md->editFmw($data, $id);
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
        $next_id = $this->db->where('id',$id)->value('next_id');
        if (empty($next_id)) {
            $this->error('分类不存在!');
        }
        //判断有无关联
        $catetypeCount = Db::name('goods_catetype')->where('game_id', $id)->count();
        if ($catetypeCount > 0) {
            $this->error('此分类有关联数据无法删除!');
        }

        // 如何清理不需要的category
        $count = $this->db->where('next_id',$next_id)->count();

        $result = true;
        Db::startTrans();
        try {
            $result = $this->db->where('id',$id)->delete();
            if ($count==0) {
                $result = $this->db2->where('id',$next_id)->delete();
            }
            // c2c_admin_log($this->name .':'. $id, '删除' . $this->flag . '：' . $name);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $result = false;
        }
        if ($result===false) {
            $this->error('删除失败');
        } else {
            $this->success('删除成功!');
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

    /**
     * [op description]
     * @param  [type] $myId [description]
     * @param  [type] $data [$data['type']]
     * @return [type]       [description]
     */
    public function op($path, $type, $fmw_path)
    {
        // dump($path);
        // die;
        $action = $this->request->action();
        $opt1   = $action == 'index' ? ['ALL', 0] : ['作为一级栏目', 0];
        $opt2   = ['请选择', 0];
        $typeV  = c2c_get_status_set($type, $this->types);

        // $path = substr($path,0,strripos($path,'-'));//去掉最后一级
        $pa = explode('-', $path);
        // array_pop($pa);
        // foreach ($pa as $key => $val) {
        // }

        $count = count($pa);
        // 如果是 edit方法，count多1才合理

        // dump($fmw_path);
        // echo $count;
        // dump($pa);
        // die;
        // 如何获取联动当前 ID. $this->selected
        if ($action == 'edit') {

            switch ($count) {
                case 2:
                    $this->assign('games', $this->md->getNext2(0, 0, $type, $opt1));
                    break;
                case 3:
                    $this->assign('games', $this->md->getNext2($fmw_path[1], 0, $type, $opt1));
                    $this->assign('seconds', $this->md->getNext2(0, $pa[1], $type, $opt2));
                    break;
                case 4:
                    $this->assign('games', $this->md->getNext2($fmw_path[1], 0, $type, $opt1));
                    $this->assign('seconds', $this->md->getNext2($fmw_path[2], $pa[1], $type, $opt2));
                    $this->assign('thirds', $this->md->getNext2($fmw_path[3], $pa[2], $type, $opt2));
                    break;
                case 5:
                    
                    break;
            }

        } else {

            // index 里末级应不显示
            // 根据pre_id判断是否为末级
            $num = 1;
            if ($action == 'index') {
                if ($count == 2) {
                    $num = $this->md->checkChilds($pa[0]);
                } elseif ($count == 3) {
                    $num = $this->md->checkChilds($pa[1]);
                } elseif ($count == 4) {
                    $num = $this->md->checkChilds($pa[2]);
                }
            }

            switch ($count) {
                case 1:
                    $this->assign('games', $this->md->getNext2(0, 0, $type, $opt1));
                    break;
                case 2:
                    $this->assign('games', $this->md->getNext2($fmw_path[1], 0, $type, $opt1));
                    break;
                case 3:
                    $this->assign('games', $this->md->getNext2($fmw_path[1], 0, $type, $opt1));
                    if ($num > 0) {
                        $this->assign('seconds', $this->md->getNext2($fmw_path[2], $pa[1], $type, $opt2));
                    }
                    break;
                case 4:
                    $this->assign('games', $this->md->getNext2($fmw_path[1], 0, $type, $opt1));
                    $this->assign('seconds', $this->md->getNext2($fmw_path[2], $pa[1], $type, $opt2));
                    if ($num > 0) {
                        $this->assign('thirds', $this->md->getNext2($fmw_path[3], $pa[2], $type, $opt2));
                    }
                    break;
            }
        }

        $this->assign('typeV', $typeV);
    }

    /**
     * 新增&编辑提交共用
     * 同时与index共用的： type,gameId,second,third
     */
    public function opp($valid = 'add')
    {
        $data = $this->request->post();
        $cate = $data['cate'];
        $fmw  = $data['fmw'];
        // dump($data);

        ### 前置处理，需要验证的
        // 获取上级数据
        $fmwId = getFmwId($data)[0];
        // $this->db->where('id', $fmwId)->column('code,path','next_id')
        $oldPre = $fmwId > 0 ? $this->db->field('next_id,parent_id,fmw_path,path,code')->where('id', $fmwId)->find() : [];
        // dump($oldPre);
        // CATE 
        $cate['name'] = trim($cate['name']);
        $name         = strtolower($cate['name']);
        // FMW 
        // 保证code的唯一性
        if (empty($fmw['code'])) {
            $fmw['code'] = (isset($oldPre['code']) ? $oldPre['code'] : '') . '-' . preg_replace("/[^\x{4e00}-\x{9fa5}^0-9^A-Z^a-z]+/u", '-', $name);
            // cmf_strip_chars();
        } else {
            $fmw['code'] = trim($fmw['code']);
        }

        ### 数据验证
        $validata = array_merge($cate, $fmw);
        // dump($validata);die;
        $result = $this->validate($validata, 'Category.' . $valid);
        if ($result !== true) {
            $this->error($result);
        }

        ### 后置处理，不需要验证的
        // CATE 
        $cate['gindex'] = substr($name, 0, 1);
        if (!preg_match('/[a-zA-Z]+/', $cate['gindex'])) {
            $cate['gindex'] = '#';
        }
        $cate['type']   = $data['type'];
        $cate['is_hot'] = isset($cate['is_hot']) ? $cate['is_hot'] : 0;
        if (!empty($cate['thumbnail'])) {
            $cate['thumbnail'] = cmf_asset_relative_url($cate['thumbnail']);
        }
        // FMW 
        // 如果gameId,second,third发生变化,pre_id,parent_id,fmw_path,path也会发生变化
        if ($valid == 'add' || $fmwId!=$fmw['parent_id']) {
            $fmw['pre_id']    = isset($oldPre['next_id']) ? $oldPre['next_id'] : 0;
            $fmw['parent_id'] = empty($fmwId) ? 0 : $fmwId;
            //这个next_id不一定有
            $fmw['next_id']   = isset($fmw['next_id']) ? $fmw['next_id'] : 0;
            // fmw_path,path获取的是当前或上级的
            if (empty($oldPre['fmw_path'])) {
                $fmw_path = '0';
                if (!empty($data['gameId'])) {
                    $fmw_path .= '-' . intval($data['gameId']);
                    if (!empty($data['second'])) {
                        $fmw_path .= '-' . intval($data['second']);
                        if (!empty($data['third'])) {
                            $fmw_path .= '-' . intval($data['third']);
                        }
                    }
                }
                $fmw['fmw_path'] = $fmw_path;
            } else {
               $fmw['fmw_path'] = $oldPre['fmw_path'];
            }
            // 
            // if (empty($fmw['path'])) {
            //     // 通过gameId,second,third获取
            // } else {
            //     $fmw['path'] = empty($oldPre['path'])?'':$oldPre['path'];
            // }
            $fmw['path'] = empty($oldPre['path'])?'0':$oldPre['path'];
            // $fmw['code'] = $code;
        }

        // dump($cate);
        // dump($fmw);
        // die;
        return [$cate, $fmw];
    }

    /*分类联动*/
    public function ajaxFirst()
    {
        $fmwId  = $this->request->get('q', 0, 'intval');
        $type   = $this->request->get('type', 0);
        $action = $this->request->get('action', '');
        $result = '';

        // 判断是否为末级
        if (is_numeric($fmwId)) {
            if ($fmwId != 0) {
                if ($action == 'index') {
                    $preId = $this->db->where('id', $fmwId)->value('next_id');
                    $num   = $this->md->checkChilds($preId);
                    if ($num) {
                        $result = $this->md->getNext(0, $fmwId, $type, false);
                    }
                } else {
                    $result = $this->md->getNext(0, $fmwId, $type, false);
                }
            }
        }

        return $result;
        // return json_encode($data);
        // return '[{"id":1,"text":"League of Legends"},{"id":2,"text":"World of Warcraft EU"},{"id":7,"text":"CrossFire"},{"id":9,"text":"CSGO"},{"id":11,"text":"Rocket League"}]';
    }

    /**
     * 名称，从库中多选，获取同级数据
     * @return [type] [description]
     */
    public function ajaxDooms()
    {
        $fmwId = $this->request->get('p', 0);
        $type  = $this->request->get('type', 0);
        $dooms = '';
        if (!empty($fmwId)) {
            $dooms = $this->md->getSiblings($fmwId, $type, ['从库中筛选', 0]);
        }
        return $dooms;
    }

    /**
     * 同步CODE码
     * @return [type] [description]
     */
    public function ajaxCode()
    {
        echo 'code';die;
    }

    /**
     * 通过fmwId获取子一级
     * @return [type] [description]
     */
    public function ajaxTable()
    {
        $fmwId  = $this->request->get('pid', 0);
        $type   = $this->request->get('type', 0);
        $subset = $this->md->getSubsetList($fmwId, $type);
// dump($subset);die;
        // return ['a','b'];
        return $subset;
    }
}
