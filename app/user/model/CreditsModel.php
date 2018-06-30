<?php
namespace app\user\model;

use think\Model;

class CreditsModel extends Model
{
    // protected $pk = 'id';
    // protected $table = 's_order_status';
    protected $name = 'credits_range';

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
        $where = [];

        // 积分区间 start_range、end_range
        $start = empty($filter['start']) ? 0 : $filter['start'];
        $end   = empty($filter['end']) ? 0 : $filter['end'];
        if (!empty($start) && !empty($end)) {
            $where['start_range'] = ['EGT', $start];
            $where['end_range'] = ['elt', $end];
        } else {
            if (!empty($start)) $where['start_range'] = ['>=', $start];
            if (!empty($end)) $where['end_range'] = ['<=', $end];
        }
        // $keyword = isset($filter['keyword'])?$filter['keyword']:'';
        // if ($keyword) {
        //     $where['name'] = ['like',"%$keyword%"];
        // }
        if (!empty($filter['keyword'])) {
            $where['name'] = ['like','%'.$filter['keyword'].'%'];
        }

        if (!empty($extra)) {
            $where = array_merge($where,$extra);
        }
        // 其它项
        $field = empty($field) ? '*' : $field;
        $join = [];
        $order = empty($order) ? 'start_range' : $order;
        $limit = empty($limit) ? config('pagerset')['size'] : $limit;

        $list = $this->field($field)->where($where)->order($order)->paginate($limit);

        return $list;
    }

    /**
     * 新增 或 更新 操作
     * @param  [type]  $data [description]
     * @param  integer $id   [description]
     * @return [type]        [description]
     */
    public function handleGo($data,$id=0)
    {
        if (!empty($data['icon'])) {
            $data['icon'] = cmf_asset_relative_url($data['icon']);
        }

        if (empty($id)) {
            // $insid = $this->db->insertGetId($data);
            $result = $this->allowField(true)->data($data, true)->isUpdate(false)->save();
        } else {
            // $insid = $this->db->where('id',$id)->update($data);
            $result = $this->allowField(true)->isUpdate(true)->data($data, true)->save();
        }

        return $result;//1成功，0失败或无变化
        // return $this->id;
    }



}