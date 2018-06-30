<?php
// 命令行上： php think test
// DOS上小写？而是这个命令名称是固定命名(test)，不可以自定义命令的名称

// 经以下三种测试，始终使用 php think test 命令执行
return [
    'app\tools\command\Test',
    // 'app\tools\command\Test1',
    // 'app\tools\controller\Test2',
];