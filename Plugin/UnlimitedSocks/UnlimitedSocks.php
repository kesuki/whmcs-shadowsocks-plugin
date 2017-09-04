<?php
if(!defined("WHMCS")){
  die("This file cannot be accessed directly");
}
require_once 'CardConfig.php';
require_once 'LangConfig.php';

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
    $query['GETUSINGCARDS'] = 'SELECT * FROM `card_usage` WHERE `sid` = :sid ORDER BY `duedate` DESC';
    $query['QUERYCARD'] = 'SELECT * FROM `cards` WHERE `number` = :card AND `cardstatus` = 1';
    $query['INSERTCARD'] = 'INSERT INTO `card_usage`(`sid`,`enable`,`card`,`traffic`,`duedate`) VALUES (:sid,1,:card,:traffic,:duedate)';
    $query['UPDATECARD'] = 'UPDATE `cards` SET `cardstatus` = 0 WHERE `cardid` = :cardid';
    $query['UPDATEACCOUNT'] = 'UPDATE `cards` SET `cardstatus` = 0 WHERE `cardid` = :cardid';
    $query['UPDATEBALANCE'] = 'UPDATE `user` SET `transfer_enable` = `transfer_enable` + :transfer WHERE `sid` = :sid';
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
		'Options'     => array('3'=> get_lang('end_of_month'), '2'=> get_lang('start_of_month'), '1' => get_lang('by_duedate_day'), '0' => get_lang('neednot_reset')),
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
	get_lang('announcements') => array('Type' => 'textarea', 'Rows' => '3', 'Cols' => '50', 'Description' => get_lang('announcements_description')),	
    get_lang('card_enable') => array(
		'Type'        => 'dropdown',
		'Options'     => array('1' => get_lang('enable'), '0' => get_lang('disable')),
		'Description' => get_lang('card_enable_description')
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
				$port = (!empty($params['configoption4']) ? $params['configoption4'] : '10000');
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
	$query = initialize($params,time());
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
        $usedcards = false;
        $useddcard = false;
        if(Card_Enable){
            $usedcards = UnlimitedSocks_GetUsingCards($params);
            if($usedcards){
                $uuu = array();
                foreach($usedcards as $usedcard){
                    if($usedcard['enable'] == 1){
                        $uuu[] = array(
                            'card' => $usedcard['card'],
                            'traffic' => UnlimitedSocks_MBGB($usedcard['traffic']),
                            'duedate' => date('Y-m-d', $usedcard['duedate']));
                    }else{
                        $useddcard[] = array(
                            'card' => $usedcard['card'],
                            'traffic' => UnlimitedSocks_MBGB($usedcard['traffic']),
                            'duedate' => date('Y-m-d', $usedcard['duedate']));
                    }
                }
                $usedcards = $uuu;
            }
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
			$b64 = makeb64($ress,$usage['port'],$usage['passwd']);
			$results[$x] = $b64;
			$x++;
		}
		$infos = $params['configoption7'] ? $params['configoption7'] : false;
		$user = array('passwd' => $usage['passwd'], 
                      'port' => $usage['port'], 
                      'u' => $usage['u'], 
                      'd' => $usage['d'], 
                      't' => $usage['t'], 
                      'sum' => $usage['u'] + $usage['d'], 
                      'transfer_enable' => $usage['transfer_enable'], 
                      'created_at' => $usage['created_at'], 
                      'updated_at' => $usage['updated_at'],
                      'tr_MB_GB' => UnlimitedSocks_MBGB($usage['transfer_enable']/1048576),
                      's_MB_GB' => UnlimitedSocks_MBGB(round(($usage['u'] + $usage['d'])/1048576,2)),
                      'u_MB_GB' => UnlimitedSocks_MBGB(round($usage['u']/1048576,2)),
                      'd_MB_GB' => UnlimitedSocks_MBGB(round($usage['d']/1048576,2)));
		if ($usage && $usage['enable']) {
			return array(
			'tabOverviewReplacementTemplate' => 'details.tpl',
			'templateVariables'              => array(
                                                    'usage' => $user,  
                                                    'params' => $params, 
                                                    'nodes' => $results,
                                                    'script' => $script,
                                                    'datadays' => $datadays,
                                                    'nowdate' => date('m/d  H:i',time()),
                                                    'pings' =>$pingresults,
                                                    'pingoption' => $params['configoption6'],
                                                    'infos' => $infos,
                                                    'usingcards' => $usedcards,
                                                    'usedcards' => $useddcard)
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

function UnlimitedSocks_MBGB($tra){
    if($tra >= 1024){
        $tra = round($tra / 1024,2);
        $tra .= 'GB';
    }else{
        $tra .= 'MB';
    }
    return $tra;
}

function UnlimitedSocks_GetUsingCards($params){
    $query = initialize($params);
    try {
        $dbhost = $params['serverip'];
        $dbname = $params['configoption1'];
        $dbuser = $params['serverusername'];
        $dbpass = $params['serverpassword'];
        $db = new PDO('mysql:host=' . $dbhost . ';dbname=' . $dbname, $dbuser, $dbpass);
        $usage = $db->prepare($query['GETUSINGCARDS']);
        $usage->bindValue(':sid', $params['serviceid']);
        $usage->execute();
        $usage = $usage->fetchAll();
        return $usage;
    }
    catch (Exception $e) {
		return false;
	}  
}

function UnlimitedSocks_ClientAreaCustomButtonArray(){
    if(Card_Enable){
        if(UnlimitedSocks_TestCardConnection()){      
            $buttonarray = array( get_lang('additional_bandwidth') => 'AdditionalBandwidth');
            return $buttonarray;
        }
    }
}

function UnlimitedSocks_AdditionalBandwidth($params){
    if(Card_Enable and $params['configoption8'] == 1){
        if(UnlimitedSocks_TestCardConnection()){
            if($_REQUEST['cardid']){
               $carduse = UnlimitedSocks_UseCard($params,$_REQUEST['cardid']);
               if($carduse){
                   switch($carduse){
                      case 'success':
                        $infos = get_lang('use_card_success');
                      break;
                      default:
                        $errors = get_lang($carduse);
                      break;
                   }                      
               }
           }else{
               $errors = get_lang('no_card_insert');
           }  
        }
        return array(
            'templatefile' => 'templates/card',
            'templateVariables'  => array(
                'infos' => $infos,
                'errors' => $errors,
                )
            );
    }
    return array(
            'templatefile' => 'templates/error',
            'templateVariables'  => array(
                'usefulErrorHelper' => get_lang('card_disable'),
                )
            );
}

function UnlimitedSocks_UseCard($params,$card){
    if(!Card_Enable){
        return false;
    }
    $card = check_input($card);
    if(!preg_match("/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$card)){
        $query = initialize($params);
        $dbhost = $params['serverip'];
		$dbname = $params['configoption1'];
		$dbuser = $params['serverusername'];
		$dbpass = $params['serverpassword'];
		$db = new PDO('mysql:host=' . Card_DB_HOST . ';dbname=' . Card_DB_NAME, Card_DB_USER, Card_DB_PASS);
		$usage = $db->prepare($query['QUERYCARD']);//查询卡是否存在
		$usage->bindValue(':card', $card);
		$usage->execute();
		$usage = $usage->fetch();
        if($usage){
            $traffic = $usage['traffic'];
            $cardid = $usage['cardid'];
            $availabletime = $usage['availabletime'];
            $duedate = mktime(0,0,0,date("m"),date("d"),date("y")) + 60 * 60 * 24 * $availabletime;
            $card = $usage['number'];
            $dbo = new PDO('mysql:host=' . $dbhost . ';dbname=' . $dbname, $dbuser, $dbpass);
            $usageo = $dbo->prepare($query['INSERTCARD']);//输入进ss数据库
            $usageo->bindValue(':sid', $params['serviceid']);
            $usageo->bindValue(':card', $card);
            $usageo->bindValue(':traffic', $traffic);
            $usageo->bindValue(':duedate', $duedate);
            $usageo->execute();
            if($usageo){
                $usageo = $db->prepare($query['UPDATECARD']);//设置卡已用
                $usageo->bindValue(':cardid', $cardid);
                $usageo->execute();
                if($usageo){
                    $transfer = convert($traffic, 'mb', 'bytes');
                    $usageo = $dbo->prepare($query['UPDATEBALANCE']);//输入进ss数据库
                    $usageo->bindValue(':sid', $params['serviceid']);
                    $usageo->bindValue(':transfer', $transfer);
                    $usageo->execute();
                    if($usageo){
                        return 'success';
                    }
                    return 'user_main_database_ERROR';
                }
                return 'card_database_ERROR';
            }
            return 'user_database_ERROR';
        }
    }
    return 'card_unisset_or_unillegal';
}

function check_input($data){
    //对特殊符号添加反斜杠
    $data = addslashes($data);
    //判断自动添加反斜杠是否开启
    if(get_magic_quotes_gpc()){
        //去除反斜杠
        $data = stripslashes($data);
    }
    //把'_'过滤掉
    $data = str_replace("_", "\_", $data);
    //把'%'过滤掉
    $data = str_replace("%", "\%", $data);
    //把'*'过滤掉
    $data = str_replace("*", "\*", $data);
    //回车转换
    $data = nl2br($data);
    //去掉前后空格
    $data = trim($data);
    //将HTML特殊字符转化为实体
    $data = htmlspecialchars($data);
    return $data;
}
    
function UnlimitedSocks_TestCardConnection(){
    try {
		$db = new PDO('mysql:host=' . Card_DB_HOST, Card_DB_USER, Card_DB_PASS);
		$success = true;
		$errorMsg = '';
	}
	catch (Exception $e) {
        return false;
	}
    return true;
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
		$language = Default_Lang;
	}
	$file = $dir.'/'.$language.".php";
	if(file_exists($file)){
		include($file);
	}else{
        $file = $dir.'/'.Default_Lang.'.php';
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

function makeb64($node,$port,$pass){
	//ssr ip:port:protocol:method:obfs:b64pass/?obfsparam=xxx&protoparam=xxx&remarks=xxx
	//ss data-params-SS="{$node[2]}:{$usage.passwd}@{$node[1]}:{$usage.port}"
	//0 remark
	//1 ip
	//2 method
	//3 protocol
	//4 protocolparam
	//5 obfs
	//6 obfsparam
	//7 type
	if(strstr($node[7], 'ss&ssr')){
        $node[] = array(
            'ss' => make_ss($node,$pass,$port),
            'ssr' => make_ssr($node,$pass,$port),
        );
    }elseif(strstr($node[7], 'ssr')){
        $node[] = make_ssr($node,$pass,$port);
	}else{
		$node[] = make_ss($node,$pass,$port);
	}
	return $node;
}

function make_ssr($node,$pass,$port){
    $ssrs = "";
    $pass = str_replace('=','',base64_encode($pass));
    $ssrs = $node[1].":".$port.":".$node[3].":".$node[2].":".$node[5].":".$pass;
    if($node[0] or $node[4] or $node[6]){
        $ssrs .= "/?";
        $ssrs .= "obfsparam=";
        if($node[4]){
            $data = str_replace('=','',base64_encode($node[4]));
            $ssrs .= $data;
        }
        $ssrs .= "&protoparam=";
        if($node[6]){
            $data = str_replace('=','',base64_encode($node[6]));
            $ssrs .= $data;
        }
        $ssrs .= "&remarks=";
        if($node[0]){
            $data = str_replace('=','',base64_encode($node[0]));
            $ssrs .= $data;
        }
    }
    $data = base64_encode($ssrs);
    $data = "ssr://".str_replace('=','',$data);
    return $data;  
}

function make_ss($node,$pass,$port){
    $sss = $node[2].":".$pass."@".$node[1].":".$port;
    $sss = "ss://".base64_encode($sss);
    if($node[0]){
        $sss .= "#".$node[0];
    }
    return $sss;
}
?>

















