<?
function admin_login($POST){
	$array = array(
				'username' => $POST['username'],
				'password' => md5($POST['password']),
				'action' => 'GetAdminDetails',
				'responsetype' => 'json',
			 );
	$result = json_decode(API_call($array),true);
	if($result['result'] == 'success' && isset($result['adminid']) && isset($result['name'])){
		admin_logined($POST['username'],md5($POST['password']),$result);
		return true;
	}
	return false;
}

function admin_logined($user,$md5pass,$res){
	if(Admin_is_login()){
		Admin_quit();
		session_start();
	}
	$_SESSION['adminname'] = $user;
	$_SESSION['adminpass'] = $md5pass;
	$_SESSION['adminusername'] = $res['name'];
	$_SESSION['adminuid'] = $res['adminid'];
	return true;
}