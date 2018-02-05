<?php
define('DB_NAME', '');//数据库名字
define('DB_USER', '');//数据库用户名
define('DB_PASS', '');//数据库密码
define('DB_HOST', 'localhost');//数据库IP/域名

$mysql = new mysqli(DB_HOST, DB_USER, DB_PASS , DB_NAME);
if(!$mysql) {
	echo("Mysql连接失败");
} else {
	$sql = "Select `u`,`d`,`enable`,`sid` FROM `user`";
	$users = $mysql->query($sql);
	$uasql = "Select * FROM `user_usage` ORDER BY `date`";
	$old_user_usage = $mysql->query($uasql);	
	$ua = array();
	if($old_user_usage){
		foreach($old_user_usage as $oua){
			$add = false;
			if($ua[$oua['sid']]){
				if($ua[$oua['sid']]['date'] <= $oua['date']){
					$add = true;
				}
			}else{
				$add = true;
			}
			if($add){
				$ua[$oua['sid']] = array(
				'u' => $oua['tupload'],
				'd' => $oua['tdownload'],
				'date' => $oua['date']
				);
			}
		}
	}else{
		$ua = false;
	}
	$nua = array();
	if($users){
		foreach($users as $user){
			if($user['enable'] = 1){
				if($ua[$user['sid']]){
					$nua[$user['sid']] = array(
					'u' => $user['u'] - $ua[$user['sid']]['u'],
					'd' => $user['d'] - $ua[$user['sid']]['d'],
					'tu' => $user['u'],
					'td' => $user['d'],
					'date' => time(),
					'sid' => $user['sid']
					);
				}else{
					$nua[$user['sid']] = array(
					'u' => $user['u'],
					'd' => $user['d'],
					'tu' => $user['u'],
					'td' => $user['d'],
					'date' => time(),
					'sid' => $user['sid']
					);
				}
			}
		}
	}else{
		echo("Mysql无数据");
		$nua = false;
	}
	if($nua){
		foreach($nua as $up){
			$dataa = $up['u'].",".$up['d'].",".$up['tu'].",".$up['td'].",".$up['date'].",".$up['sid'];
			$upmysql = "INSERT INTO `user_usage` (`upload`,`download`,`tupload`,`tdownload`,`date`,`sid`) VALUES(".$dataa.")";
			$mysql->query($upmysql);
		}
		echo("操作完成，时间".date('Y-m-d H:i:s',time())."</br>");
		$datee = time();
		$oldatee = $datee - 3600*24*6;
		$dlsql = "delete from `user_usage` where `date` <= ".$oldatee;
		$mysql->query($dlsql);
		echo(date('Y-m-d H:i:s',$oldatee)."前的数据已删除");
	}
}

?>