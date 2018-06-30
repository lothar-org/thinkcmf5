<?php
// 系统定义的  \simplewind\cmf\common.php
// 自定义方法  针对不同模块的还可以在每个模块里定义所需方法

// use think\Config;
// use email\PHPMailer5;
// use think\Url;
// use dir\Dir;
// use think\Route;
// use think\Loader;
// use think\Request;
// use cmf\lib\Storage;
use think\Db;
use think\Excel;
use think\Image;

/**
 * 系统日志
 * cmf_log()
 */
function c2c_log($content, $filename = 'test.log')
{
    error_log(date('Y-m-d H:i:s') . $content . PHP_EOL, 3, 'log/' . $filename);
}

/**
 * 管理员日志
 * 关键位置操作记录
 */
function c2c_admin_log($obj = '', $remark = '', $action = '')
{
    $request  = request();
    $adminLog = [
        'admin_id'    => cmf_get_current_admin_id(),
        'object'      => $obj,
        'remark'      => $remark,
        'create_time' => time(),
        'ip'          => get_client_ip(), //get_client_ip(0, true)
    ];

    if (empty($action)) {
        $module             = $request->module(); //Request::module()
        $controller         = $request->controller();
        $action             = $request->action();
        $adminLog['action'] = strtolower($module . '/' . $controller . '/' . $action);
    } else {
        $adminLog['action'] = $action;
    }

    Db::name('admin_log')->insert($adminLog);
}

/**
 * 订单日志
 * 把订单操作记录单独拿出来
 * @param  string $data [order_id,order_status,deal_id,user_type,create_time,ip,description]
 * ### $data为数组时必须是完整数据，这里不再做验证
 * @return [type]       [description]
 */
function c2c_order_log($data = '', $status = '', $description = '', $deal_id = '')
{
    if (!is_array($data)) {
        $data = [
            'order_id'     => $data,
            'order_status' => $status,
            'description'  => $description,
        ];
        if (empty($deal_id)) {
            $uid = cmf_get_current_user_id();
            if (empty($uid)) {
                $data['deal_id'] = cmf_get_current_admin_id();
            } else {
                $data['deal_id'] = $uid;
            }
        }
    }
    $data['create_time'] = time();
    $data['ip']          = get_client_ip();

    Db::name('order_log')->insert($data);//insertGetId()
}

/**
 * [c2c_visit_log 访问记录]
 * https://github.com/cbschuld/Browser.php
 * @param  string $type   [用户类型：1:admin;2:会员]
 * @param  string $uid    [用户id]
 * @param  string $object [访问对象的id,格式:不带前缀的表名+id;如posts1表示xx_posts表里id为1的记录]
 * @param  string $action [操作名称;格式:应用名+控制器+操作名,也可自己定义格式只要不发生冲突且惟一;]
 * @return [type]         [description]
 */
function c2c_visit_log($type = '2', $uid = '', $object = '', $action = '')
{
    // 获取设备和浏览器标识
    $bros = new \Browser();
    // if( $bros->getBrowser() == \Browser::BROWSER_FIREFOX && $bros->getVersion() >= 2 ) {
    //     echo 'You have FireFox version 2 or greater';
    // } else {
    //     echo 'ok<br>';
    // }
    // dump($bros->reset());
    // echo 'isBrowser<br>';dump($bros->isBrowser('aaaa'));
    // echo 'getBrowser<br>';dump($bros->getBrowser());
    // echo 'getPlatform<br>';dump($bros->getPlatform());
    // echo 'getVersion<br>';dump($bros->getVersion());
    // echo 'isMobile<br>';dump($bros->isMobile());
    // echo 'getUserAgent<br>';dump($bros->getUserAgent());
    // echo 'isAol<br>';dump($bros->isAol());
    // echo 'getAolVersion<br>';dump($bros->getAolVersion());
    // exit();
    $device  = $bros->getPlatform();
    $browser = $bros->getBrowser();
    $version = $bros->getVersion();

    $uid      = empty($uid) ? ($type == 1 ? cmf_get_current_admin_id() : cmf_get_current_user_id()) : $uid;
    $visitLog = [
        'user_type' => $type,
        'user_id'   => $uid,
        'object'    => $object,
        'last_time' => time(),
        'ip'        => get_client_ip(0, true),
        'device'    => $device,
        'browser'   => $browser, //get_browser()
        'version'   => $version,
    ];

    if (empty($action)) {
        $module             = $request->module(); //Request::module()
        $controller         = $request->controller();
        $action             = $request->action();
        $adminLog['action'] = strtolower($module . '/' . $controller . '/' . $action);
    } else {
        $adminLog['action'] = $action;
    }

    Db::name('visit_log')->insert($visitLog);
}

/**
 * [存放消息记录]
 * $data = [
 *     'title' => '标题',
 *     'object'=> 'table'.$id,
 *     'content'=>'',
 * ];
 * @param  [type] $data [description]
 * @param  [type] $file [description]
 * @return [type]       [description]
 */
function c2c_put_msg($data, $file = null)
{
    // file_put_contents($file, $content, FILE_APPEND);
    // $request = Request::instance();

    $data = [];

    if (empty($data['create_time'])) {
        $data['create_time'] = time();
    }

    // return Db::name('news')->insertGetId($data);
    // Db::name('news')->insert($data);
    // return Db::name('news')->getLastInsID();
}

/**
 * [取出消息 提醒]
 * __STATIC__ 可换成 /static
 * @param  string  $type   [description]
 * @param  boolean $dialog [description]
 * @return [type]          [description]
 */
function c2c_get_msg($type = '', $dialog = false)
{

}
function c2c_get_news($type = '', $dialog = false)
{
    $where['status'] = 0;
    if (!empty($type)) {
        $where['app'] = $type;
    }
    $news = [1];
    // $news = Db::name('news')->where($where)->select();

    // 执行JS弹窗
    if ($dialog === true) {
        $sysSet = cmf_get_option('sys_settings');
        if ($sysSet['news_switch'] !== 1) {
            $count = count($news);
            if ($count > 0) {
                $msg     = '您有 ' . $count . ' 条未处理消息！';
                $jumpurl = url('goods/AdminGoods/index', ['status' => 0]);
                $audio   = '/static/audio/4182.mp3';
                $audio   = '';
                $html    = <<<EOT
                    <style type="text/css">
                        /*提示信息消息*/
                        .alert_msg{position:absolute;width:320px;right:0;bottom:0;background-color:#FCEFCF;color:#6A5128;font-size:16px;font-weight:bold;padding:25px 15px;box-sizing:border-box;box-sizing:-webkit-border-box;box-sizing:-moz-border-box;box-sizing:-ms-border-box;box-sizing:-o-border-box;}
                        .alert_msg a{display:block;margin-top:4px;}
                        .alert_msg b{position:absolute;top:3px;right:17px;font-size:23px;cursor:pointer;}
                    </style>
                    <script type="text/javascript">
                        $("#news_clock").append("<div class='alert_msg'><b>x</b>{$msg}<br/><a href='{$jumpurl}'>点击查看</a></div><audio id='sound' autoplay='autoplay' src='{$audio}'>");
                        // 消息提示弹窗
                        $(document).delegate(".alert_msg b","click",function(){
                            $(this).parent().hide(600);
                        })
                    </script>
EOT;
                echo $html;
            }
        }
    } else {
        return $news;
    }
}

/**
 * 放入回收站
 * @param  string $data [description]
 * ### $data为数组时必须是完整数据，这里不再做验证
 * @param  string $id   [description]
 * @param  string $name [description]
 * @param  string $uid  [description]
 * @return [type]       [description]
 */
function c2c_recycle_bin($data = '', $id = '', $name = '', $uid = '')
{
    if (!is_array($data)) {
        $recycle = [
            'table_name'  => $data ? $data : 'order',
            'object_id'   => $id,
            'name'        => $name,
            'user_id'     => $uid ? $uid : cmf_get_current_admin_id(),
            'create_time' => time(),
        ];
    }
    Db::name('recycleBin')->insert($recycle);
    // return Db::name('recycle_bin')->insertGetId($recycle);
}

/**
 * 信用积分区间
 * @param  int $id          [description]
 * @param  array $condition [description]
 * @return [type]           [description]
 */
function c2c_credits_range($id = '', $condition = [])
{
    if (empty($id)) {
        $result = cache('credits_range');
        if (empty($result)) {
            $result = Db::name('credits_range')->where($condition)->select()->toArray();
            cache('credits_range', $result, 7200);
        }
    } else {
        $result = Db::name('creditsRange')->where(array_merge(['id' => $id], $condition))->find();
    }
    return $result;
}

/**
 * 折扣设置
 * 满减、百分比
 * @param  string $value [description]
 * @return [type]        [description]
 */
function c2c_coupon_set($value = '')
{

}

/**
 * [用户认证信息]
 * @param  [type] $uid  [默认是当前用户]
 * @param  string $code [默认是实名认证]
 * @param  string $data [是否返回数据集、统计]
 * @return boolean or array       [description]
 */
function c2c_verify($uid = null, $code = 'certification', $data = 'status')
{

}

/**
 * 通过用户名/邮箱/手机 获取用户ID
 * @param  string $uname [description]
 * @return [type]        [description]
 */
function c2c_get_uid($uname = '')
{
    $userId  = '';
    $get_uid = session('get_uid');

    if ($uname == $get_uid['uname']) {
        $userId = $get_uid['uid'];
    } else {
        $userId = Db::name('member')->where(['username|email|mobile' => ['like', "%$uname%"]])->value('id');
        session('get_uid', ['uname' => $uname, 'uid' => $userId]);
    }

    return $userId;
}

/**
 * [状态设定：从config.php]
 * @param  string $status [description]
 * @param  string $config [description]
 * @return [type]         [description]
 */
function c2c_get_status_set($status = '', $config = 'order_status', $option = '')
{
    if (is_array($config)) {
        $ufoconfig = $config;
    } elseif (empty(config('?' . $config))) {
        return false;
    } else {
        $ufoconfig = config($config);
    }
    //非数值型非小数？
    $status  = is_numeric($status) ? intval($status) : $status;
    $opt     = is_array($option) ? $option : [$option, ''];
    $options = '';
    $options = (empty($opt[0])) ? '' : '<option value="' . $opt[1] . '"' . ($status === $opt[1] ? 'selected' : '') . '>' . $opt[0] . '</option>';
    foreach ($ufoconfig as $key => $vo) {
        $options .= '<option value="' . $key . '" ' . (($status === $key) ? 'selected' : '') . '>' . $vo . '</option>';
    }

    return $options;
}

/**
 * [选择框设定：从数据库]
 * @param  integer $selectId [当前ID]
 * @param  string  $option   [选项，value="" || value="0"]
 * @param  array   $data     [数据集]
 * @param  array   $kv       [键值对]
 * @return [type]  mix       [返回值]
 */
function c2c_get_options($selectId = 0, $option = '', $data = [], $kv = ['id', 'name'])
{
    if ($option == 'json') {
        return json_encode($data);
    } elseif ($option == 'false' || $option === false) {
        return $data;
    }
    $opt     = is_array($option) ? $option : [$option, ''];
    $options = '';
    if (!empty($data) && is_array($data)) {
        $options = (empty($opt[0])) ? '' : '<option value="' . $opt[1] . '"' . ($selectId == $opt[1] ? 'selected' : '') . '>' . $opt[0] . '</option>';
        foreach ($data as $o) {
            $options .= '<option value="' . $o[$kv[0]] . '" ' . ($selectId == $o[$kv[0]] ? 'selected' : '') . '>' . $o[$kv[1]] . '</option>';
        }
    }
    return $options;
}

/**
 * [缩略图生成]
 * getcwd()
 * banner图：
 * @param  string $imgpath [文件源:本地不带http，远程下载处理]
 * @param  array $style   config('thumbnail_size');
 * @param  number $type   [图片处理方式]
 * @return string $url    [description]
 */
function c2c_thumb_make($imgpath = 'http://hcfarm.wincomtech.cn/upload/admin/20180307/dfa2bfa304f350653f2f9389f3bb92f1.jpg', $style = [[600, 480], [600, 481], [601, 481], [602, 481]], $type = 6)
{
    $fork = true;
    // 如果是网络上的 当地址不是真实位置时，无法下载
    if (strpos($imgpath, 'http') === 0) {
        // $dirpath = request()->module().'/'.gmdate("Ymd").'/';
        $dirpath = 'test/' . gmdate("Ymd") . '/';
        // $savepath = $dirpath.time().cmf_random_string().'.jpg';
        // if (is_file($imgpath)) {
        //     c2c_download($imgpath,$savepath);
        //     $imgpath = $savepath;
        // } else {
        //     $fork = false;
        // }
        $fork = false;
    } elseif (strpos($imgpath, '/') === 0) {
        $url = cmf_get_domain() . $imgpath;
        return $url;
    }

    if (empty($fork)) {
        $url = $imgpath;
    } else {
        // 预设
        $orginpath = "./upload/" . $imgpath;
        $savepath  = '';

        // 处理 is_file($savepath)
        // $fileArr    = pathinfo($orginpath);
        // $savepath   = $fileArr['dirname'] .'/'. $fileArr['filename'] .'_'. $width .'x'. $height .'.'. $fileArr['extension'];
        if (is_file($orginpath)) {
            foreach ($style as $key => $set) {
                $savepath  = $orginpath . '_' . $key . '.jpg';
                $avatarImg = Image::open($orginpath); //每次重新实例化
                $avatarImg->thumb($set[0], $set[1], $type)->save($savepath);
            }
        }

        // 原图，如果是远程则替换成下载到本地的原始图
        $url = $imgpath;
        // $url = $orginpath;
    }

    return $url;
}

/**
 * [处理上传图片、文件。考虑后台JS插件 ]
 * 如何彻底删除照片？在url('user/AdminAsset/index'),但缩略图仍在,可修改源码实现
 * @param  array  $files [$files = ['names' => [], 'urls' => [], 'states' => []]]
 * @param  array  $style [$style = config('thumbnail_size');$style = [[580,384]]]
 * @return [type]        [description]
 */
function c2c_deal_files($files = [], $style = [])
{

}

/**
 * [下载网络文件到本地]
 * @param  [type] $url  [真实地址]
 * @param  string $path [要保存的位置]
 * @return [type]       [description]
 */
function c2c_download($url, $path = 'test/1.jpg')
{
    // $ch = curl_init();
    // curl_setopt($ch, CURLOPT_URL, $url);
    // curl_setopt($ch, CURLOPT_POST, 0);
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    // $file = curl_exec($ch);
    // curl_close($ch);

    //$filename = pathinfo($url, PATHINFO_BASENAME);
    $path = getcwd() . '/upload/' . $path;
    // $path = './upload/'.$path;
    $dirname = pathinfo($path, PATHINFO_DIRNAME);
    if (!is_dir($dirname)) {
        mkdir($dirname, 0777, true);
    }

    // $resource = fopen($path, 'a');//a w
    // fwrite($resource, $file);
    // fclose($resource);

    // set_time_limit(10);
    $content = file_get_contents($url);
    file_put_contents($path, $content);
}

/**
 * [图片上传处理]
 * @param  array  $field_var [要处理的图片字段名]
 * @param  string $module    [所在模块,可自定义]
 * @param  array  $valid     [验证规则]
 * @return [type]            [description]
 * 控制器中使用
$file_var = ['driving_license','identity_card'];
$files = model('Service')->c2c_upload_photos($file_var);
foreach ($files as $key => $it) {
if (!empty($it['err'])) {
$this->error($it['err']);
}
$post['more'][$key] = $it['data'];
}
 */
function c2c_upload_photos($field_var = [], $module = '', $valid = [])
{
    $module = empty($module) ? request()->module() : $module;
    $valid  = empty($valid) ? ['size' => 1024 * 1024, 'ext' => 'jpg,jpeg,png,gif'] : $valid;
    $move   = '.' . DS . 'upload' . DS . $module . DS;
    // $move       = ROOT_PATH . 'public' . DS . 'upload'. DS .'service'. DS;

    if (is_string($field_var)) {
        return $this->c2c_upload_photo_one($field_var, $module, $valid, $move);
    } elseif (is_array($field_var)) {
        foreach ($field_var as $fo) {
            $data[$fo] = $this->c2c_upload_photo_one($fo, $module, $valid, $move);
        }
        return $data;
    }

    return false;
}

/**
 * [处理一张图片]
 * @param  [type] $field_var [要处理的图片字段名]
 * @param  [type] $module    [所在模块,可自定义]
 * @param  [type] $valid     [验证规则]
 * @param  [type] $move      [保存的位置]
 * @return [type]            [description]
 */
function c2c_upload_photo_one($field_var, $module, $valid, $move)
{
    $file = request()->file($field_var);

    // 移动到框架应用根目录/public/uploads/ 目录下
    if (empty($file)) {
        $data['err'] = '文件上传出错，请检查';
    } else {
        $result = $file->validate($valid)->move($move);
        // var_dump($result);
        if ($result) {
            // 成功上传后 获取上传信息
            // 输出 jpg
            // echo $result->getExtension();
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
            // echo $result->getSaveName();
            // 输出 42a79759f284b767dfcb2a0197904287.jpg
            // echo $result->getFilename();

            // 处理
            $saveName = str_replace('//', '/', str_replace('\\', '/', $result->getSaveName()));
            $photo    = $module . '/' . $saveName;
            // session('photo_'.$field_var, $photo);
            $data['data'] = $photo;
            $data['err']  = '';
        } else {
            // 上传失败获取错误信息
            $data['data'] = '';
            $data['err']  = $file->getError();
        }

        // json形式
        // if ($result) {
        //     $saveName = str_replace('//', '/', str_replace('\\', '/', $result->getSaveName()));
        //     $photo         = $module .'/'. $saveName;
        //     // session('photo_'.$field_var, $photo);
        //     $data = json_encode([
        //         'code' => 1,
        //         "msg"  => "上传成功",
        //         "data" => ['file' => $photo],
        //         "url"  => ''
        //     ]);
        // } else {
        //     $data = json_encode([
        //         'code' => 0,
        //         "msg"  => $file->getError(),
        //         "data" => "",
        //         "url"  => ''
        //     ]);
        // }
    }

    return $data;
}

// 同一字段多图上传
function c2c_upload_photo_more($field_var, $module, $valid, $move)
{
    # code...
}

/**
 * PHP通用
 */

/**
 * curl 请求
 * @param $url
 * @param $params
 * @param $method
 * @return mixed
 */
function cmf_curl($url, $params = array(), $method = 'GET')
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $method == "POST" ? $url : $url . '?' . http_build_query($params));
    // curl_setopt($ch, CURLOPT_HTTPHEADER, array());
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    //超时时间
    // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    //如果是https协议
    if (strpos($url, "https://") !== false) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 检查证书中是否设置域名,true可用数字1表示,false用其它数字
    }
    //通过POST方式提交
    if ($method == "POST") {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $content = curl_exec($ch);
    //http status
    // $CURLINFO_HTTP_CODE = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $content;
}

/**
 * 网址补全
 */
function c2c_link($link)
{
    //处理网址，补加http://
    $patten = '/^(http|ftp|https):\/\/([\w.]+\/?)\S*/';
    if (preg_match($patten, $link) === 0) {
        $link = 'http://' . $link;
    }
    return $link;
}

/**
 * 过滤HTML得到纯文本
 */
function c2c_get_content($list, $len = 100)
{
    //过滤富文本
    $tmp = [];
    foreach ($list as $k => $v) {
        $content_01   = $v["content"]; //从数据库获取富文本content
        $content_02   = htmlspecialchars_decode($content_01); //把一些预定义的 HTML 实体转换为字符
        $content_03   = str_replace("&nbsp;", "", $content_02); //将空格替换成空
        $contents     = strip_tags($content_03); //函数剥去字符串中的 HTML、XML 以及 PHP 的标签,获取纯文本内容
        $con          = mb_substr($contents, 0, $len, "utf-8"); //返回字符串中的前100字符串长度的字符
        $v['content'] = $con . '……';
        $tmp[]        = $v;
    }
    return $tmp;
}

/**
 * 格式化数字
 * number_format()
 */
function c2c_num_format($value = '')
{
    if (is_numeric($value)) {
        return round($value, 2);
        // return sprintf("%.2f", $value);// 0.01;
    } else {
        return $value;
    }
}

/**
 * 来自第三方插件
 */

/**
 * [c2c_excel_port Excel处理]
 * @param  string $title    [标题]
 * @param  string $head     [头部th]
 * @param  string $field    [主体td]
 * @param  array  $where    [额外条件]
 * @param  string $dir      [服务器上保存路径]
 * @param  array  $colWidth [列宽,[24,15,15,15,18,18,20]]
 * @return [type]           [description]
 */
function c2c_excel_port($title = '', $head = '', $field = '*', $where = [], $dir = '', $colWidth = [24, 15, 15, 15, 18, 18, 20])
{
    $dir   = getcwd() . '/data/excel/' . $dir; //getcwd() 使用当前工作空间，CMF_ROOT网站根
    $excel = new Excel($dir);

    if (is_string($field)) {
        $dataTemp = $this->field($field)->where($where)->select()->toArray();
    } else {
        $dataTemp = $field;
    }
    if (empty($dataTemp)) {
        return false;
    }

    foreach ($dataTemp as $key => $row) {
        $data[] = array_values($row);
    }

    $excel->export($title, $head, $data, $colWidth);
}

/**
 * 获取发送箱配置
 * @param  boolean $check [检测是否到上限，否则邮件发不出去]
 * @return [type]         [description]
 */
function c2c_get_emailset($check = false)
{
    $where = 'status=1 AND (upper_limit-used_limit)>0';
    if ($check === true) {
        $sets = Db::name('email_set')->where($where)->column('*', 'id');
    } else {
        // $sets = cmf_get_option('smtp_setting');
        $sets = cache('smtp_setting');
        if (empty($sets)) {
            // 定时任务 - 每日0:00清零used_limit
            $sets = Db::name('email_set')->where($where)->column('*', 'id');
            cache('smtp_setting', $sets);
        }
    }
    return $sets;
}

/**
 * 发送邮件
 * 核心解决： 批量、多个发邮箱。邮件每日上限、随机发件箱
 * 单一发送： c2c_send_email('baijunyao@baijunyao','邮件标题','邮件内容');
 * 邮件群发： $emails=['a@qq.com','b@qq.com'];c2c_send_email($emails,'邮件标题','邮件内容');
 * 发送失败：SMTP connect() failed. https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting
 * 失败原因： SMTPAutoTLS=false
 * @param string $address 收件人邮箱,可设多个
 * @param string $subject 邮件标题
 * @param string $message 邮件内容
 * @param string $smtpSetting 发件箱配置
 * @return array
 * 返回格式：
 *     array(
 *        'error'=>0|1,//0代表出错
 *        'message'=> "出错信息"
 *     );
 */
function c2c_send_email($address, $subject, $message, $smtpSetting = '')
{
    if (empty($address)) {
        return ['error' => 1, 'message' => '收件箱为空'];
    }
    if (empty($smtpSetting)) {
        // $smtpSetting = c2c_get_emailset(true);
    }
    // dump($smtpSetting);die;

    // vendor('phpmailer/phpmailer/class.phpmailer');
    // vendor('phpmailer/phpmailer/class.smtp');
    $mail = new \PHPMailer();

    // 设置邮件的字符编码，若不指定，则为'UTF-8'。
    $mail->CharSet = 'UTF-8';

    // 设置PHPMailer使用SMTP服务器发送Email。
    $mail->isSMTP();
    // 设置调试模式
    //$mail->SMTPDebug = 3;
    // 启用SMTP验证功能，设置为"需要验证"。
    $mail->SMTPAuth = true;
    // TLS方式，这里不注释的话则邮件发送不了。
    // $mail->SMTPAutoTLS = false;

    // 设置SMTP服务器。
    $mail->Host = $smtpSetting['host'];
    // 设置安全协议（连接方式），可以注释掉。
    $secure           = $smtpSetting['smtp_secure'];
    $mail->SMTPSecure = empty($secure) ? '' : $secure;
    // 设置SMTP服务器端口。
    $port       = $smtpSetting['port'];
    $mail->Port = empty($port) ? "25" : $port;
    // 设置用户名和密码。
    $mail->Username = $smtpSetting['username']; //发件箱帐号
    $mail->Password = $smtpSetting['password']; //发件箱密码或授权码

    // 设置发件人名字
    $mail->FromName = $smtpSetting['from_name'];
    // 设置邮件头的From字段，发件人邮箱地址。
    $mail->From = $smtpSetting['from'];

    // 添加收件人地址，可以多次使用来添加多个收件人
    if (is_array($address)) {
        foreach ($address as $a) {
            $mail->addAddress($a);
        }
    } else {
        $mail->addAddress($address, '');
    }
    // 设置邮件标题
    $mail->Subject = $subject;
    // 设置邮件正文
    $mail->Body = $message;

    // 是否HTML格式邮件
    $mail->isHTML(true);
    // 设置超时时间
    $mail->Timeout = 10;

    // 发送邮件。
    if (!$mail->send()) {
        $mailError = $mail->ErrorInfo;
        return ['error' => 1, 'message' => $mailError];
    } else {
        return ['error' => 0, 'message' => 'success'];
    }
}

/**
 * 邮件发送
 * 参考 cmf_send_email(),这里不再重复
 * 明文/SSL/TLS三种方式
 * POP3/IMAP/SMTP/Exchange/CardDAV/CalDAV
 * @param  [type] $mailto  [收件人地址,可设多个]
 * @param  string $subject [邮件标题]
 * @param  string $body    [邮件正文]
 * @param  string $m_service    [开关]
 * @return [type]          [description]
 */
// function c2c_send_email2($mailto, $subject = '', $body = '', $m_service = true)
// {
//     $smtpSetting = cmf_get_option('smtp_setting');
//     if ($m_service) {
//         $mail       = new PHPMailer5; // 实例化
//         $m_host     = $smtpSetting['host'];
//         $m_username = $smtpSetting['username'];
//         $m_password = $smtpSetting['password'];
//         $m_secure   = $smtpSetting['smtp_secure'];
//         $m_port     = $smtpSetting['port'];
//         $m_fromname = $smtpSetting['from_name'];
//         $m_altbody  = '';

//         $mail->CharSet = 'UTF-8'; // 设定邮件编码
//         $mail->isSMTP(); // 设定使用SMTP服务
//         // $mail->SMTPDebug = 3; // 启用SMTP调试功能
//         $mail->SMTPAuth   = true; // 启用SMTP验证功能
//         $mail->Host       = $m_host; // SMTP服务器
//         $mail->Username   = $m_username; // SMTP服务器用户名
//         $mail->Password   = $m_password; // SMTP服务器密码
//         $mail->SMTPSecure = empty($m_secure) ? '' : $m_secure; // 安全协议，可以注释掉
//         $mail->Port       = empty($m_port) ? '25' : $m_port; // SMTP服务器的端口号

//         $mail->From     = $m_username; // 发件人地址
//         $mail->FromName = $m_fromname; // 发件人姓名

//         $mail->addAddress($mailto, ''); // 收件地址，可选指定收件人姓名
//         $mail->Subject = $subject; // 邮件标题
//         $mail->Body    = $body; // 邮件内容

//         $mail->isHTML(true); // 是否HTML格式邮件
//         $mail->AltBody = $m_altbody;// 邮件正文不支持HTML的备用显示

//         if ($mail->send()) {
//             return true;
//         }
//     } else {
//         $s_email = $smtpSetting['username'];
//         $subject = "=?UTF-8?B?" . base64_encode($subject) . "?="; // 解决邮件主题乱码问题，UTF8编码格式
//         $header  = "From: 测试 <$s_email>\n";
//         $header .= "Return-Path: <$s_email>\n"; // 防止被当做垃圾邮件
//         $header .= "MIME-Version: 1.0\n";
//         $header .= "Content-type: text/html; charset=utf-8\n"; // 邮件内容为utf-8编码
//         $header .= "Content-Transfer-Encoding: 8bit\r\n"; // 注意header的结尾，只有这个后面有\r
//         ini_set('sendmail_from', $s_email); // 解决mail的一个bug
//         $body = wordwrap($body, 70); // 每行最多70个字符,这个是mail方法的限制
//         if (mail($mailto, $subject, $body, $header)) {
//             return ture;
//         }
//     }
// }
