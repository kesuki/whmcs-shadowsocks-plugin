<?php
define('DB_NAME', 'xxx');
define('DB_USER', 'xxx');
define('DB_PASS', 'xxx');
define('DB_HOST', 'xxx');

$mysql = mysql_connect(DB_HOST, DB_USER, DB_PASS);
if(!$mysql) {
  die(json_encode(array(
    'status' => 'Error',
    'result' => 'Unable to connect to database.'
  )));
} else {
  mysql_select_db(DB_NAME, $mysql);
  mysql_query("UPDATE `user` SET `u` = '0', `d` = '0';");
}

?>