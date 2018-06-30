<?php
namespace statistics;

use think\Db;
use think\helper\Time;

/**
 * 报表统计 - 数据分析
 * 用户统计 - 注册时间为时间戳
 * select count(1) from table_name group by date_format(date,'%y-%m-%d');
 */
class CountUsers
{
    public function __construct($option = [])
    {
        $this->xSet = $option['x'];
        $this->ySet = $option['y'];
    }

    /**
     * 自定义
     * 每日的数据
     * x轴：reg_time
     * y轴：注册人数。 后期可设为变量
     * @param  array $x 横轴配置
     * @param  array $y 纵轴配置
     * @param  array $condition 条件
     * @return [type]
     */
    public function look($condition = [], $x = '', $y = '')
    {
        // dump($condition);die;
        $xSet = $x ? $x : $this->xSet;
        $ySet = $y ? $y : $this->ySet;
        if (empty($xSet['key'])) {
            return ['error' => 1, 'msg' => 'no key'];
        }

        // x 轴数据，作为 x 轴标注
        $xAxis    = array();
        $new_time = 0;
        if (empty($xSet['value'])) {
            $j          = date('t'); //获取当前月份天数
            $start_time = strtotime(date('Y-m-01')); //获取本月第一天时间戳
            $cur_time   = time();
            $timestamp  = $cur_time - $start_time;

            for ($i = 0; $i < $j; $i++) {
                if ($timestamp > $new_time) {
                    $xAxis[] = date('Y-m-d', ($start_time + $new_time)); //每隔一天赋值给数组
                } else {
                    break;
                }
                $new_time += 86400;
            }
        } else {
            $tv = $xSet['value'];
            $startTime = empty($tv[0]) ? date('Y-m-01') : $tv[0];
            $startTime = strtotime($startTime);
            $endTime   = empty($tv[1]) ? time() : strtotime($tv[1]);
            if ($startTime > $endTime) {
                $tmp       = $startTime;
                $startTime = $endTime;
                $endTime   = $tmp;
            }
            $days = Time::diff_day($startTime, $endTime);
            if ($days) {
                for ($i = 0; $i <= $days; $i++) {
                    $xAxis[] = date('Y-m-d', $startTime + $new_time);
                    $new_time += 86400;
                }
            } else {
                return ['error' => 1, 'msg' => 'no x-axis'];
            }
        }

        // 获取原始数据
        $orginData = Db::name('member')->field("FROM_UNIXTIME(reg_time,'%Y-%m-%d') days,count(*) count")->where($condition)->group('days')->select()->toArray();

        //处理获取到的数据
        $ySemData = array();
        if (!empty($orginData)) {
            foreach ($xAxis as $k => $v) {
                foreach ($orginData as $kk => $vv) {
                    if ($v == $vv['days']) {
                        $ySemData[$k] = $vv['count'];
                        break;
                    } else {
                        $ySemData[$k] = 0;
                        continue;
                    }
                }
            }
        } else {
            foreach ($xAxis as $k => $v) {
                $ySemData[$k] = 0;
            }
        }

        return ['error' => 0, 'data' => [$xAxis, $ySemData]];
    }

    /**
     * 统计每年的注册用户
     * @param  string $condition [description]
     * @return [type]            [description]
     */
    public function years($condition = '')
    {
        return [];
    }

    public function months($condition = '')
    {
        return [];
    }

    /**
     * 统计一年内每月的注册用户
     * 一年的时间戳: 31556926 秒,1年3千万多秒
     * @param  string $condition [description]
     * @return [type]            [description]
     */
    public function monthsByYear($condition = '')
    {
        //获取原始数据。 为0的月份是没有数据的
        $orgin = Db::query("SELECT DATE_FORMAT(FROM_UNIXTIME(reg_time),'%Y%m') AS month,count(*) AS num FROM s_member WHERE `reg_time` BETWEEN UNIX_TIMESTAMP(now()) - 31556926 AND UNIX_TIMESTAMP(now()) GROUP BY month");

        // 填充数据
        $today = date('m');
        $res   = [];
        foreach ($orgin as $val) {
            $res[$val['month']] = $val['num'];
        }
        $ext = date('Y');
        for ($i = 1; $i < 13; $i++) {
            if ($today < 10) {
                if ($i < 10 && $i > $today) {
                    $key = ($ext - 1) . '0';
                } elseif ($i >= 10) {
                    $key = $ext - 1;
                } else {
                    $key = $ext . '0';
                }
                if (!array_key_exists($key . $i, $res)) {
                    $res[$key . $i] = 0;
                }
            } else {
                if ($i > $today) {
                    $key = $ext - 1;
                } elseif ($i >= 10 && $i <= $today) {
                    $key = $ext;
                } else {
                    $key = $ext . '0';
                }
                if (!array_key_exists($key . $i, $res)) {
                    $res[$key . $i] = 0;
                }
            }
        }
        asort($res);

        return $res;
    }

    /**
     * 每日注册人数
     * @param  array $condition 条件
     * @return [type]        [description]
     */
    public function days($condition = [])
    {

        return [];
    }

    /**
     * 统计本月每日注册的人数
     * 一个月的时间戳: 不定,依据当月而定
     * @param  string $condition [description]
     * @return [type]            [description]
     */
    public function daysByMonth($condition = '')
    {
        $month  = date('Y-m', time());
        $prefix = config('database.prefix');

        // 连贯操作
        $semRes = Db::name('member')
            ->field("FROM_UNIXTIME(reg_time,'%Y-%m-%d') days,count(id) count")
            ->where($condition)->where("FROM_UNIXTIME(reg_time,'%Y-%m') = '{$month}'")
            ->group('days')
        // ->fetchSql(true)->select();
            ->select()->toArray();
        // 原生查询
        // $semRes = Db::query("SELECT FROM_UNIXTIME(reg_time,'%Y-%m-%d') AS days,COUNT(id) AS count from " . $prefix . "member WHERE FROM_UNIXTIME(reg_time,'%Y-%m') = '" . $month . "' group by days");
        // $seoRes = Db::query("SELECT FROM_UNIXTIME(reg_time,'%Y-%m-%d') days,count(*) count FROM
        // dump($semRes);die;

        // x 轴数据，作为 x 轴标注
        $j          = date("t"); //获取当前月份天数
        $start_time = strtotime(date('Y-m-01')); //获取本月第一天时间戳
        $xAxis      = array();
        for ($i = 0; $i < $j; $i++) {
            $xAxis[] = date('Y-m-d', $start_time + $i * 86400); //每隔一天赋值给数组
        }
        //处理获取到的数据
        $ySemData = array();
        if (!empty($semRes)) {
            foreach ($xAxis as $k => $v) {
                foreach ($semRes as $kk => $vv) {
                    if ($v == $vv['days']) {
                        $ySemData[$k] = $vv['count'];
                        break;
                    } else {
                        $ySemData[$k] = 0;
                        continue;
                    }
                }
            }
        } else {
            foreach ($xAxis as $k => $v) {
                $ySemData[$k] = 0;
            }
        }

        return [$xAxis, $ySemData];
    }

    /**
     * 统计每小时人流量
     * @param  string $condition [description]
     * @return [type]            [description]
     */
    public function hours($condition = '')
    {
        return 'hours';
    }
}
