<?php
namespace app\portal\model;

use cmf\model\ComModel;

/**
 * summary
 */
class HelpModel extends ComModel
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    /**
     * 关联 user表
     * @return $this
     */
    // public function center()
    // {
    //     return $this->belongsTo('HelpCenterModel', 'cate_id')->setEagerlyType(1);
    // }

    /**
     * [getlist description]
     * @param  [type] $filter [description]
     * @return [type]         [description]
     */
    public function getlist($filter)
    {
        $field = 'a.*,b.name AS catename,u.user_login,u.user_nickname,u.user_email';

        $join = [
            ['__HELP_CENTER__ b', 'a.cate_id = b.id'],
            ['__USER__ u', 'a.user_id = u.id'],
        ];

        $where = [];
        $category = empty($filter['category']) ? 0 : intval($filter['category']);
        if (!empty($category)) {
            $where['a.cate_id'] = ['eq', $category];
        }
        
        $startTime = empty($filter['start_time']) ? 0 : strtotime($filter['start_time']);
        $endTime   = empty($filter['end_time']) ? 0 : strtotime($filter['end_time']);
        if (!empty($startTime) && !empty($endTime)) {
            $where['a.update_time'] = [['>= time', $startTime], ['<= time', $endTime]];
        } else {
            if (!empty($startTime)) {
                $where['a.update_time'] = ['>= time', $startTime];
            }
            if (!empty($endTime)) {
                $where['a.update_time'] = ['<= time', $endTime];
            }
        }

        $keyword = empty($filter['keyword']) ? '' : $filter['keyword'];
        if (!empty($keyword)) {
            $where['a.title'] = ['like', "%$keyword%"];
        }

        $list = $this->alias('a')->field($field)
            ->join($join)
            ->where($where)
            ->order('update_time', 'DESC')
            ->paginate();

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
        if (empty($id)) {
            $result = $this->allowField(true)->save($data);
        } else {
            $result = $this->isUpdate(true)->allowField(true)->save($data,['id'=>$id]);
        }

        return $result;
    }
}
