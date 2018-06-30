<?php
/*
 * 数据库备份与还原
 */
class SqlBack
{
    private $end  = ";--end_flag--";
    private $line = PHP_EOL;
    private $dir;
    private $db;
    private $dbname;

    public function __construct($host = 'localhost', $username = 'root', $password = 'root', $database = 'test', $port = '3306', $charset = 'utf8', $dir = 'data/')
    {
        header("content-type:text/html;charset=utf-8");
        set_time_limit(0); //无时间限制
        @ob_end_flush();
        $this->dbname = $database;
        $this->dir    = $dir;
        // 连接数据库
        $this->db = new mysqli($host, $username, $password, $database, $port) or die('<p class="dbDebug"><span class="err">Mysql Connect Error : </span>' . mysqli_connect_error() . '</p>');
        // 数据库编码方式
        mysqli_set_charset($this->db, $charset);
        //连续的->报错
        // ($this->db)->set_charset($charset);
    }

    /*
     * 备份数据库
     *  */
    public function backup()
    {
        $this->showMsg('正在备份，请等待，不要做其他操作');
        //设置超时时间为0，表示一直执行。当php在safe mode模式下无效，此时可能会导致导入超时，此时需要分段导入

        $dname = $this->dbname;
        $m     = $this->db;
        $mysql = "";
        //结束符
        $line = $this->line;
        $end  = ($this->end) . $line;
        $dir  = $this->dir;

        //得到所有表
        $tables = $m->query("show tables");

        while ($t = ($tables->fetch_assoc())) {
            $table = $t['Tables_in_' . $dname];
            $mysql .= "DROP TABLE IF EXISTS `" . $table . "`" . $end;

            $creat = $m->query("show create table $table");
            $sql   = $creat->fetch_assoc();
            //创建数据库表结构语句
            $mysql .= $sql['Create Table'] . $end;

            $insert = $m->query("select * from $table");
            //组装insert语句
            $k = 0;
            while ($data = ($insert->fetch_assoc())) {
                if ($k == 0) {
                    $k = 1;
                    //获取数组的键名数组
                    $keys = array_keys($data);

                    //addslashes — 使用反斜线引用字符串,为了清除`的影响
                    $keys = array_map('addslashes', $keys);

                    $keys = join('`,`', $keys);
                    $keys = "`" . $keys . "`";
                    $vals = array_values($data);
                    $vals = array_map('addslashes', $vals);
                    $vals = join("','", $vals);
                    $vals = "'" . $vals . "'";
                    $mysql .= "insert into $table($keys) values $line ($vals)";
                } else {
                    $vals = array_values($data);
                    $vals = array_map('addslashes', $vals);
                    $vals = join("','", $vals);
                    $vals = "'" . $vals . "'";
                    $mysql .= ",$line($vals)";
                }
            }
            if ($k == 1) {
                $mysql .= $end;
            }
        }

        //存储在data文件夹下
        $filename = $dir . $dname . date('Ymd-His') . ".sql";
        $fp       = fopen($filename, 'wb');
        if (!$fp) {
            $this->showMsg('文件打开失败', true);
            return false;
        }
        fputs($fp, $mysql);
        fclose($fp);

        return true;
    }

    //数据库还原
    public function restore($fname)
    {
        $m = $this->db;
        //指定要恢复的MySQL备份文件路径,请自已修改此路径

        if (file_exists($fname)) {
            $this->showMsg('正在还原，请等待，不要做其他操作');
            // 锁定数据库，以免备份或导入时出错
            $m->query("lock tables " . ($this->dbname) . " WRITE");
            $sql_value = "";
            $sqls      = file($fname);
            foreach ($sqls as $sql) {
                $sql_value .= $sql;
            }
            $a     = explode(($this->end) . ($this->line), $sql_value);
            $total = count($a) - 1;
            for ($i = 0; $i < $total; $i++) {
                //执行命令
                $m->query($a[$i]);
            }
            // 解锁
            $m->query("unlock tables ");
            return true;
        } else {
            $this->showMsg('MySQL备份文件不存在，请检查文件路径是否正确', true);
            return false;
        }
    }

    //  及时输出信息
    private function showMsg($msg, $err = false)
    {
        $err = $err ? "<span class='err'>ERROR:</span>" : '';
        echo "<p class='dbDebug'>" . $msg . "</p>";
        flush();
    }

    // 析构
    public function __destruct()
    {
        mysqli_query($this->db, "unlock tables ");
        mysqli_close($this->db);
    }
}
