<?php
namespace app\portal\model;

// use app\admin\model\RouteModel;
use think\Model;
use tree\Tree;

/**
 * summary
 */
class HelpCenterModel extends Model
{
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
        $categories = $this->all()->toArray();
        // $categories = $this->order('list_order ASC')->where($where)->select()->toArray();

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

        $pid = $filter['parentId'];
        if ($pid > 0) {
            $path          = $this->where('id', $pid)->value('path');
            $where['path'] = ['like', $path . '-%'];
        }

        $categories = $this->field('id,parent_id,name,description,post_count,status,list_order')->order('list_order')->where($where)->select()->toArray();

        $tree       = new Tree();
        $tree->icon = ['&nbsp;&nbsp;│', '&nbsp;&nbsp;├─', '&nbsp;&nbsp;└─'];
        $tree->nbsp = '&nbsp;&nbsp;';

        if (!is_array($currentIds)) {
            $currentIds = [$currentIds];
        }

        $newCategories = [];
        foreach ($categories as $item) {
            $item['checked']    = in_array($item['id'], $currentIds) ? "checked" : "";
            $item['status']     = $item['status'] == 0 ? '<a data-toggle="tooltip" title="未发布"><i style="color:#F00" class="fa fa-close"></i></a>' : '<a data-toggle="tooltip" title="已发布"><i class="fa fa-check"></i></a>';
            $item['str_action'] =
                '<a href="' . url("add", ["parent" => $item['id']]) . '">添加子项</a> &nbsp; &nbsp;' .
                '<a href="' . url("edit", ["id" => $item['id']]) . '">' . lang('EDIT') . '</a> &nbsp; &nbsp;' .
                '<a href="' . url("delete", ["id" => $item['id']]) . '" class="js-ajax-delete">' . lang('DELETE') . '</a> &nbsp; &nbsp;';
            array_push($newCategories, $item);
        }

        $tree->init($newCategories);

        if (empty($tpl)) {
            $tpl = "<tr>
                        <td><input name='list_orders[\$id]' type='text' size='3' value='\$list_order' class='input-order'></td>
                        <td>\$id</td>
                        <td>\$spacer \$name</td>
                        <td>\$description</td>
                        <td>\$post_count</td>
                        <td>\$status</td>
                        <td>\$str_action</td>
                    </tr>";
        }
        $treeStr = $tree->getTree($pid, $tpl);

        return $treeStr;
    }

    /**
     * 获取一级分类
     * 获取多级请参照 categoryTree()
     * @param  integer $selectId  [description]
     * @param  integer $parentId  [description]
     * @param  string  $option    [description]
     * @param  array   $condition [description]
     * @return [type]             [description]
     */
    public function getFirstCate($selectId = 0, $parentId = 0, $option = ['全部', 0], $condition = [])
    {
        $where = [
            'delete_time' => 0,
            'parent_id'   => $parentId,
        ];
        if (!empty($condition)) {
            $where = array_merge($where, $condition);
        }
        $data    = $this->field('id,name')->where($where)->select()->toArray();
        $options = c2c_get_options($selectId, $option, $data);
        return $options;
    }

    /**
     * [addCategory 添加帮助分类]
     * @param [type] $data [description]
     */
    public function addCategory($data)
    {
        // dump($data);die;
        $result = true;
        self::startTrans();
        try {
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
     * [editCategory 编辑帮助分类]
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
            $this->isUpdate(true)->allowField(true)->save($data, ['id' => $id]);

            $children = $this->field('id,path')->where('path', 'like', $oldCategory['path'] . "-%")->select();
            if (!$children->isEmpty()) {
                foreach ($children as $child) {
                    $childPath = str_replace($oldCategory['path'] . '-', $newPath . '-', $child['path']);
                    $this->where('id', $child['id'])->update(['path' => $childPath], ['id' => $child['id']]);
                }
            }
        }

        return $result;
    }

}
