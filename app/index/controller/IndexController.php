<?php 
namespace app\index\controller;
use app\admin\model\RouteModel;
// use think\Route;
use think\Db;
use cmf\controller\BaseController;


/**
 * summary
 */
class IndexController extends BaseController
{
    private $ak;
    public function _initialize()
    {
        parent::_initialize();
        $this->code = 'orz';
    }

    public function index()
    {
        echo 'index';
        exit();
    }

    function test()
    {
        echo 'test';
        
    }
    function simulate()
    {
        $data = [];
        for ($n = 0; $n < 2000; $n++) {
            $data[] = [
                'parent_id'=>rand(0,3),
                'path'=>cmf_random_string(rand(6,15)),
                'code'=>cmf_random_string(rand(6,15)),
                'name'=>cmf_random_string().cmf_get_order_sn(),
                'gindex'=>cmf_random_string(1),
                'unit'=>cmf_random_string(1),
                'status'=>rand(0,1),
                'list_order'=>$n
            ];
        }
        $res = Db::name('goods_category')->insertAll($data);
        $this->success('成功'.$res.'条',url('simulateview'));
    }
    function simulateview()
    {
        return $this->display('<a href="'.url('simulate').'">再次生成</a>');
    }
    function goods_category_fmw($value='')
    {

        $data = [
            ['pre_id'=>0,'next_id'=>1],
            ['pre_id'=>0,'next_id'=>2],
            ['pre_id'=>0,'next_id'=>7],
            ['pre_id'=>0,'next_id'=>9],
            ['pre_id'=>0,'next_id'=>11],
            ['pre_id'=>2,'next_id'=>3],
            ['pre_id'=>2,'next_id'=>6],
            ['pre_id'=>2,'next_id'=>16],
            ['pre_id'=>3,'next_id'=>4],
            ['pre_id'=>3,'next_id'=>5],
            ['pre_id'=>7,'next_id'=>8],
            ['pre_id'=>9,'next_id'=>10],
            ['pre_id'=>11,'next_id'=>12],
            ['pre_id'=>11,'next_id'=>13],
            ['pre_id'=>11,'next_id'=>14],
            ['pre_id'=>11,'next_id'=>15],
        ];
        // $res=Db::name('goods_category_fmw')->insertAll($data);
        // echo $res;
    }

    function blog()
    {
        echo 'blog';
        
    }

    function news()
    {
        echo 'news';
        
    }

    function route5()
    {
        
    }

    function route()
    {
        $routeModel = new RouteModel();
        $alias      = $routeModel->getUrl('portal/List/index', ['id' => $id]);
    }

    function routeAdd()
    {
        $alias = $this->code;
        //设置别名
        $routeModel = new RouteModel();
        if (!empty($alias) && !empty($id)) {
            $routeModel->setRoute($alias, 'portal/List/index', ['id' => $id], 2, 5000);
            $routeModel->setRoute($alias . '/:id', 'portal/Article/index', ['cid' => $id], 2, 4999);
        }
        $routeModel->getRoutes(true);
    }

    function routeEdit($value='')
    {
        $alias = $this->code;
        $routeModel = new RouteModel();
        if (!empty($alias)) {
            $routeModel->setRoute($alias, 'portal/List/index', ['id' => $data['id']], 2, 5000);
            $routeModel->setRoute($alias . '/:id', 'portal/Article/index', ['cid' => $data['id']], 2, 4999);
        } else {
            $routeModel->deleteRoute('portal/List/index', ['id' => $data['id']]);
            $routeModel->deleteRoute('portal/Article/index', ['cid' => $data['id']]);
        }

        $routeModel->getRoutes(true);
    }
}
