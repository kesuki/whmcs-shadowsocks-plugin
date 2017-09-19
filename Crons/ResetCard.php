<?php
define('DB_NAME', '');//数据库名字
define('DB_USER', '');//用户名
define('DB_PASS', '');//密码
define('DB_HOST', 'localhost');//数据库IP

$cardmysql = new mysqli(DB_HOST, DB_USER, DB_PASS , DB_NAME);
if(!$cardmysql) {
    die('Unable to connect to card database.');
} else {
    $sql = 'SELECT * FROM `card_usage` WHERE `enable` = 1';
    $cards = $cardmysql->query($sql);
    if($cards){
        foreach($cards as $card){
            if(strtotime(date('Y-m-d')) >= $card['duedate']){
                $sql = 'UPDATE `user` SET `transfer_enable` = `transfer_enable` - '. $card['traffic'] * 1048576 .' WHERE `sid` = ' . $card['sid'];
                $c = $cardmysql->query($sql);
                if($c){
                    $sql = "UPDATE `card_usage` SET `enable` = 0 WHERE `card` = '" . $card['card'] ."'";
                    $c = $cardmysql->query($sql);
                    if($c){
                        echo('Reset '.$card['sid'].' Success, Card number: '.$card['card'] .'</br>');
                    }else{
                        echo('Failed To disable '.$card['card'].'(CardUnableFailed)</br>'); 
                    }
                }else{
                    echo('Failed To Change'.$card['sid'].'(BandSetFailed)</br>');
                }
            }
        }
        die('Done!');
    }
    die('No cards Enabled');
}


