<?php
namespace app\order\controller;

use app\order\model\OrderModel;
use cmf\controller\AdminBaseController;
use think\Db;

/**
 * Class AdminIndexController
 * 订单列表
 * 添加订单
 * 发货单
 * 退货单
 * 订单日志
 *
 * 高级查询
 * 合并订单
 * 订单打印
 * 缺货登记
 * 库存日志
 * 
 * @package app\order\controller
 * @adminMenuRoot(
 *     'name'   =>'订单管理',
 *     'action' =>'default',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 20,
 *     'icon'   =>'shopping-cart',
 *     'remark' =>'订单管理'
 * )
 */
class AdminIndexController extends AdminBaseController
{
    protected $name = 'order';
    protected $flag = '订单列表';
    // private $db;
    public function _initialize()
    {
        parent::_initialize();

        // dump(get_browser(null, true));
        // $browser = new \Browser();
        // $device  = $browser->getPlatform();
        // $browser = $browser->getBrowser();
        // dump($device);
        // dump($browser);
        // die;

        $this->db = Db::name($this->name);
        $this->md = new OrderModel;
        $this->assign('flag', $this->flag);
    }

    /**
     * 订单列表
     * @adminMenu(
     *     'name'   => '订单列表',
     *     'parent' => 'order/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 1,
     *     'icon'   => '',
     *     'remark' => '订单列表',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $param = $this->request->param();
        $payment = $this->request->param('payment','');
        $status = $this->request->param('status',0);

        $where = [];
        // 下单日期
        $start = empty($param['start']) ? 0 : strtotime($param['start']);
        $end   = empty($param['end']) ? 0 : strtotime($param['end']);
        if (!empty($start) && !empty($end)) {
            $where['a.create_time'] = [['>= time', $start], ['<= time', $end]];
        } else {
            if (!empty($start)) {
                $where['a.create_time'] = ['>= time', $start];
            }
            if (!empty($end)) {
                $where['a.create_time'] = ['<= time', $end];
            }
        }
        // 收货人
        // $consignee = empty($param['consignee']) ? '' : $param['consignee'];
        // if (!empty($consignee)) {
        //     $where['b.username'] = ['like', "%$consignee%"];
        // }
        // 订单号
        $order_sn = empty($param['order_sn']) ? '' : $param['order_sn'];
        if (!empty($order_sn)) {
            $where['a.order_sn'] = $order_sn;
        }
        // 支付方式
        if (!empty($param['payment'])) {
            $where['a.payment'] = intval($param['payment']);
        }
        // 状态
        if (!empty($param['status'])) {
            $where['a.status'] = intval($param['status']);
        }

        $join = [];

        $list = $this->db->alias('a')
            ->field('*')
            ->join($join)
            ->where($where)
            ->order('a.create_time DESC')
            ->paginate();
// dump($list);

        $payments = Db::name('payment')->field('code,name')->select()->toArray();
        $payments = c2c_get_options($payment, '', $payments, ['code', 'name']);


        $this->assign('payment',$payment);
        $this->assign('payments',$payments);
        $this->assign('status',$status);
        $this->assign('statusV',config('order_status'));
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     *
     * @adminMenu(
     *     'name'   => '添加',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        $this->assign('statusV', c2c_get_status_set());
        return $this->fetch();
    }

    /**
     *
     * @adminMenu(
     *     'name'   => '添加提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        // $order = $this->request->param('order/a');
        // $info = $this->request->param('info/a');

        // $result = true;
        // Db::startTrans();
        // try {
        //     $insid = $this->db->insertGetId($order);
        //     Db::name('goods_info')->insert($info);
        //     Db::commit();
        // } catch (\Exception $e) {
        //     Db::rollback();
        //     $result = false;
        // }

        $data = $this->opp();
        $data['create_time'] = time();
        $bros = new \Browser();
        $data['device'] = $bros->getPlatform();
        $data['browser'] = $bros->getBrowser();
        // $data['version'] = $bros->getVersion();
        $insid = $this->md->addDataCom($data);

        if (empty($insid)) {
            $this->error('添加失败');
        }
        $log = [
            'order_id'  => $insid,
            'order_status'  => $data['status'],
            'deal_id'  => cmf_get_current_admin_id(),
            'description'=>'添加订单',
        ];
        c2c_order_log($log);
        // c2c_admin_log($this->name .':'. $insid, '添加' . $this->flag . '：' . $data['order_sn']);
        $this->success('添加成功', url('index'));
        // $this->success('添加成功', url('edit', ['id' => $insid]));
    }

    /**
     *
     * @adminMenu(
     *     'name'   => '编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');
        if (empty($id)) {
            $this->error('非法操作');
        }

        $order = $this->md->get($id);
        $order = empty($order) ? $this->error('数据不存在') : $order->toArray();

        $this->assign('statusV', c2c_get_status_set($order['status']));
        $this->assign('order',$order);
        return $this->fetch();
    }

    /**
     *
     * @adminMenu(
     *     'name'   => '编辑提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        $id = $this->request->param('order.id', 0, 'intval');
        if (empty($id)) {
            $this->error('数据非法!');
        }
        // $order = $this->request->param('order/a');
        // $info = $this->request->param('info/a');

        // $result = true;
        // Db::startTrans();
        // try {
        //     $insid = $this->db->insertGetId($order);
        //     Db::name('goods_info')->insert($info);
        //     Db::commit();
        // } catch (\Exception $e) {
        //     Db::rollback();
        //     $result = false;
        // }

        $data = $this->opp();
        $result = $this->md->editDataCom($data, $id);

        if (empty($result)) {
            $this->error('编辑失败 或 数据无变化');
        }
        $log = [
            'order_id'  => $id,
            'order_status'  => $data['status'],
            'deal_id'  => cmf_get_current_admin_id(),
            'description'=>'修改订单',
        ];
        c2c_order_log($log);
        // c2c_admin_log($this->name .':'. $id, '修改' . $this->flag . '：' . $data['order_sn']);
        $this->success('编辑成功', url('index'));
    }

    /**
     * 删除，启用回收站
     * @adminMenu(
     *     'name'   => '删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $id     = $this->request->param('id', 0, 'intval');
        $name   = $this->db->where('id', $id)->value('order_sn');

        $result = true;
        try {
            $this->db->where('id', $id)->delete();
            $log = [
                'order_id'  => $id,
                'order_status'  => $data['status'],
                'deal_id'  => cmf_get_current_admin_id(),
                'description'=>'删除订单',
            ];
            c2c_recycle_bin($this->name, $id, $name);
            c2c_order_log($log);
            // c2c_admin_log($this->name .':'. $id, '删除' . $this->flag . '：' . $name);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $result = false;
        }

        if ($result) {
            $this->success(lang('DELETE_SUCCESS'));
        } else {
            $this->error(lang('DELETE_FAILED'));
        }
    }

    /**
     * [opp 共用]
     * @param  string $valid [description]
     * @param  array  $data  [description]
     * @return [type]        [description]
     */
    public function opp($valid = 'add')
    {
        $data = $this->request->param();
        // $data = $this->request->param('order/a');

        // $result = $this->validate($data, 'Order.' . $valid);
        // if ($result !== true) {
        //     $this->error($result);
        // }

        dump($data);die;
        return $data;
    }
}
