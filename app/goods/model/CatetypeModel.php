<?php
namespace app\goods\model;

use think\Db;
use think\Model;
// use app\admin\model\RouteModel;
use think\Route;

class CatetypeModel extends Model
{
    protected $name = 'goods_catetype';
    // 开启自动写入时间戳字段
    // protected $autoWriteTimestamp = true;

    /**
     * 关联 goods_type表
     * @return $this
     */
    // public function types()
    // {
    //     return $this->belongsTo('UserModel', 'user_id')->setEagerlyType(1);
    // }

    /**
     * 关联 goods_category表
     */
    // public function categories()
    // {
    //     return $this->belongsToMany('PortalCategoryModel', 'portal_category_post', 'category_id', 'post_id');
    // }

    /**
     * 关联 标签表
     */
    // public function tags()
    // {
    //     return $this->belongsToMany('PortalTagModel', 'portal_tag_post', 'tag_id', 'post_id');
    // }

    /**
     * content 自动转化
     * @param $value
     * @return string
     */
    public function getContentAttr($value)
    {
        return cmf_replace_content_file_url(htmlspecialchars_decode($value));
    }
    public function setContentAttr($value)
    {
        return htmlspecialchars(cmf_replace_content_file_url(htmlspecialchars_decode($value), true));
    }
    public function getTradeInfoAttr($value = '')
    {
        return $this->getContentAttr($value);
    }
    public function setTradeInfoAttr($value = '')
    {
        return $this->setContentAttr($value);
    }

    /**
     * 获取分页列表数据
     * @param  array  $filter [description]
     * @param  string $order  [description]
     * @param  string $limit  [description]
     * @param  array  $extra  [description]
     * @param  string $field  [description]
     * @return [type]         [description]
     */
    public function getlist($filter = [], $order = '', $limit = '', $extra = [], $field = '')
    {
        // 筛选条件
        $where   = [];
        $keyword = isset($filter['keyword']) ? $filter['keyword'] : '';
        if (!empty($keyword)) {
            $where['code|title'] = ['like', "%$keyword%"];
        }
        if (!empty($extra)) {
            $where = array_merge($where, $extra);
        }
        // 其它项
        $field = empty($field) ? 'a.*,b.name type_name,d.name AS game_name' : $field;
        $join  = [
            ['__GOODS_TYPE__ b', 'a.type_id=b.id', 'LEFT'],
            ['goods_category_fmw c', 'a.game_id=c.id', 'LEFT'],
            ['goods_category d', 'c.next_id=d.id'],
        ];
        $order = empty($order) ? 'views DESC' : $order;
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

    public function getOne($id)
    {
        $find = [];

        return $find;
    }

    public function handleGo($data, $id)
    {

    }

    public function addCatetype($data, $return_id = true)
    {
        $transStatus = true;
        Db::startTrans();
        try {
            $result = $this->isUpdate(false)->allowField(true)->data($data, true)->save();
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $transStatus = false;
        }

        // 路由处理
        $route = new Route;
        $code  = $data['code'];

        return $return_id === true ? $this->id : $result;
    }

    public function editCatetype($data, $is_obj = false)
    {
        $result = $this->isUpdate(true)->allowField(true)->data($data, true)->save();

        // 路由处理
        $route = new Route;
        $code  = $data['code'];

        return ($is_obj === true) ? $this : $result;
    }

}
