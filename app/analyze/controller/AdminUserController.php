<?php
namespace app\analyze\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use statistics\CountUsers;

class AdminUserController extends AdminBaseController
{
    protected $types = [1 => '买家', 2 => '卖家'];
    public function _initialize()
    {
        parent::_initialize();

        $this->db = Db::name('member');
        // $this->db2 = Db::name('user');
        $this->assign('flag','用户统计'); 
    }

    /**
     * 用户统计
     * \app\user\controller\AdminIndexController.php
     * @adminMenu(
     *     'name'   => '用户统计',
     *     'parent' => 'analyze/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 3,
     *     'icon'   => '',
     *     'remark' => '用户统计',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        // 用于筛选的字段: 
        // status,type,level,source
        // reg_time,login_time,
        // balance,orders,reviews,positives,negatives,rating
        // email,mobile,verifys

        $filter = $this->request->param();

        $where = [];
        // 单值查询: 状态,用户类型,级别,来源
        $check = ['status','type','level','source'];
        foreach ($check as $row) {
            if (!empty($filter[$row])) {
                $where[$row] = $$row = trim($filter[$row]);
            } else {
                $$row = '';
            }
        }
        // 关键词 这里是 OR条件，另做处理
        $keywordComplex = [];
        if (!empty($filter['keyword'])) {
            $keyword = trim($filter['keyword']);
            $keywordComplex['email|mobile|verifys'] = ['like', "%$keyword%"];
        }
        // 时间查询: 注册时间,最后登录时间
        $check = ['reg_time','login_time'];
        foreach ($check as $row) {
            $start = empty($filter['start_'.$row]) ? 0 : strtotime($filter['start_'.$row]);
            $end   = empty($filter['end_'.$row]) ? 0 : strtotime($filter['end_'.$row]);
            if ($start > $end) {
                $tmp = $start; $start = $end; $end = $tmp;
            }
            if (!empty($start) && !empty($end)) {
                $where[$row] = [['>= time', $start], ['<= time', $end]];
            } else {
                if (!empty($start)) {
                    $where[$row] = ['>= time', $start];
                }
                if (!empty($end)) {
                    $where[$row] = ['<= time', $end];
                }
            }
        }
        // 区间查询: 账户余额,成功订单数,总评数,好评数,差评数,信用积分
        $check = ['balance','orders','reviews','positives','negatives','rating'];
        foreach ($check as $row) {
            $start = empty($filter['start_'.$row]) ? 0 : intval($filter['start_'.$row]);
            $end = empty($filter['end_'.$row]) ? 0 : intval($filter['end_'.$row]);
            if (!empty($start) && !empty($end)) {
                $where[$row] = [['>=', $start], ['<=', $end]];
            } else {
                if (!empty($start)) $where[$row] = ['egt', $start];
                if (!empty($end)) $where[$row] = ['elt', $end];
            }
        }
// dump($where);die;

        $set = [
            'x' => ['key'=>'reg_time','value'=>[@$filter['start_reg_time'],@$filter['end_reg_time']]],
            'y' => []
        ];

        $uc = new CountUsers($set);
        // dump($uc->look($where));
        // dump($uc->years($where));
        // dump($uc->months($where));
        // dump($uc->monthsByYear($where));
        // dump($uc->days($where));
        // dump($uc->daysByMonth($where));
        // dump($uc->hours($where));
// die;


        // 根据注册时间的区间，判断采用何种方式处理，显示图表
        // 按年、按月、按日、按时
        $chart = $uc->look($where);
        if ($chart['error']!=0) {
            $this->error($chart['msg'],url('index'));
        }
        $chart = $chart['data'];
        // dump($chart);
        // echo json_encode($chart);
        // $this->assign('chart',$chart);
        // $this->assign('x',$chart[0]);
        // $this->assign('y',$chart[1]);
        $this->assign('x',json_encode($chart[0]));
        $this->assign('y',json_encode($chart[1]));


        // 统计每天的新增用户。 获取横轴、纵轴数据，但纵轴有可能是多组数据。
        // 例如：不同网站下相同时间段的注册人数
        // $data = $this->db->field('FROM_UNIXTIME(reg_time,'%Y-%m') as x,count(*) as y')->where($where)->group('reg_time')->fetchSql(false)->select();
        // dump($data);die;
        // echo $data;die;



        $levelOrg = [1=>1,2=>2,3=>3];
        $sourceOrg = ['Windows','Linux','ubuntu','Mac','Android','iOS','symbian','BlackBerry','YunOS'];

        $this->assign('statusV',c2c_get_status_set($status,'member_status','请选择'));
        $this->assign('types',c2c_get_status_set($type,$this->types,'请选择'));
        $this->assign('levels',c2c_get_status_set($level,$levelOrg,'请选择'));
        $this->assign('sources',c2c_get_status_set($source,$sourceOrg,'请选择'));
        $this->assign('keyword_show','邮箱/手机号/认证序列');

        return $this->fetch();
    }
}
