<?php
namespace app\admin\controller;

use app\admin\model\SlideModel;
use cmf\controller\AdminBaseController;
use think\Db;

/**
 * 广告位置
 *
 * 父级parent_id
 * 广告位名称name
 * 宽度width、高度height
 * ###结构
 * 描述
 * 状态
 * 
 * (关联游戏)
 * 
 */
class SlideController extends AdminBaseController
{

    /**
     * 广告位置列表
     * @adminMenu(
     *     'name'   => '广告位置',
     *     'parent' => 'admin/Setting/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 40,
     *     'icon'   => '',
     *     'remark' => '广告位置管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $param = $this->request->param();
        $slideId = $this->request->param('slideId/d',0);
        $parentId = isset($param['parent'])?intval($param['parent']):2;
        $spModel = new SlideModel();


        if ($slideId>0) {
            $slide = Db::name('slide')->where('id',$slideId)->find();
            if ($slide['delete_time']>0) {
                $this->error('此广告在回收站中');
            }
            $parentId = $slide['parent_id'];
            $this->assign('list', [$slide]);
        } else {
            $slides = $spModel->getlist($param);
            $slides->appends($param);
            $this->assign('list', $slides->items());
            $this->assign('pager', $slides->render());
        }

        $cates   = $spModel->getFirstCate($parentId);

        $this->assign('cates', $cates);
        return $this->fetch();
    }

    /**
     * 添加广告位置
     * @adminMenu(
     *     'name'   => '添加广告位置',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加广告位置',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        $spModel = new SlideModel();
        $cates   = $spModel->getFirstCate(2);
        $this->assign('cates', $cates);
        return $this->fetch();
    }

    /**
     * 添加广告位置提交
     * @adminMenu(
     *     'name'   => '添加广告位置提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加广告位置提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        $data    = $this->request->param();
        $spModel = new SlideModel();
        $result  = $spModel->validate(true)->save($data);
        if ($result === false) {
            $this->error($spModel->getError());
        }
        $this->success("添加成功！", url("slide/index"));
    }

    /**
     * 编辑广告位置
     * @adminMenu(
     *     'name'   => '编辑广告位置',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑广告位置',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id      = $this->request->param('id');
        $spModel = new SlideModel();
        $slide  = $spModel->where('id', $id)->find();
        $cates   = $spModel->getFirstCate($slide['parent_id']);
        $this->assign('cates', $cates);
        $this->assign('result', $slide);
        return $this->fetch();
    }

    /**
     * 编辑广告位置提交
     * @adminMenu(
     *     'name'   => '编辑广告位置提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑广告位置提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        $data    = $this->request->param();
        $spModel = new SlideModel();
        $result  = $spModel->validate(true)->save($data, ['id' => $data['id']]);
        if ($result === false) {
            $this->error($spModel->getError());
        }
        $this->success("保存成功！", url("slide/index"));
    }

    /**
     * 删除广告位置
     * @adminMenu(
     *     'name'   => '删除广告位置',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除广告位置',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $id      = $this->request->param('id', 0, 'intval');
        $spModel = new SlideModel();
        $result  = $spModel->where(['id' => $id])->find();
        if (empty($result)) {
            $this->error('广告位置不存在!');
        }

        //如果存在页面。则不能删除。
        $slidePostCount = Db::name('slide_item')->where('slide_id', $id)->count();
        if ($slidePostCount > 0) {
            $this->error('此广告位置有广告无法删除!');
        }

        $resultSlide = $spModel->save(['delete_time' => time()], ['id' => $id]);
        if ($resultSlide) {
            c2c_recycle_bin('slide', $id, $result['name']);
        }
        $this->success("删除成功！", url("slide/index"));
    }
}
