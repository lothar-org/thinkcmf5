<?php
namespace app\goods\model;

use think\Db;
use think\Model;

class FmwModel extends Model
{
    protected $name = 'goods_category_fmw';
    // protected $pk = 'id';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 自定义
    protected $setField = 'a.id,a.pre_id,a.next_id,a.path,a.code,a.status,a.list_order,b.type,b.name,b.gindex,b.thumbnail,b.is_hot,b.unit';

    // 获取FMW列表数据
    public function getlist($filter = [], $order = '', $limit = '', $extra = '', $field = '')
    {
        $where = '1=1';
        // $where = 'a.status=1';

        // 针对多对多特殊处理
        $fmwId = getFmwId($filter)[0];
        /*if ($fmwId > 0) {
        $subQ = $this->alias('s')->field('s.next_id')->where('s.id', $fmwId)->buildSql();
        $where .= ' AND a.pre_id=' . $subQ;
        } else {
        $where .= ' AND a.pre_id=0';
        }*/
        //针对游戏无类别
        $type = empty($filter['type']) ? 0 : intval($filter['type']);
        $where .= ' AND ' . $this->exceptGame($fmwId, $type);

        if (!empty($filter['keyword'])) {
            $where .= ' AND b.name LIKE \'%' . $filter['keyword'] . '%\'';
        }
        if (!empty($extra)) {
            $where .= $extra;
        }
        // dump($filter);
        // dump($where);die;
        // 其它项
        if (empty($field)) {
            $field = $this->setField;
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
     * 通过fmwId获取一条
     * @param  integer $id    [description]
     * @param  string  $field [description]
     * @param  array   $extra [description]
     * @return [type]         [description]
     */
    public function getOne($id, $field = '', $extra = [])
    {
        $where = ['a.id' => $id];
        if (!empty($extra)) {
            $where = array_merge($where, $extra);
        }
        if (empty($field)) {
            $field = 'a.id,a.pre_id,a.next_id,a.parent_id,a.fmw_path,a.path,a.code,a.status,b.type,b.name,b.gindex,b.thumbnail,b.is_hot,b.unit,b.seo_title,b.seo_keywords,b.seo_description';
        }

        return $this->createOpt($where, $field, false)[0];
    }

    public function getSubsetList($fmwId, $type)
    {
        return $this->getNext(0, $fmwId, $type, false, $this->setField);
    }

    // 通过fmwId获取子一级
    public function getNext($curId = 0, $fmwId = 2, $type = 0, $option = '请选择', $field = 'a.id,b.name', $filter = [])
    {
        // $subQ = $this->field('next_id')->where('id', $fmwId)->buildSql();
        // $where  = 'a.pre_id=' . $subQ . ' AND b.type=' . $type;
        //针对游戏无类别
        $type  = empty($type) ? 0 : intval($type);
        $where = $this->exceptGame($fmwId, $type);

        $result = $this->createOpt($where, $field, $option, $curId);

        return $result;
    }
    // 通过path获取分类子一级
    public function getNext2($curId = 0, $preId = 0, $type = 0, $option = '请选择', $field = 'a.id,b.name', $filter = [])
    {
        // 联表查询
        /*$subset = $this->alias('a')
        ->field($field)
        ->join('__GOODS_CATEGORY__ b', 'a.next_id=b.id')
        ->where(['a.pre_id' => $preId, 'b.type' => $type])
        ->fetchSql(true)->select();*/
        // 结构：SELECT `b`.`id`,`b`.`name` FROM `s_goods_category_fmw` `a` INNER JOIN `s_goods_category` `b` ON `a`.`next_id`=`b`.`id` WHERE  `b`.`type` = 0  AND `a`.`pre_id` = 0
        // 分析：key有,key_len=3,ref=const|a.next_id,row小,Extra=Using where
        // dump($subset);die;

        // 子查询
        /*$subQ = Db::name('goods_category_fmw')->field('next_id')->where('pre_id',$preId)->buildSql();
        // $where = ['id'=>['IN',$subQ],'type'=>$type];
        $where = 'id IN '.$subQ.' AND type='.$type;
        $subSet = Db::name('goods_category')->field('id,name')->where($where)->fetchSql(true)->select();*/
        // 结构：SELECT `id`,`name` FROM `s_goods_category` WHERE  (  id IN ( SELECT `next_id` FROM `s_goods_category_fmw` WHERE  `pre_id` = 0 ) AND type=0 )
        // 分析：key=null|next_id,ref=func,row=max,Extra=Using where
        // dump($subset);die;

        $where = ['a.pre_id' => $preId];
        //针对游戏无类别
        if ($preId > 0) {
            $where['b.type'] = $type;
        }

        $result = $this->createOpt($where, $field, $option, $curId);

        return $result;
    }

    // 获取所有兄弟子集
    public function getSiblings($fmwId, $type = 0, $option = ['从库中筛选', 0])
    {
        // $preId = $this->field('pre_id')->where('id',$fmwId)->buildSql();
        $preId = $this->where('id', $fmwId)->value('pre_id');
        $kids  = $this->where('pre_id', $preId)->column('next_id');
        $t     = [];
        foreach ($kids as $col) {
            $temp = $this->where('pre_id', $col)->column('next_id');
            // array_push($t,$temp);
            $t = array_merge($t, $temp);
        }
        $t = array_unique($t);
        // $t = array_values($t);

        $where  = ['id' => ['in', $t], 'type' => $type];
        $subset = $this->name('goods_category')->field('id,name')->where($where)->select()->toArray();
        return c2c_get_options(0, $option, $subset);
    }

    // 通过fmwId获取所有子集
    public function getChilds($fmwId = 0, $type = 0, $level = 3)
    {
        $path = $this->where('id', $fmwId)->value('path');
        $arr  = explode('-', $path);
        $temp = [];
        foreach ($arr as $v) {
            $temp[] = $this->createOpt('a.pre_id=' . $v . ' AND b.type=' . $type, 'a.id,b.name', false);
        }

        return $temp;
    }

    // 根据pre_id检测是否还有子二级
    public function checkChilds($curId = 0)
    {
        // $subQ = $this->field('next_id')->where('id', $curId)->buildSql();
        // $subset   = $this->field('next_id')->where('pre_id=' . $subQ)->select();
        // dump($subset);die;

        $subset = $this->field('next_id')->where('pre_id', $curId)->select();
        // dump($subset->toArray());
        $num = 0;
        foreach ($subset as $row) {
            $num += $this->where('pre_id', $row['next_id'])->count();
        }

        return $num;
    }

    // 获取fmw上一级
    public function getPrev($fmwId = 0)
    {
        $preId = $this->where('id', $fmwId)->value('parent_id');
        return $preId;
    }
    // 获取fmw所有上级
    public function getParents($fmwId)
    {
        // $preId = $this->where('id',$fmwId)->value('parent_id');
        // if ($preId>0) {
        //     $preId = $this->getParents($preId) . '-' . $fmwId;
        // } else {
        //     $preId = '0'.$preId;
        // }

        // return $preId;
    }

    /**
     * 固定关系结构
     * @param  [type]  $where  [description]
     * @param  [type]  $field  [description]
     * @param  string  $option [description]
     * @param  integer $curId  [description]
     * @return [type]          [description]
     */
    public function createOpt($where, $field, $option = '', $curId = 0)
    {
        $subset = $this->alias('a')
            ->field($field)
            ->join('__GOODS_CATEGORY__ b', 'a.next_id=b.id')
            ->where($where)
        // ->fetchSql(true)->select();
            ->select()->toArray();
        // dump($subset);die;

        if ($option === false) {
            return $subset;
        }
        return c2c_get_options($curId, $option, $subset);
    }

    /**
     * 游戏除外，游戏不受类型限制
     * @param  [type] $fmwId [=0游戏,>0游戏之下]
     * @param  [type] $type  [类别：0游戏,1物品分类]
     * @return [type]        [description]
     */
    public function exceptGame($fmwId=0, $type=0)
    {
        if ($fmwId > 0) {
            $fmw   = $this->field('pre_id,next_id')->where('id', $fmwId)->find();
            $queue = 'a.pre_id=' . $fmw['next_id'] . ' AND b.type=' . $type;
        } else {
            $queue = 'a.pre_id=0';
        }

        return $queue;
    }



    /**
     * 添加数据
     * @param [type] $data [description]
     */
    public function addFmw($data)
    {
        // dump($data);
        // die;
        // return true;

        $cate  = $data[0];
        $fmw   = $data[1];
        $gc_db = Db::name('goods_category');
        foreach ($cate as $key => $value) {
            if (empty($value)) {
                unset($cate[$key]);
            }
        }
        // 已处理： pre_id,code
        // 要在这里处理的: next_id,parent_id,fmw_path,path

        $result = true;
        self::startTrans();
        try {
            // 处理 $cate
            if (empty($fmw['next_id'])) {
                $count = $gc_db->where('name', $cate['name'])->count();
                if ($count > 0) {
                    $cateId = $gc_db->where('name', $cate['name'])->value('id');
                    // if (empty($cateId)) {
                    //     throw new \Exception('数据丢失');
                    // }
                    $gc_db->where('id', $cateId)->update($cate);
                } else {
                    // $scModel->allowField(true)->save($cate);
                    // $cateId = $scModel->id;
                    // $gc_db->insert($cate);
                    // $cateId = $gc_db->getLastInsID();
                    $cateId = $gc_db->insertGetId($cate);
                }
                $fmw['next_id'] = $cateId;
            } else {
                $cateId = $fmw['next_id'];
                $gc_db->where('id', $fmw['next_id'])->update($cate);
            }

            // 处理 $fmw
            // next_id,fmw_path,path需要补充
            $fmw['path']     = $fmw['path'] . '-' . $cateId;
            $fmw_path        = $fmw['fmw_path'];
            $fmw['fmw_path'] = 'temporary';
            $this->allowField(true)->save($fmw);
            // $result=$this->isUpdate(false)->allowField(true)->data($fmw, true)->save();
            // 处理fmw_path
            $fmwId    = $this->id;
            $fmw_path = $fmw_path . '-' . $fmwId;
            $this->where('id', $fmwId)->update(['fmw_path' => $fmw_path]);

            c2c_admin_log('goods_category_fmw' . $fmwId, '新增类：' . $cate['name']);

            self::commit();
        } catch (\Exception $e) {
            self::rollback();
            $result = false;
        }

        return isset($fmwId) ? $fmwId : $result;
    }

    /**
     * 编辑数据
     * @param  [type] $data [description]
     * @param  [type] $fmwId   [description]
     * @return [type]       [description]
     */
    public function editFmw($data, $fmwId)
    {
        // dump($fmwId);
        // dump($data);die;
        $result = true;

        $cate  = $data[0];
        $fmw   = $data[1];
        $gc_db = Db::name('goods_category');
        foreach ($cate as $key => $value) {
            if (empty($value)) {
                unset($cate[$key]);
            }
        }
        // 已处理： pre_id,code
        // 要在这里处理的: next_id,parent_id,fmw_path,path

        $result = true;
        self::startTrans();
        try {
            // 处理 $cate
            // $gc_db->where('id',$cateId)->update($cate);


            // 处理 $fmw
            // $this->isUpdate(true)->allowField(true)->save($cate, ['id' => $fmwId]);
       

            // c2c_admin_log('goods_category_fmw' . $fmwId, '修改类：' . $cate['name']);

            self::commit();
        } catch (\Exception $e) {
            self::rollback();
            $result = false;
        }


        return $result;
    }
}
