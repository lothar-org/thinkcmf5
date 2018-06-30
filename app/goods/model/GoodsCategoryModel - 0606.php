<?php
// namespace app\goods\model;

// use app\admin\model\RouteModel;
use think\Model;
use tree\Tree;

class GoodsCategoryModel extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    public function getlist($filter)
    {

        $list = '';
        // $tree = new Tree;
        // $list = $tree->getGoodsTreeArray($myId, 0, 1);
    }

    /**
     * [getGoodsTreeArray description]
     * ->fetchSql(true)->select();
     * @param  integer $myId     [description]
     * @param  integer $type     [description]
     * @return [type]            [description]
     */
    public function getGoodsTreeArray($myId=0,$filter=[])
    {
        $returnArray = [];
        $field='a.id,a.level,a.type,a.code,a.name,a.gindex,a.thumbnail,a.unit,a.is_hot,a.status,a.list_order';
        $subset = $this->getChild($myId,$filter,$field);
        if (is_array($subset)) {
            foreach ($subset as $child) {
                $returnArray[$child['id']] = $child;
                $returnArray[$child['id']]['children'] = $this->getGoodsTreeArray($child['id'],$filter);
            }
        }
        // return $returnArray;
        return array_values($returnArray);
    }

    public function getGoodsTreeArray2($myId = 0, $type = 0, $maxLevel = 0, $level = 1)
    {
        $returnArray = [];
        //获取一级数组
        $subset = $this->alias('a')
            ->field('a.id,a.level,a.type,a.code,a.name,a.gindex,a.thumbnail,a.unit,a.is_hot,a.status,a.list_order')
            ->join('__GOODS_CATEGORY_FMW__ b', 'a.id=b.next_id')
            ->where(['type' => $type, 'b.pre_id' => $myId])
            ->select()->toArray();
        if (is_array($subset)) {
            foreach ($subset as $child) {
                $child['_level']           = $level;
                $returnArray[$child['id']] = $child;
                if ($maxLevel === 0 || ($maxLevel !== 0 && $maxLevel > $level)) {
                    $mLevel                                = $level + 1;
                    $returnArray[$child['id']]["children"] = $this->getGoodsTreeArray2($child['id'], $maxLevel, $mLevel);
                }
            }
        }
        return $returnArray;
    }
    
    public function getChild($myId, $filter=[], $field='a.id,a.name')
    {
        $where = ['b.pre_id'=>$myId,'a.delete_time'=>0];
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
     * @param  array   $condition ['id', 'text']
     * @return [type]
     */
    public function getFirstCate($selectId = 0, $level = 0, $option = ['全部', 0], $condition = [])
    {
        $where = [
            'delete_time' => 0,
            'level'   => $level
        ];
        if (!empty($condition)) {
            $where = array_merge($where, $condition);
        }

        $data = $this->field('id,name')->where($where)->select()->toArray();

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
     * 列表分类树
     * 使用 path 而不是 递归方法 查找
     * @param int|array $currentIds
     * @param int|array $currentIds
     * @param string $tpl
     * @return string
     */
    public function categoryTableTree($filter = [], $currentIds = 0, $tpl = '')
    {
        $where = ['delete_time' => 0];
        // if (!empty($currentCid)) {
        //     $where['id'] = ['neq', $currentCid];
        // }

        $pid = $filter['category'];
        if ($pid > 0) {
            $path          = $this->where('id', $pid)->value('path');
            $where['path'] = ['like', $path . '-%'];
        } else {
            // $where['parent_id'] = 0;
        }
        // dump($where);die;
        if (!empty($filter['type'])) {
            $where['type'] = intval($filter['type']);
        }

        if (!empty($filter['keyword'])) {
            $where['name|code'] = ['like', "%$filter[keyword]%"];
        }
        // $where = ['id'=>1];

        $categories = $this->field('id,parent_id,type,code,name,gindex,thumbnail,unit,list_order')
            ->order('list_order ASC')
            ->where($where)
            // ->limit(6)
            // ->fetchSql(true)->select();
            ->select()->toArray();

        $tree       = new Tree();
        $tree->icon = ['&nbsp;&nbsp;│', '&nbsp;&nbsp;├─', '&nbsp;&nbsp;└─'];
        $tree->nbsp = '&nbsp;&nbsp;';

        if (!is_array($currentIds)) {
            $currentIds = [$currentIds];
        }

        $newCategories = [];
        foreach ($categories as $item) {
            $item['checked']    = in_array($item['id'], $currentIds) ? "checked" : "";
            $item['thumbnail']  = '<a href="javascript:parent.imagePreviewDialog(\'' . cmf_get_image_preview_url($item['thumbnail']) . '\');"><i class="fa fa-photo fa-fw"></i></a>';
            $item['type']       = $item['parent_id'] > 0 ? ($item['type'] == 0 ? '游戏' : '物品') : '▉▉▉';
            $item['str_action'] =
                '<a href="' . url("add", ["parent" => $item['id']]) . '">添加子项</a> &nbsp; &nbsp;' .
                '<a href="' . url("edit", ["id" => $item['id']]) . '">' . lang('EDIT') . '</a> &nbsp; &nbsp;' .
                '<a href="' . url("delete", ["id" => $item['id']]) . '" class="js-ajax-delete">' . lang('DELETE') . '</a> &nbsp; &nbsp;' .
                ($item['parent_id'] == 0 ? '<a target="_blank" href="' . url('AdminCatetype/index', ['gameId' => $item['id']]) . '">' . lang('VIEW') . '关联</a>' : '');
            array_push($newCategories, $item);
        }

        $tree->init($newCategories);

        if (empty($tpl)) {
            $tpl = "<tr>
                        <td><input name='list_orders[\$id]' type='text' size='3' value='\$list_order' class='input-order'></td>
                        <td>\$id</td>
                        <td>\$type</td>
                        <td>\$spacer \$name</td>
                        <td>\$code</td>
                        <td>\$gindex</td>
                        <td>\$unit</td>
                        <td>\$thumbnail</td>
                        <td>\$str_action</td>
                    </tr>";
        }
        $treeStr = $tree->getTree($pid, $tpl);

        return $treeStr;
    }

    /**
     * [addCategory 添加产品分类]
     * @param [type] $data [description]
     */
    public function addCategory($data)
    {
        // dump($data);die;
        $result = true;
        self::startTrans();
        try {
            if (!empty($data['thumbnail'])) {
                $data['thumbnail'] = cmf_asset_relative_url($data['thumbnail']);
            }
            $this->allowField(true)->save($data);
            $id = $this->id;
            if (empty($data['parent_id'])) {
                $this->where(['id' => $id])->update(['path' => '0-' . $id]);
            } else {
                $parentPath = $this->where('id', intval($data['parent_id']))->value('path');
                $this->where(['id' => $id])->update(['path' => "$parentPath-$id"]);
            }
            self::commit();
        } catch (\Exception $e) {
            self::rollback();
            $result = false;
        }
        return isset($id) ? $id : $result;
    }

    /**
     * [editCategory 编辑产品分类]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editCategory($data)
    {
        $result = true;

        $id          = intval($data['id']);
        $parentId    = intval($data['parent_id']);
        $oldCategory = $this->where('id', $id)->find();

        if (empty($parentId)) {
            $newPath = '0-' . $id;
        } else {
            $parentPath = $this->where('id', intval($data['parent_id']))->value('path');
            if ($parentPath === false) {
                $newPath = false;
            } else {
                $newPath = "$parentPath-$id";
            }
        }

        if (empty($oldCategory) || empty($newPath)) {
            $result = false;
        } else {
            $data['path'] = $newPath;
            if (!empty($data['thumbnail'])) {
                $data['thumbnail'] = cmf_asset_relative_url($data['thumbnail']);
            }
            $this->isUpdate(true)->allowField(true)->save($data, ['id' => $id]);

            $subset = $this->field('id,path')->where('path', 'like', $oldCategory['path'] . "-%")->select();
            if (!$subset->isEmpty()) {
                foreach ($subset as $child) {
                    $childPath = str_replace($oldCategory['path'] . '-', $newPath . '-', $child['path']);
                    $this->where('id', $child['id'])->update(['path' => $childPath], ['id' => $child['id']]);
                }
            }
        }

        return $result;
    }
}
