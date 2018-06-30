<?php
namespace app\admin\controller;

use think\Db;
use cmf\controller\AdminBaseController;
use app\admin\model\SlideItemModel;

/**
 * 广告列表
 * 
 * 广告标题title
 * 广告位置ID：slide_id
 * 媒介类型(图、flash、代码、文字)：type
 * 广告链接url
 * 打开方式target：_blank,_self,
 * 开始日期start_time、结束日期end_time
 * ###点击数： hits
 * ###生成订单： orders
 * 状态status
 * ###排序list_order
 * 
 */
class SlideItemController extends AdminBaseController
{
    protected $targets = ["_blank" => "新标签页打开", "_self" => "本窗口打开"];
    /**
     * 广告列表
     * @adminMenu(
     *     'name'   => '广告列表',
     *     'parent' => 'admin/Slide/index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '广告列表',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $id      = $this->request->param('slide_id');
        $slideId = !empty($id) ? $id : 1;
        $result  = Db::name('slideItem')->field('id,title,url,target,description,status')->where(['slide_id' => $slideId])->select();

        $this->assign('slide_id', $id);
        $this->assign('targets', $this->targets);
        $this->assign('result', $result);
        return $this->fetch();
    }

    /**
     * 广告添加
     * @adminMenu(
     *     'name'   => '广告添加',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '广告添加',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        $slideId = $this->request->param('slide_id');

        $this->assign('slide_id', $slideId);
        $this->assign('targets', $this->targets);
        return $this->fetch();
    }

    /**
     * 广告添加提交
     * @adminMenu(
     *     'name'   => '广告添加提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '广告添加提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        $data = $this->request->param();
        $data['post']['more']['img'] = cmf_asset_relative_url($data['post']['more']['img']);
        $data['post']['more'] = json_encode($data['post']['more']);

        Db::name('slideItem')->insert($data['post']);
        $this->success("添加成功！", url("slideItem/index", ['slide_id' => $data['post']['slide_id']]));
    }

    /**
     * 广告编辑
     * @adminMenu(
     *     'name'   => '广告编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '广告编辑',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id     = $this->request->param('id');
        $result = Db::name('slideItem')->where(['id' => $id])->find();
        $data['post']['more'] = json_decode($data['post']['more'],true);

        $this->assign('slide_id', $result['slide_id']);
        $this->assign('result', $result);
        $this->assign('targets', $this->targets);
        return $this->fetch();
    }

    /**
     * 广告编辑
     * @adminMenu(
     *     'name'   => '广告编辑提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '广告编辑提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        $data = $this->request->param();

        $data['post']['more']['img'] = cmf_asset_relative_url($data['post']['more']['img']);
        $data['post']['more'] = json_encode($data['post']['more']);

        Db::name('slideItem')->update($data['post']);

        $this->success("保存成功！", url("SlideItem/index", ['slide_id' => $data['post']['slide_id']]));

    }

    /**
     * 广告删除
     * @adminMenu(
     *     'name'   => '广告删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '广告删除',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $id     = $this->request->param('id', 0, 'intval');

        $slideItem = Db::name('slideItem')->find($id);

        $result = Db::name('slideItem')->delete($id);
        if ($result) {
            //删除图片。
//            if (file_exists("./upload/".$slideItem['image'])){
//                @unlink("./upload/".$slideItem['image']);
//            }
            $this->success("删除成功！", url("SlideItem/index",["slide_id"=>$slideItem['slide_id']]));
        } else {
            $this->error('删除失败！');
        }

    }

    /**
     * 广告隐藏
     * @adminMenu(
     *     'name'   => '广告隐藏',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '广告隐藏',
     *     'param'  => ''
     * )
     */
    public function ban()
    {
        $id = $this->request->param('id', 0, 'intval');
        if ($id) {
            $rst = Db::name('slideItem')->where(['id' => $id])->update(['status' => 0]);
            if ($rst) {
                $this->success("幻灯片隐藏成功！");
            } else {
                $this->error('幻灯片隐藏失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    /**
     * 广告显示
     * @adminMenu(
     *     'name'   => '广告显示',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '广告显示',
     *     'param'  => ''
     * )
     */
    public function cancelBan()
    {
        $id = $this->request->param('id', 0, 'intval');
        if ($id) {
            $result = Db::name('slideItem')->where(['id' => $id])->update(['status' => 1]);
            if ($result) {
                $this->success("幻灯片启用成功！");
            } else {
                $this->error('幻灯片启用失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    /**
     * 广告排序
     * @adminMenu(
     *     'name'   => '广告排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '广告排序',
     *     'param'  => ''
     * )
     */
    public function listOrder()
    {
        $slideItemModel = new  SlideItemModel();
        parent::listOrders($slideItemModel);
        $this->success("排序更新成功！");
    }
}