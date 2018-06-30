<?php
namespace app\user\controller;

use app\goods\model\GoodsCommentModel;
use cmf\controller\UserBaseController;

class CommentController extends UserBaseController
{
    /**
     * 个人中心我的评论列表
     */
    public function index()
    {
        // $user   = cmf_get_current_user();
        // $userId = cmf_get_current_user_id();

        // $commentModel = new GoodsCommentModel();
        // $list         = $commentModel->where(['user_id' => $userId, 'delete_time' => 0])
        //     ->order('create_time DESC')->paginate();

        // $this->assign($user);
        // $this->assign("list", $list);
        // $this->assign("pager", $list->render());
        return $this->fetch();
    }
}
