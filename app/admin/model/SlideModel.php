<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use think\Model;

class SlideModel extends Model
{
    /**
     * 获取分页列表数据
     * @param  array  $filter [description]
     * @param  string $order  [description]
     * @param  string $limit  [description]
     * @param  array  $extra  [description]
     * @param  string $field  [description]
     * @return [type]         [description]
     */
    public function getlist($filter=[], $order='', $limit='', $extra=[], $field='')
    {
        // 筛选条件
        $where = ['delete_time' => 0, 'parent_id' => 2];

        if (isset($filter['parent'])) {
            $where['parent_id'] = intval($filter['parent']);
        }
        $keyword = isset($filter['keyword']) ? $filter['keyword'] : '';
        if (!empty($keyword)) {
            $where['name'] = ['like', "%$keyword%"];
        }
        if (!empty($extra)) {
            $where = array_merge($where,$extra);
        }
        // 其它项
        $field = empty($field) ? '*' : $field;
        $order = empty($order) ? '' : $order;
        $limit = empty($limit) ? config('pagerset')['size'] : $limit;

        $list = $this->alias('a')
            ->field($field)
            ->where($where)
            ->order($order)
            // ->fetchSql(true)->select();
            ->paginate($limit);
// dump($list);die;
        return $list;
    }

    /**
     * 获取单一级别
     * @param  integer $selectId  [description]
     * @param  integer $parentId  [description]
     * @param  string  $option    [description]
     * @param  array   $condition [description]
     * @return [type]             [description]
     */
    public function getFirstCate($selectId = 0, $parentId = 0, $option = ['请选择','0'], $condition = [])
    {
        $where = [
            'parent_id'   => $parentId,
            // 'status' => 1,
        ];
        if (!empty($condition)) {
            $where = array_merge($where, $condition);
        }

        $data = $this->field('id,name')->where($where)->select()->toArray();

        $options = c2c_get_options($selectId, $option, $data);
        return $options;
    }
}