<?php
namespace app\goods\api;
// use think\Db;
use app\goods\model\FmwModel;

class GameApi
{
    /**
     * 游戏列表 用于模板设计
     * @param array $param
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function index($param = [])
    {
        return [];
    }

    /**
     * 游戏列表 用于导航选择
     * @return array
     */
    public function nav()
    {
        $fmwModel = new FmwModel();

        $categories = $fmwModel->getNext2(0,0,0,false);
        // dump($categories);die;
        $return = [
            //'name'  => '热门游戏',
            'rule'  => [
                'action' => 'goods/List/index',//这里如何应用url美化？
                'param'  => [
                    'id' => 'id'
                ]
            ],//url规则
            'items' => $categories //每个子项item里必须包括id,name,如果想表示层级关系请加上 parent_id
        ];

        return $return;
    }
}
