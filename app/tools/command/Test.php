<?php
namespace app\tools\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class Test extends Command
{
    protected function configure()
    {
        $this->setName('test')->setDescription('Here is the remark ');
    }

    protected function execute(Input $input, Output $output)
    {
        $user = Db::name('user')->field('user_login,user_nickname,user_email,mobile,last_login_ip')->find();
        $output->writeln("TestCommand:".json_encode($user));
    }
}