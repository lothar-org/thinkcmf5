<?php
// namespace app\goods\model;

// use app\admin\model\RouteModel;
// use think\Route;
use think\Db;
use think\Model;
use tree\Tree;

class GoodsCategoryModel extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    protected $field = 'a.id,a.level,a.type,a.code,a.name,a.gindex,a.thumbnail,a.unit,a.is_hot,a.status,a.list_order';

    public function getlist($filter = [], $order = '', $limit = '', $extra = [], $field = '')
    {
        $where = [
            'a.delete_time' => 0,
            'a.type'        => 0,
        ];
        if (!empty($filter['type'])) {
            $where['a.type'] = $filter['type'];
        }
        $gameId = empty($filter['gameId'])?0:intval($filter['gameId']);
        if ($gameId == 0) {
            $where['a.level'] = 1;
        } else {
            $where['b.pre_id'] = intval($gameId);
        }
        $second = empty($filter['second'])?0:intval($filter['second']);
        if (!empty($second)) {
            $where['b.pre_id'] = $second;
        }
        $third = empty($filter['third'])?0:intval($filter['third']);
        if (!empty($third)) {
            $where['b.pre_id'] = $third;
        }
        if (!empty($filter['keyword'])) {
            $where['a.name'] = ['like', '%' . $filter['keyword'] . '%'];
        }
        if (!empty($extra)) {
            $where = array_merge($where, $extra);
        }
        // dump($filter);
        // dump($where);die;
        // 其它项
        $field = empty($field) ? $this->field : $field;
        $join  = [['__GOODS_CATEGORY_FMW__ b', 'a.id=b.next_id']];
        $order = empty($order) ? 'a.list_order' : $order;
        $limit = empty($limit) ? config('pagerset')['size'] : $limit;

        $list = $this->alias('a')
        // ->distinct('status')
            ->field($field)
            ->join($join)
            ->where($where)
            ->order($order)
        // ->fetchSql(true)->select();
            ->paginate($limit);
// dump($list);die;

        // 使用层级结构
        // $tree = new Tree;
        // $list = $tree->getGoodsTreeArray($myId, 0, 1);

        return $list;
    }

    /**
     * [getGoodsTreeArray description]
     * ->fetchSql(true)->select();
     * @param  integer $myId     [description]
     * @param  integer $type     [description]
     * @return [type]            [description]
     */
    public function getGoodsTreeArray($myId = 0, $filter = [])
    {
        $returnArray = [];
        $subset      = $this->getChild($myId, $filter, $this->field);
        if (is_array($subset)) {
            foreach ($subset as $child) {
                $returnArray[$child['id']]             = $child;
                $returnArray[$child['id']]['children'] = $this->getGoodsTreeArray($child['id'], $filter);
            }
        }
        // return $returnArray;
        return array_values($returnArray);
    }

    public function getChild($myId, $filter = [], $field = 'a.id,a.name')
    {
        $where = [
            'a.delete_time' => 0,
            'a.type'        => 0,
            'b.pre_id'      => $myId,
        ];
        if (!empty($filter['type'])) {
            $where['a.type'] = intval($filter['type']);
        }

        //获取一级数组
        $subset = $this->alias('a')
            ->field($field)
            ->join('goods_category_fmw b', 'a.id=b.next_id')
            ->where($where)
            ->select()->toArray();
        return $subset;
    }

    /**
     * 获取单一级别
     * 获取多级请参照 categoryTree()
     * @param  integer $selectId  被选ID
     * @param  integer $level     级别
     * @param  string  $option    ['全部', 0]
     * @param  array   $condition ['level'=>1]
     * @param  array   $kv        ['id', 'text']
     * @return [type]
     */
    public function getFirstCate($selectId = 0, $option = ['全部', 0], $condition = ['level' => 1])
    {
        $where = [
            'delete_time' => 0,
        ];
        if (!empty($condition)) {
            $where = array_merge($where, $condition);
        }

        $data    = $this->field('id,name')->where($where)->select()->toArray();
        $options = c2c_get_options($selectId, $option, $data);

        return $options;
    }

    /**
     * 生成分类 select树形结构
     * @param int $selectId 需要选中的分类 id
     * @param int $currentCid 需要隐藏的分类 id
     * @return string
     */
    public function categoryTree($selectId = 0, $currentCid = 0)
    {
        $where = ['delete_time' => 0];
        if (!empty($currentCid)) {
            $where['id'] = ['neq', $currentCid];
        }
        // $categories = $this->all()->toArray();
        $categories = $this->order('list_order ASC')->where($where)->select()->toArray();

        $tree       = new Tree();
        $tree->icon = ['&nbsp;&nbsp;│', '&nbsp;&nbsp;├─', '&nbsp;&nbsp;└─'];
        $tree->nbsp = '&nbsp;&nbsp;';

        $newCategories = [];
        foreach ($categories as $item) {
            $item['selected'] = ($selectId == $item['id']) ? 'selected' : '';
            array_push($newCategories, $item);
        }

        $tree->init($newCategories);
        $str     = '<option value=\"{$id}\" {$selected}>{$spacer}{$name}</option>';
        $treeStr = $tree->getTree(0, $str);

        return $treeStr;
    }

    /**
     * [addCategory 添加产品分类]
     * @param [type] $data [description]
     * @return int
     */
    public function addCategory($data)
    {
        // dump($data);die;
        $mq     = Db::name('goods_category_fmw');
        $result = true;
        self::startTrans();
        try {
            if (!empty($data['thumbnail'])) {
                $data['thumbnail'] = cmf_asset_relative_url($data['thumbnail']);
            }
            if (empty($data['next_id'])) {
                $this->allowField(true)->save($data);
                $id = $this->id;
            } else {
                $id = $data['next_id'];
            }
            if ($data['pre_id'] > 0) {
                $prePath = $mq->where('pre_id', $data['pre_id'])->value('path');
                $mq->insert(['pre_id' => $data['pre_id'], 'next_id' => $id, 'path' => $prePath . '-' . $id]);
            } else {
                $mq->insert(['next_id' => $id, 'path' => '0-' . $id]);
            }
            // 路由

            self::commit();
        } catch (\Exception $e) {
            self::rollback();
            $result = false;
        }
        return isset($id) ? $id : $result;
    }

    /**
     * [editCategory 编辑产品分类]
     * level 如何改变？
     * @param  array  $data [description]
     * @return bool         [description]
     */
    public function editCategory($data)
    {
        $result = true;

        $id    = intval($data['id']);
        $preId = intval($data['pre_id']);
        $mq    = Db::name('goods_category_fmw');

        // 举例：将 0-2-3 以及子集 0-2-3-4、0-2-3-5 移动到 0-7
        $oldPath = $mq->where('next_id',$id)->value('path');//0-2-3 => 0-7-3

        // 对当前的path进行改变
        if ($preId==0) {
            $newPath = '0-'.$id;//作为一级分类时
        } else {
            $prePath = $mq->where('next_id',$data['pre_id'])->value('path');
            // $preCategory = $this->alias('a')->field('a.level,b.path')->join('goods_category_fmw b','a.id=b.next_id')->where('b.next_id',$data['pre_id'])->find();
            if ($prePath===false) {
                $newPath = false;
            } else {
                $newPath = $prePath.'-'.$id;//归到某个上级
            }
        }
        if (empty($oldPath) || empty($newPath)) {
            $result = false;
        } else {
            // $data['level'] = $preCategory['level']+1;
            if (!empty($data['thumbnail'])) {
                $data['thumbnail'] = cmf_asset_relative_url($data['thumbnail']);
            }
            $this->isUpdate(true)->allowField(true)->save($data, ['id' => $id]);
            $mq->where('next_id',$id)->update(['path'=>$newPath]);

            $subset = $mq->field('next_id,path')->where('path', 'like', $oldPath . '-%')->select();// 0-2-3-4 => 0-7-3-4, 0-2-3-5 => 0-7-3-5
            if (!$subset->isEmpty()) {
                foreach ($subset as $child) {
                    $childPath = str_replace($oldPath . '-', $newPath . '-', $child['path']);
                    $mq->where('next_id', $child['next_id'])->update(['path' => $childPath]);
                }
            }
            // code也要改变
            // 路由

        }

        return $result;
    }
}
