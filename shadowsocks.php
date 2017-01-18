<?php
function initialize(array $params)
{
	$query['RECYCLE'] = 'SELECT `port` FROM `recycle_bin` ORDER BY `created_at` DESC LIMIT 1';
	$query['DELETE_RECYCLE'] = 'DELETE FROM `recycle_bin` WHERE `port` = :port';
	$query['ADD_RECYCLE'] = 'INSERT INTO `recycle_bin`(`port`,`created_at`) VALUES (:port,UNIX_TIMESTAMP())';
	$query['LATEST_USER'] = 'SELECT `port` FROM `user` ORDER BY `port` DESC LIMIT 1';
	$query['CREATE_ACCOUNT'] = 'INSERT INTO `user`(`passwd`,`transfer_enable`,`port`,`created_at`,`need_reset`,`sid`) VALUES (:passwd,:transfer_enable,:port,UNIX_TIMESTAMP(),:need_reset,:sid)';
	$query['ALREADY_EXISTS'] = 'SELECT `port` FROM `user` WHERE `sid` = :sid';
	$query['ENABLE'] = 'UPDATE `user` SET `enable` = :enable WHERE `sid` = :sid';
	$query['DELETE_ACCOUNT'] = 'DELETE FROM `user` WHERE `sid` = :sid';
	$query['CHANGE_PASSWORD'] = 'UPDATE `user` SET passwd = :passwd WHERE `sid` = :sid';
	$query['USERINFO'] = 'SELECT `id`,`passwd`,`port`,`t`,`u`,`d`,`transfer_enable`,`enable`,`created_at`,`updated_at`,`need_reset`,`sid` FROM `user` WHERE `sid` = :sid';
	$query['RESET'] = 'UPDATE `user` SET `u`=0,`d`=0 WHERE `sid` = :sid';
	$query['CHANGE_PACKAGE'] = 'UPDATE `user` SET `transfer_enable` = :transfer_enable WHERE `sid` = :sid';
	return $query;
}
function convert($number, $from, $to)
{
	$to = strtolower($to);
	$from = strtolower($from);
	switch ($from) {
	case 'gb':
		switch ($to) {
		case 'mb':
			return $number * 1024;
		case 'bytes':
			return $number * 1073741824;
		default:
		}
		return $number;
		break;
	case 'mb':
		switch ($to) {
		case 'gb':
			return $number / 1024;
		case 'bytes':
			return $number * 1048576;
		default:
		}
		return $number;
		break;
	case 'bytes':
		switch ($to) {
		case 'gb':
			return $number / 1073741824;
		case 'mb':
			return $number / 1048576;
		default:
		}
		return $number;
		break;
	default:
	}
	return $number;
}
function shadowsocks_MetaData()
{
	return array('DisplayName' => 'Shadowsocks for whmcs', 'RequiresServer' => true);
}
function shadowsocks_ConfigOptions()
{
	return array(
	'数据库名' => array('Type' => 'text', 'Size' => '25'),
	'重置流量' => array(
		'Type'        => 'dropdown',
		'Options'     => array('1' => '需要重置', '0' => '不需要重置'),
		'Description' => '是否需要重置流量'
		),
	'流量限制' => array('Type' => 'text', 'Size' => '25', 'Description' => '单位MB'),
	'授权密钥' => array('Type' => 'text', 'Size' => '32', 'Description' => '请输入您从购买者手中获取的密钥'),
	'起始端口' => array('Type' => 'text', 'Size' => '25', 'Description' => '如果数据库有记录此项无效'),
	'线路列表' => array('Type' => 'textarea', 'Rows' => '3', 'Cols' => '50', 'Description' => '格式 xxx|服务器地址|加密方式|协议|混淆| 一行一个')
	);
}
function shadowsocks_TestConnection(array $params)
{
	try {
		$dbhost = $params['serverip'];
		$dbuser = $params['serverusername'];
		$dbpass = $params['serverpassword'];
		$db = new PDO('mysql:host=' . $dbhost, $dbuser, $dbpass);
		$success = true;
		$errorMsg = '';
	}
	catch (Exception $e) {
		logModuleCall('shadowsocks', 'shadowsocks_TestConnection', $params, $e->getMessage(), $e->getTraceAsString());
		$success = false;
		$errorMsg = $e->getMessage();
	}
	return array('success' => $success, 'error' => $errorMsg);
}
function shadowsocks_CreateAccount(array $params)
{
	$query = initialize($params);
	try {
		$dbhost = $params['serverip'];
		$dbname = $params['configoption1'];
		$dbuser = $params['serverusername'];
		$dbpass = $params['serverpassword'];
		$db = new PDO('mysql:host=' . $dbhost . ';dbname=' . $dbname, $dbuser, $dbpass);
		$already = $db->prepare($query['ALREADY_EXISTS']);
		$already->bindValue(':sid', $params['serviceid']);
		$already->execute();
		if ($already->fetchColumn()) {
			return 'User already exists.';
		}
		$bandwidth = (!empty($params['configoption3']) ? convert($params['configoption3'], 'mb', 'bytes') : (!empty($params['configoptions']['traffic']) ? convert($params['configoptions']['traffic'], 'gb', 'bytes') : '1099511627776'));

		$recycle = $db->prepare($query['RECYCLE']);
		$recycle->execute();
		$recycle = $recycle->fetch();
		if ($recycle) {
			$port = $recycle['port'];
			define('RECYCLE', true);
		}
		else {
			$port = $db->prepare($query['LATEST_USER']);
			$port->execute();
			$port = $port->fetch();
			if ($port) {
				$port = $port['port'] + 1;
			}
			else {
				$port = (!empty($params['configoption5']) ? $params['configoption5'] : '10000');
			}
		}
		$create = $db->prepare($query['CREATE_ACCOUNT']);
		$create->bindValue(':passwd', $params['customfields']['password']);
		$create->bindValue(':transfer_enable', $bandwidth);
		$create->bindValue(':port', $port);
		$create->bindValue(':need_reset', $params['configoption2']);
		$create->bindValue(':sid', $params['serviceid']);
		$create = $create->execute();
		
		if ($create) {
			if (defined('RECYCLE') && RECYCLE) {
				$recycle = $db->prepare($query['DELETE_RECYCLE']);
				$recycle->bindParam(':port', $port);
				$recycle->execute();
			}
			return 'success';
		}
		else {
			$error = $db->errorInfo();
			return $error;
		}
	}
	catch (Exception $e) {
		logModuleCall('shadowsocks', 'shadowsocks_CreateAccount', $params, $e->getMessage(), $e->getTraceAsString());
		return $e->getMessage();
	}
}
function shadowsocks_SuspendAccount(array $params)
{
	$query = initialize($params);
	try {
		$dbhost = $params['serverip'];
		$dbname = $params['configoption1'];
		$dbuser = $params['serverusername'];
		$dbpass = $params['serverpassword'];
		$db = new PDO('mysql:host=' . $dbhost . ';dbname=' . $dbname, $dbuser, $dbpass);
		$enable = $db->prepare($query['ENABLE']);
		$enable->bindValue(':enable', '0');
		$enable->bindValue(':sid', $params['serviceid']);
		
		$todo = $enable->execute();
		if (!$todo) {
			$error = $db->errorInfo();
			return $error;
		}
		return 'success';
	}
	catch (Exception $e) {
		logModuleCall('shadowsocks', 'shadowsocks_SuspendAccount', $params, $e->getMessage(), $e->getTraceAsString());
		return $e->getMessage();
	}
}
function shadowsocks_UnsuspendAccount(array $params)
{
	$query = initialize($params);
	try {
		$dbhost = $params['serverip'];
		$dbname = $params['configoption1'];
		$dbuser = $params['serverusername'];
		$dbpass = $params['serverpassword'];
		$db = new PDO('mysql:host=' . $dbhost . ';dbname=' . $dbname, $dbuser, $dbpass);
		$enable = $db->prepare($query['ENABLE']);
		$enable->bindValue(':enable', '1');
		$enable->bindValue(':sid', $params['serviceid']);
		
		$todo = $enable->execute();
		if (!$todo) {
			$error = $db->errorInfo();
			return $error;
		}
		return 'success';
	}
	catch (Exception $e) {
		logModuleCall('shadowsocks', 'shadowsocks_UnsuspendAccount', $params, $e->getMessage(), $e->getTraceAsString());
		return $e->getMessage();
	}
}
function shadowsocks_TerminateAccount(array $params)
{
	$query = initialize($params);
	try {
		$dbhost = $params['serverip'];
		$dbname = $params['configoption1'];
		$dbuser = $params['serverusername'];
		$dbpass = $params['serverpassword'];
		$db = new PDO('mysql:host=' . $dbhost . ';dbname=' . $dbname, $dbuser, $dbpass);
		$already = $db->prepare($query['ALREADY_EXISTS']);
		$already->bindValue(':sid', $params['serviceid']);
		$already->execute();
		$already = $already->fetch();
		
		if ($already) {
			$port = $already['port'];
			$recycle = $db->prepare($query['ADD_RECYCLE']);
			$recycle->bindValue(':port', $port);
			
			$todo = $recycle->execute();
			if (!$todo) {
				$error = $db->errorInfo();
				return $error;
			}
		}
		else {
			return 'User does not exists.';
		}
		$enable = $db->prepare($query['DELETE_ACCOUNT']);
		$enable->bindValue(':sid', $params['serviceid']);
		
		$todo = $enable->execute();
		if (!$todo) {
			$error = $db->errorInfo();
			return $error;
		}
		return 'success';
	}
	catch (Exception $e) {
		logModuleCall('shadowsocks', 'shadowsocks_TerminateAccount', $params, $e->getMessage(), $e->getTraceAsString());
		return $e->getMessage();
	}
}
function shadowsocks_ChangePackage(array $params)
{
	$query = initialize($params);
	try {
		$dbhost = $params['serverip'];
		$dbname = $params['configoption1'];
		$dbuser = $params['serverusername'];
		$dbpass = $params['serverpassword'];
		$db = new PDO('mysql:host=' . $dbhost . ';dbname=' . $dbname, $dbuser, $dbpass);
		$bandwidth = (!empty($params['configoption3']) ? convert($params['configoption3'], 'mb', 'bytes') : (!empty($params['configoptions']['traffic']) ? convert($params['configoptions']['traffic'], 'gb', 'bytes') : '1099511627776'));
		$enable = $db->prepare($query['CHANGE_PACKAGE']);
		$enable->bindValue(':transfer_enable', $bandwidth);
		$enable->bindValue(':sid', $params['serviceid']);
		$todo = $enable->execute();
		if (!$todo) {
			$error = $db->errorInfo();
			return $error;
		}
		return 'success';
	}
	catch (Exception $e) {
		logModuleCall('shadowsocks', 'shadowsocks_ChangePackage', $params, $e->getMessage(), $e->getTraceAsString());
		return $e->getMessage();
	}
}
function shadowsocks_ChangePassword(array $params)
{
	$query = initialize($params);
	try {
		$dbhost = $params['serverip'];
		$dbname = $params['configoption1'];
		$dbuser = $params['serverusername'];
		$dbpass = $params['serverpassword'];
		$db = new PDO('mysql:host=' . $dbhost . ';dbname=' . $dbname, $dbuser, $dbpass);
		$enable = $db->prepare($query['CHANGE_PASSWORD']);
		$enable->bindValue(':passwd', $params['password']);
		$enable->bindValue(':sid', $params['serviceid']);
		$todo = $enable->execute();
		if (!$todo) {
			$error = $db->errorInfo();
			return $error;
		}
		return 'success';
	}
	catch (Exception $e) {
		logModuleCall('shadowsocks', 'shadowsocks_ChangePassword', $params, $e->getMessage(), $e->getTraceAsString());
		return $e->getMessage();
	}
}
function shadowsocks_AdminCustomButtonArray()
{
	return array('Reset' => 'ResetBandwidth');
}
function shadowsocks_ResetBandwidth(array $params)
{
	$query = initialize($params);
	try {
		$dbhost = ($params['serverip']);
		$dbname = ($params['configoption1']);
		$dbuser = ($params['serverusername']);
		$dbpass = ($params['serverpassword']);
		$db = new PDO('mysql:host=' . $dbhost . ';dbname=' . $dbname, $dbuser, $dbpass);
		$enable = $db->prepare($query['RESET']);
		$enable->bindValue(':sid', $params['serviceid']);
		$todo = $enable->execute();
		if (!$todo) {
			$error = $db->errorInfo();
			return $error;
		}
		return 'success';
	}
	catch (Exception $e) {
		logModuleCall('shadowsocks', 'shadowsocks_ResetBandwidth', $params, $e->getMessage(), $e->getTraceAsString());
		return $e->getMessage();
	}
}
function shadowsocks_ClientArea(array $params)
{
	$query = initialize($params);
	try {
		$dbhost = $params['serverip'];
		$dbname = $params['configoption1'];
		$dbuser = $params['serverusername'];
		$dbpass = $params['serverpassword'];
		$db = new PDO('mysql:host=' . $dbhost . ';dbname=' . $dbname, $dbuser, $dbpass);
		$usage = $db->prepare($query['USERINFO']);
		$usage->bindValue(':sid', $params['serviceid']);
		$usage->execute();
		$usage = $usage->fetch();
		$nodes = $params['configoption6'];
		$results = array();
		$node = explode('|', $nodes);
		$x=0;$count=count($node)-1;
		while($x <= $count){
			$results[$x/5][$x%5] = $node[$x];
			$x++;
		}
		$user = array('passwd' => $usage['passwd'], 'port' => $usage['port'], 'u' => $usage['u'], 'd' => $usage['d'], 't' => $usage['t'], 'sum' => $usage['u'] + $usage['d'], 'transfer_enable' => $usage['transfer_enable'], 'created_at' => $usage['created_at'], 'updated_at' => $usage['updated_at']);
		if ($usage && $usage['enable']) {
			return array(
	'tabOverviewReplacementTemplate' => 'details.tpl',
	'templateVariables'              => array('usage' => $user, 'params' => $params, 'nodes' => $results)
	);
		}
		return array(
	'tabOverviewReplacementTemplate' => 'error.tpl',
	'templateVariables'              => array('usefulErrorHelper' => '出现了一些问题，可能您的服务还未开通，请稍后再来试试。')
	);
	}
	catch (Exception $e) {
		logModuleCall('shadowsocks', 'shadowsocks_ClientArea', $params, $e->getMessage(), $e->getTraceAsString());
		return array(
	'tabOverviewReplacementTemplate' => 'error.tpl',
	'templateVariables'              => array('usefulErrorHelper' => $e->getMessage())
	);
	}
}
function shadowsocks_AdminServicesTabFields(array $params)
{
	$query = initialize($params);
	try {
		$dbhost = $params['serverip'];
		$dbname = $params['configoption1'];
		$dbuser = $params['serverusername'];
		$dbpass = $params['serverpassword'];
		$db = new PDO('mysql:host=' . $dbhost . ';dbname=' . $dbname, $dbuser, $dbpass);
		$userinfo = $db->prepare($query['USERINFO']);
		$userinfo->bindValue(':sid', $params['serviceid']);
		$userinfo->execute();
		$userinfo = $userinfo->fetch();
		if ($userinfo) {
			return array('端口' => $userinfo['port'], '流量' => convert($userinfo['transfer_enable'], 'bytes', 'mb') . 'MB', '上传' => round(convert($userinfo['u'], 'bytes', 'mb')) . 'MB', '下载' => round(convert($userinfo['d'], 'bytes', 'mb')) . 'MB', '已用' => round(convert($userinfo['d'] + $userinfo['u'], 'bytes', 'mb')) . 'MB', '最后使用' => date('Y-m-d H:i:s', $userinfo['t']), '上次重置' => date('Y-m-d H:i:s', $userinfo['updated_at']));
		}
	}
	catch (Exception $e) {
		logModuleCall('shadowsocks', 'shadowsocks_AdminServicesTabFields', $params, $e->getMessage(), $e->getTraceAsString());
		return $e->getTraceAsString();
	}
}
?>