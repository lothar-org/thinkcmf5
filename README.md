# platform
C2C游戏交易平台


主营网站：
        https://www.mmogo.com/
        https://www.mmotank.com/
参考站：https://www.playerauctions.com/
演示站：http://112.27.206.173:8081/
流程图：https://www.processon.com/view/link/5af172c1e4b02c126a595699    4321
文档：
    为了更爽：https://www.kancloud.cn/thinkcmf/doc/313906
    目录结构：https://www.kancloud.cn/thinkcmf/doc/266477
    开发规范：https://www.kancloud.cn/thinkcmf/doc/266476
    数据库：https://www.kancloud.cn/thinkcmf/doc/303474
    系统函数：https://www.kancloud.cn/thinkcmf/doc/266499
    专题：https://www.kancloud.cn/thinkcmf/doc/266551
    插件开发：https://www.kancloud.cn/thinkcmf/doc/266542
    URL规则：https://www.kancloud.cn/thinkcmf/doc/411993
本地文档：
    开发文档: \platform\doc\开发文档\开发文档.docx
    开发规范: \platform\doc\开发文档\开发规范.docx

前台测试：http://tx.c2c/user/login/
    账号： 密码： 
测试后台： http://tx.c2c/admin/index/index.html
    账号： admin    密码： 123456
    账号： super    密码： 123456




目录结构
    app 应用目录
        admin   系统后台，管理者
        user    前后台用户
        order   订单管理
        goods   产品相关，订单
        funds   资金管理
        portal  门户应用目录
        analyze 报表统计
        tools   工具箱

数据库       \platform\wwwroot\thinkcmf5\platform.sql
                20180524 更新
入口文件     \public\index.php debug开启
配置文件     \app\config.php
数据库配置   \data\conf\database.php
自定义配置   \data\conf\config.php
                20180604 更新
自定义方法   \app\common.php
语言包优先级 \simplewind\cmf\lang\zh-cn.php
            \data\lang\zh-cn\admin_menu.php
            \app\admin\lang\zh-cn.php

模型        \simplewind\cmf\model\ComModel.php
            \app\tools\model\OrderStatusModel.php
            \app\user\model\CreditsModel.php
控制器       \app\tools\controller\AdminRateController.php
            \app\user\controller\AdminCreditsController.php

说明：DEBUG模式下后台菜单管理可见
包管理：https://packagist.org/packages/
# 安装: php composer.phar install
     如果已添加到环境变量: composer install
# 添加包： 
    composer require cbschuld/browser.php 1.95.x-dev
    composer require paypal/merchant-sdk-php ~3.12
    composer require topthink/think-worker
    composer require workerman/workerman-for-win  /*window下做服务端*/
# 更新包：composer update vendor/package
    composer update
    composer update monolog/monolog [...]




















































#字段过滤
    $data = array_map('trim', $this->request->post());
#富文本原始数据。 原样获取,避免框架过滤
    $data['content'] = isset($_POST['content'])?$_POST['content']:'';
#验证
    $result = $this->validate($data, 'Order.' . $valid);
    if ($result !== true) {
        $this->error($result);
    }
#try catch模块
    $result = true;
    Db::startTrans();
    try {
        Db::commit();
    } catch (\Exception $e) {
        Db::rollback();
        $result = false;
    }
#管理员日志
    c2c_admin_log($this->name .':'. $id, '删除'. $this->flag .'：'. $find);

生成缩略图
图表


Select2插件
    <link rel="stylesheet" type="text/css" href="__STATIC__/js/select2/select2.min.css">
    <script type="text/javascript" src="__STATIC__/js/select2/select2.min.js"></script>
    <script type="text/javascript">
        //页面加载完成后初始化select2控件  
        $(function() {
            $("#category").select2();
        });
    </script>