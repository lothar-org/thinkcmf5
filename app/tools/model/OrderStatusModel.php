<?php
namespace  app\tools\model;
use think\Model;

/**
 * summary
 */
class OrderStatusModel extends Model
{
    // protected $createTime = 'addtime';//自定义字段名
    // protected $updateTime = 'last_time';
    // protected $updateTime = false;//关闭

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 时间字段取出后的默认时间格式
    // protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * 自动完成
     * @param [type] $value [description]
     */
    public function setOvertimeAttr($value)
    {
        return strtotime($value);
    }
    public function setAheadTimeAttr($value)
    {
        return strtotime($value);
    }
    public function getAheadTimeAttr($value)
    {
        return date('Y-m-d H:i:s',$value);
    }
    // public function setStatusAttr($value)
    // {
    //     return isset($value)?$value:0;;
    // }

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
        if (!empty($extra)) {
            $where = array_merge($where,$extra);
        }
        // 其它项
        $field = empty($field) ? '*' : $field;
        $join = [];
        $order = empty($order) ? 'list_order' : $order;
        $limit = empty($limit) ? config('pagerset')['size'] : $limit;

        $list = $this->alias('a')
            // ->distinct('status')
            ->field($field)
            // ->join($join)
            ->where($where)
            ->order($order)
            // ->fetchSql(true)->select();
            ->paginate($limit);
// dump($list);die;
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
            $result = $this->allowField(true)->save($data);
        } else {
            $result = $this->isUpdate(true)->allowField(true)->data($data, true)->save();
        }

        return $result;//1成功，0失败或无变化
    }

    /**
     * [获取状态数据]
     * @param string $status [状态值]
     */
    // public function getOrderStatus($status='')
    // {
    //     return c2c_get_status_set($status);
    // }
}