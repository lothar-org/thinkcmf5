<?php
namespace cmf\model;

use think\Model;

/**
 * 共用 模型类
 */
class ComModel extends Model
{
    /**
     * 新增数据
     * pk='id'
     * 资源链接处理 cmf_asset_relative_url()
     * @param [array]  $data  [要保存的数据]
     * @param boolean $is_obj [是否返回对象]
     */
    public function addDataCom($data, $is_obj = false)
    {
        $result = $this->allowField(true)->save($data);
        // $result = $this->isUpdate(false)->allowField(true)->data($data, true)->save();
        // $insid = $this->id;
        // $insid = $this->insertGetId($data);
        // $result = $this->insert($cate);
        // $insid = $this->getLastInsID();

        return ($is_obj === true) ? $this : $result;
    }

    /**
     * 修改数据
     * pk='id'
     * 资源链接处理 cmf_asset_relative_url()
     * @param  [array]  $data  [要更新的数据]
     * @param  boolean $is_obj [是否返回对象$this]
     * @return [mix]           [description]
     */
    public function editDataCom($data, $is_obj = false)
    {
        $result = $this->isUpdate(true)->allowField(true)->data($data, true)->save();
        // $id = intval($data['id']);
        // $result = $this->isUpdate(true)->allowField(true)-->save($data,['id'=>$id]);
        // $result = $this->where('id',$id)->update($data);
        
        // $result: 1成功，0失败或无变化
        return ($is_obj === true) ? $this : $result;
    }

    /**
     * 后台管理编辑显示页面
     * $post = $this->where('id',$id)->find()->toArray();//find()结果集为空时返回NULL，toArray()报错
     * $post = $this->where('id',$id)->select()->toArray();//select()为空时返回空对象，toArray()为空数组
     * @param int $id 唯一ID
     * @return $post 获取一条数据
     */
    public function getPost($id)
    {
        $post = $this->get($id);
        $post = empty($post)?[]:$post->toArray();
        // $post = $post->find()->toArray();

        return $post;
    }

    function moreModel()
    {

        $transStatus = true;
        Db::startTrans();
        try{
            # ……
            # throw new \Exception('msg');
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $transStatus = false;
            // throw $e;
            // echo $e->getMessage();
        }
    }
}
