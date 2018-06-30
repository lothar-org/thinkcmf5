<?php
// namespace app\goods\model;

// use app\admin\model\RouteModel;
use think\Model;

/**
 * summary
 */
class FmwModel extends Model
{
    protected $name = 'goods_category_fmw';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 自定义

    public function getlist($filter = [], $order = '', $limit = '', $extra = [], $field = '')
    {
        $where = [
            'a.status' => 1,
        ];
        if (!empty($filter['type'])) {
            $where['b.type'] = intval($filter['type']);
        } else {
            $where['b.type'] = 0;
        }
        $gameId            = empty($filter['gameId']) ? 0 : intval($filter['gameId']);
        $where['a.pre_id'] = $gameId;
        $second            = empty($filter['second']) ? 0 : intval($filter['second']);
        if (!empty($second)) {
            $where['a.pre_id'] = $second;
        }
        $third = empty($filter['third']) ? 0 : intval($filter['third']);
        if (!empty($third)) {
            $where['a.pre_id'] = $third;
        }
        if (!empty($filter['keyword'])) {
            $where['b.name'] = ['like', '%' . $filter['keyword'] . '%'];
        }
        if (!empty($extra)) {
            $where = array_merge($where, $extra);
        }
        // dump($filter);
        // dump($where);die;
        // 其它项
        if (empty($field)) {
            $field = 'a.id,a.pre_id,a.next_id,a.path,a.code,a.status,a.list_order,b.type,b.name,b.gindex,b.thumbnail,b.is_hot,b.unit';
        }

        $join  = [['__GOODS_CATEGORY__ b', 'a.next_id=b.id']];
        $order = empty($order) ? 'a.list_order' : $order;
        $limit = empty($limit) ? config('pagerset')['size'] : $limit;

        $list = $this->alias('a')
            ->field($field)
            ->join($join)
            ->where($where)
            ->order($order)
        // ->fetchSql(true)->select();
            ->paginate($limit);
// dump($list);die;

        return $list;
    }

    /**
     * [getOne 获取一条]
     * @param  integer $id    [description]
     * @param  string  $field [description]
     * @param  array   $extra [description]
     * @return [type]         [description]
     */
    public function getOne($id, $field = '', $extra = [])
    {
        $where = ['a.id'=>$id];
        if (!empty($extra)) {
            $where = array_merge($where, $extra);
        }
        if (empty($field)) {
            $field = 'a.id,a.pre_id,a.next_id,a.path,a.code,a.status,b.type,b.name,b.gindex,b.thumbnail,b.is_hot,b.unit,b.seo_title,b.seo_keywords,b.seo_description';
        }

        $find = $this->alias('a')
            ->field($field)
            ->join('__GOODS_CATEGORY__ b', 'a.next_id=b.id')
            ->where($where)
            // ->fetchSql(true)->find();
            ->find()->toArray();
// dump($find);die;
        return $find;
    }

    /**
     * [getNext 获取子一集]
     * @param  integer $curId  [description]
     * @param  integer $preId  [description]
     * @param  integer $type   [description]
     * @param  string  $option [description]
     * @param  string  $field  [description]
     * @param  array   $filter [description]
     * @return mix          [description]
     */
    public function getNext($curId = 0, $preId = 0, $type = 0, $option = '请选择', $field = 'a.id,b.name', $filter = [])
    {
        // 联表查询
        $where = [
            'a.pre_id' => $preId,
            'b.type'   => $type,
        ];
        $subset = $this->alias('a')
            ->field($field)
            ->join('__GOODS_CATEGORY__ b', 'a.next_id=b.id')
            ->where($where)
        // ->fetchSql(true)->select();
            ->select()->toArray();
        // 结构：SELECT `b`.`id`,`b`.`name` FROM `s_goods_category_fmw` `a` INNER JOIN `s_goods_category` `b` ON `a`.`next_id`=`b`.`id` WHERE  `b`.`type` = 0  AND `a`.`pre_id` = 0
        // 分析：key有,key_len=3,ref=const|a.next_id,row小,Extra=Using where
        // dump($subset);die;

        // 子查询
        /*$subQ = Db::name('goods_category_fmw')->field('next_id')->where('pre_id',$preId)->buildSql();
        // $where = ['id'=>['IN',$subQ],'type'=>$type];
        $where = 'id IN '.$subQ.' AND type='.$type;
        $subset = Db::name('goods_category')->field('id,name')->where($where)
        // ->fetchSql(true)->select();
        ->select()->toArray();*/
        // 结构：SELECT `id`,`name` FROM `s_goods_category` WHERE  (  id IN ( SELECT `next_id` FROM `s_goods_category_fmw` WHERE  `pre_id` = 0 ) AND type=0 )
        // 分析：key=null|next_id,ref=func,row=max,Extra=Using where
        // dump($subset);die;

        $result = c2c_get_options($curId, $option, $subset);
        return $result;
    }

    /**
     * [getPrev 获取上一级]
     * @param  integer $curId [description]
     * @param  string  $path  [description]
     * @param  integer $type  [description]
     * @return [type]         [description]
     */
    public function getPrev($curId = 0, $path = '', $type = 0)
    {

    }

    // 获取所有子集
    public function getChilds($preId = 0, $type = 0, $level = 3)
    {

    }
    // 这个暂时用不到
    public function getParents()
    {

    }

    // 检测是否还有子二级
    public function checkChilds($curId = 0)
    {
        $sub = $this->field('next_id')->where('pre_id', $curId)->select();
        $num = 0;
        foreach ($sub as $row) {
            $num += $this->where('pre_id', $row['next_id'])->count();
        }
        return $num;
    }

    public function addFmw($data)
    {

        return isset($id) ? $id : $result;
    }
    public function editFmw($data, $id)
    {
        $result = true;

        return $result;
    }
}
