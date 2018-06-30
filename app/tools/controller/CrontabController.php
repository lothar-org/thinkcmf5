<?php
namespace app\tools\controller;

use cmf\controller\BaseController;
use think\Db;

/**
 * 处理每日定时任务
 * 配置 \app\tools\controller\AdminCrontabController.php
 * Command.php
 */
class CrontabController extends BaseController
{
    public function task($set = [])
    {
        $this->resetEmail();
        $this->checkRoute();
    }

    /**
     * 邮箱每日已发送邮件数
     * 每日 0:00 重置
     * @param  string $mail [指定邮箱]
     * @return [type]       [description]
     */
    protected function resetEmail($mail = '')
    {

        c2c_log('重置 ' . $result . ' 条', 'resetEmail.log');
    }

    /**
     * 检查路由状况，保证完整性
     * 闲时检查
     * @return [type] [description]
     */
    protected function checkRoute()
    {
        cmf_log('更新 ' . $result . ' 条，删除 ' . $dnum . ' 条', 'checkRoute.log');
    }

    /**
     * 统计
     * 定时
     * @param  string $module [指定模块的]
     * @return [type]         [description]
     */
    protected function statistics($module='')
    {
        
    }

    /**
     * 统计每个游戏与类型的产品数量
     * 统计 上架/所有？ 产品数量
     * @param  array $w [指定条件]
     * @param  string $f [指定字段]
     * @return [type]    [description]
     */
    protected function catetype($w=[], $f='')
    {
        // $orders = Db::name('order')->where(['goods_id'=>['gt',0]])->count();
        // $orders = Db::name('goods')->where(['game_id'=>['>',0],'type_id'=>['>',0]])->count();

        // $find = Db::name('goods_catetype')-field('views,count,orders')->where(['status'=>1])->select();
    }

    /**
     * 游戏列表页标签自动生成
     * 闲时生成
     * 依据卖家发布产品标题进行分词？还是通过买家关键词搜索记录？
     * 暂时为每个游戏列表中账号类型下关键词筛选
     * @return [type] [description]
     */
    protected function hotTags($type=3)
    {
        $where = ['status'=>1];
        if (!empty($type)) {
            $where['type_id'] = $type;
        }
        $hots = Db::name('goods_hot_keyword')->field('game_id,type_id,name,url,count,is_rec')->where($where)->order('is_rec DESC')->select();
        // 文件缓存
        // cache();
    }
}
