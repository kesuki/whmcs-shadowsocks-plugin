<?php
if(!defined("WHMCS")){
  die("This file cannot be accessed directly");
}

multi_language_support();

function initialize(array $params , $date = false){
	$query['RECYCLE'] = 'SELECT `port` FROM `recycle_bin` ORDER BY `created_at` DESC LIMIT 1';
	$query['DELETE_RECYCLE'] = 'DELETE FROM `recycle_bin` WHERE `port` = :port';
	$query['ADD_RECYCLE'] = 'INSERT INTO `recycle_bin`(`port`,`created_at`) VALUES (:port,UNIX_TIMESTAMP())';
	$query['LATEST_USER'] = 'SELECT `port` FROM `user` ORDER BY `port` DESC LIMIT 1';
	$query['CREATE_ACCOUNT'] = 'INSERT INTO `user`(`passwd`,`u`,`d`,`transfer_enable`,`port`,`created_at`,`updated_at`,`need_reset`,`sid`) VALUES (:passwd,0,0,:transfer_enable,:port,UNIX_TIMESTAMP(),0,:need_reset,:sid)';
	$query['ALREADY_EXISTS'] = 'SELECT `port` FROM `user` WHERE `sid` = :sid';
	$query['ENABLE'] = 'UPDATE `user` SET `enable` = :enable WHERE `sid` = :sid';
	$query['DELETE_ACCOUNT'] = 'DELETE FROM `user` WHERE `sid` = :sid';
	$query['CHANGE_PASSWORD'] = 'UPDATE `user` SET passwd = :passwd WHERE `sid` = :sid';
	$query['USERINFO'] = 'SELECT `id`,`passwd`,`port`,`t`,`u`,`d`,`transfer_enable`,`enable`,`created_at`,`updated_at`,`need_reset`,`sid` FROM `user` WHERE `sid` = :sid';
	$query['CHANGE_PACKAGE'] = 'UPDATE `user` SET `transfer_enable` = :transfer_enable WHERE `sid` = :sid';
	$query['RESETUSERCHART'] = 'delete from `user_usage` where `sid` = :sid';
	if($date){
		$query['RESET'] = 'UPDATE `user` SET `u`=0,`d`=0,`updated_at`='.$date.'  WHERE `sid` = :sid';
		$query['CHARTINFO'] = 'SELECT * FROM `user_usage` WHERE `sid` = :sid AND `date` >= '.$date.' ORDER BY `date` DESC';
	}else{
		$query['RESET'] = 'UPDATE `user` SET `u`=0,`d`=0 WHERE `sid` = :sid';
		$query['CHARTINFO'] = 'SELECT * FROM `user_usage` WHERE `sid` = :sid ORDER BY `date` DESC';
	}
	return $query;
}

function convert($number, $from, $to){
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

function UnlimitedSocks_MetaData(){
	return array(
		'DisplayName' => 'UnlimitedSocks', 
		'RequiresServer' => true,
		);
}

function UnlimitedSocks_ConfigOptions(){
	return array(
	get_lang('database') => array('Type' => 'text', 'Size' => '25'),
	get_lang('resetbandwidth') => array(
		'Type'        => 'dropdown',
		'Options'     => array('1' => get_lang('need_reset'), '0' => get_lang('neednot_reset')),
		'Description' => get_lang('resetbandwidth_description')
		),
	get_lang('bandwidth') => array('Type' => 'text', 'Size' => '25', 'Description' => get_lang('bandwidth_description')),
	get_lang('start_port') => array('Type' => 'text', 'Size' => '25', 'Description' => get_lang('start_port_description')),
	get_lang('routelist') => array('Type' => 'textarea', 'Rows' => '3', 'Cols' => '50', 'Description' => get_lang('routelist_description')),
	get_lang('ping_test') => array(
		'Type'        => 'dropdown',
		'Options'     => array('2' => get_lang('test_user'), '1' => get_lang('test_server'), '0' => get_lang('do_not_test')),
		'Description' => get_lang('ping_test_description')
		),
	);
}

function UnlimitedSocks_TestConnection(array $params){
	try {
		$dbhost = $params['serverip'];
		$dbuser = $params['serverusername'];
		$dbpass = $params['serverpassword'];
		$db = new PDO('mysql:host=' . $dbhost, $dbuser, $dbpass);
		$success = true;
		$errorMsg = '';
	}
	catch (Exception $e) {
		logModuleCall('UnlimitedSocks', 'UnlimitedSocks_TestConnection', $params, $e->getMessage(), $e->getTraceAsString());
		$success = false;
		$errorMsg = $e->getMessage();
	}
	return array('success' => $success, 'error' => $errorMsg);
}

function UnlimitedSocks_RandomPass($length = 8){
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|'; 
	$password = ''; 
	for ( $i = 0; $i < $length; $i++ ) 
	{ 
		$password .= $chars[ mt_rand(0, strlen($chars) - 1) ]; 
	} 
	return $password; 
}

function UnlimitedSocks_CreateAccount(array $params){
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
			return get_lang('User_already_exists');
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
		if($params['customfields']['password'] != ""){$pass = $params['customfields']['password'];}else{$pass =UnlimitedSocks_RandomPass();};
		$create->bindValue(':passwd', $pass);
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
		logModuleCall('UnlimitedSocks', 'UnlimitedSocks_CreateAccount', $params, $e->getMessage(), $e->getTraceAsString());
		return get_lang('Model_error').$e->getMessage();
	}
}

function UnlimitedSocks_SuspendAccount(array $params){
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
		logModuleCall('UnlimitedSocks', 'UnlimitedSocks_SuspendAccount', $params, $e->getMessage(), $e->getTraceAsString());
		return $e->getMessage();
	}
}

function UnlimitedSocks_UnsuspendAccount(array $params){
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
		logModuleCall('UnlimitedSocks', 'UnlimitedSocks_UnsuspendAccount', $params, $e->getMessage(), $e->getTraceAsString());
		return $e->getMessage();
	}
}

function UnlimitedSocks_TerminateAccount(array $params){
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
			return get_lang('User_does_not_exists');
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
		logModuleCall('UnlimitedSocks', 'UnlimitedSocks_TerminateAccount', $params, $e->getMessage(), $e->getTraceAsString());
		return $e->getMessage();
	}
}

function UnlimitedSocks_ChangePackage(array $params){
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
		logModuleCall('UnlimitedSocks', 'UnlimitedSocks_ChangePackage', $params, $e->getMessage(), $e->getTraceAsString());
		return $e->getMessage();
	}
}

function UnlimitedSocks_ChangePassword(array $params){
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
		logModuleCall('UnlimitedSocks', 'UnlimitedSocks_ChangePassword', $params, $e->getMessage(), $e->getTraceAsString());
		return $e->getMessage();
	}
}

function UnlimitedSocks_AdminCustomButtonArray(){
	return array(get_lang('resetbandwidth') => 'ResetBandwidth');
}

function UnlimitedSocks_ResetBandwidth(array $params){
	$query = initialize($params,time());
	try {
		$dbhost = ($params['serverip']);
		$dbname = ($params['configoption1']);
		$dbuser = ($params['serverusername']);
		$dbpass = ($params['serverpassword']);
		$db = new PDO('mysql:host=' . $dbhost . ';dbname=' . $dbname, $dbuser, $dbpass);
		$enable = $db->prepare($query['RESET']);
		$enable->bindValue(':sid', $params['serviceid']);
		$todo = $enable->execute();
		$resetchart = $db->prepare($query['RESETUSERCHART']);
		$resetchart->bindValue(':sid', $params['serviceid']);
		$resetchart->execute();
		if (!$todo) {
			$error = $db->errorInfo();
			return $error;
		}
		return 'success';
	}
	catch (Exception $e) {
		logModuleCall('UnlimitedSocks', 'UnlimitedSocks_ResetBandwidth', $params, $e->getMessage(), $e->getTraceAsString());
		return $e->getMessage();
	}
}

function UnlimitedSocks_ClientArea(array $params){
	require_once 'Mobile_Detect.php';
	$detect = new Mobile_Detect;
	if($detect->isMobile()){
		$date = time() - 60*60*24;
		$datadays = 1;
	}else{
		$date = time() - 60*60*24*3;
		$datadays = 3;
	}
	$query = initialize($params,$date);
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
		
		$chartinfo = $db->prepare($query['CHARTINFO']);
		$chartinfo->bindValue(':sid', $params['serviceid']);
		$chartinfo->execute();
		if($chartinfo){
			$exa = array();
			foreach($chartinfo as $chart){
				$exa[] = $chart;
			}
			$label = "";
			$total = "";
			$upload = "";
			$download = "";
			$chartinfo = array_reverse($exa,true);
			foreach($chartinfo as $chart){
				$label .= "'"."',";
				//$label .= "'".date('m/d  H:i',$chart['date'])."',";
				$upload .= number_format(convert($chart['upload'], 'bytes', 'mb'), 2, '.', '').",";
				$download .= number_format(convert($chart['download'], 'bytes', 'mb'), 2, '.', '').",";
				$total .= number_format(convert($chart['upload']+$chart['download'], 'bytes', 'mb'), 2, '.', '').",";
			}
			$label = substr($label,0,strlen($label)-1);
			$total = substr($total,0,strlen($total)-1);
			$upload = substr($upload,0,strlen($upload)-1);
			$download = substr($download,0,strlen($download)-1);
			$script = make_script("totalc",$label,$total);
			$script .= make_script("uploadc",$label,$upload);
			$script .= make_script("downloadc",$label,$download);
		}
		
		$nodes = $params['configoption5'];
		$results = array();
		$pingresults = array();
		$z = 0;
		
		$noder = explode("\n",$nodes);
		$x = 0;
		foreach($noder as $nodee){
			$nodee = explode('|', $nodee);
			$y = 0;
			$ress = array();
			foreach($nodee as $nodet){
				$ress[$y] = $nodet;
				$y ++;
				if($y == 2 and !$detect->isMobile() and $params['configoption6'] == 1){
					$res = ping_Server($nodet,$usage['port']);
					$pingresults[$z] = $res;
					$z ++;
				}
			}
			$results[$x] = $ress;
			$x++;
		}
		
		$user = array('passwd' => $usage['passwd'], 'port' => $usage['port'], 'u' => $usage['u'], 'd' => $usage['d'], 't' => $usage['t'], 'sum' => $usage['u'] + $usage['d'], 'transfer_enable' => $usage['transfer_enable'], 'created_at' => $usage['created_at'], 'updated_at' => $usage['updated_at']);
		if ($usage && $usage['enable']) {
			return array(
			'tabOverviewReplacementTemplate' => 'details.tpl',
			'templateVariables'              => array('usage' => $user, 'params' => $params, 'nodes' => $results ,'script' => $script ,'datadays' => $datadays,'nowdate' => date('m/d  H:i',time()),'pings' =>$pingresults,'pingoption' => $params['configoption6'])
			);
		}
		return array(
		'tabOverviewReplacementTemplate' => 'error.tpl',
		'templateVariables'              => array('usefulErrorHelper' => get_lang('error_Service_Disable'))
		);
	}
	catch (Exception $e) {
		logModuleCall('UnlimitedSocks', 'UnlimitedSocks_ClientArea', $params, $e->getMessage(), $e->getTraceAsString());
		return array(
	'tabOverviewReplacementTemplate' => 'error.tpl',
	'templateVariables'              => array('usefulErrorHelper' => get_lang('Model_error').$e->getMessage())
	);
	}
}

function make_script($name,$label,$data){
	if($name and $label and $data){
		$script = "
			var canvas=document.getElementById('".$name."');
			var data = {
				labels : [".$label."],
				datasets : [
					{
						fillColor : 'rgba(220,220,220,0.5)',
						strokeColor : 'rgba(220,220,220,1)',
						pointColor : 'rgba(220,220,220,1)',
						pointStrokeColor : '#fff',
						data : [".$data."]
					},
				]
			}
			var ctx = canvas.getContext('2d');
			var myLine = new Chart(ctx).Line(data,{
			responsive: true,
			scaleLabel: '<%=value%>MB'});";
		return $script;
	}
}

function UnlimitedSocks_AdminServicesTabFields(array $params){
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
			return array(get_lang('port') => $userinfo['port'], get_lang('bandwidth') => convert($userinfo['transfer_enable'], 'bytes', 'mb') . 'MB', get_lang('upload') => round(convert($userinfo['u'], 'bytes', 'mb')) . 'MB', get_lang('download') => round(convert($userinfo['d'], 'bytes', 'mb')) . 'MB', get_lang('used') => round(convert($userinfo['d'] + $userinfo['u'], 'bytes', 'mb')) . 'MB', get_lang('last_use_time') => date('Y-m-d H:i:s', $userinfo['t']), get_lang('last_reset_time') => date('Y-m-d H:i:s', $userinfo['updated_at']));
		}
	}
	catch (Exception $e) {
		logModuleCall('UnlimitedSocks', 'UnlimitedSocks_AdminServicesTabFields', $params, $e->getMessage(), $e->getTraceAsString());
		return $e->getTraceAsString();
	}
}

function multi_language_support(){
	global $_LANG;
	$dir = realpath(dirname(__FILE__) . "/lang");
	if(isset($GLOBALS['CONFIG']['Language']) ){
		$language = $GLOBALS['CONFIG']['Language'];
	}
	if(isset($_SESSION['adminid'])){
		$language = _getUserLanguage('tbladmins', 'adminid');
	}elseif( $_SESSION['uid'] ){
		$language = _getUserLanguage('tblclients', 'uid');
	}
	if(!$language){
		$language = "english";
	}
	$file = $dir.'/'.$language.".php";
	if(file_exists($file)){
		include($file);
	}
	return $file;
}

function _getUserLanguage($table, $field){
	$sqlresult = select_query($table, 'language', array( 'id' => mysql_real_escape_string($_SESSION[$field])));
	if($data = mysql_fetch_row($sqlresult)){
		return reset($data);
	}
	return false;
}

function get_lang($var){
	global $_LANG;
	return isset($_LANG[$var]) ? $_LANG[$var] : $var . '(Missing Language)' ;
}

function ping_Server($host,$port) {
	$time_start = microtime_float();
	$ip = gethostbyname($host);
	$fp = @fsockopen($host,$port,$errno,$errstr,1);
	if(!$fp) return 'Request timed out.'."\r\n";
	$get = "GET / HTTP/1.1\r\nHost:".$host."\r\nConnection: Close\r\n\r\n";
	@fputs($fp,$get);
	@fclose($fp);
	$time_end = microtime_float();
	$time = $time_end - $time_start;
	$time = ceil($time * 1000);
	return $time;
}

function microtime_float(){
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}
?>

















