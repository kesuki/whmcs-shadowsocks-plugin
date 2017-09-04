<?php
define('DB_NAME', '');//数据库
define('DB_USER', '');//用户名
define('DB_PASS', '');//密码
define('DB_HOST', '');

$mysql = new mysqli(DB_HOST, DB_USER, DB_PASS , DB_NAME);
if(!$mysql) {
  die(json_encode(array(
    'status' => 'Error',
    'result' => 'Unable to connect to database.'
  )));
} else {
  $mysql->query("UPDATE `user` SET `u` = '0', `d` = '0' where `need_reset` = 1;");
  $mysql->query("delete from `user_usage`;");
}

?>