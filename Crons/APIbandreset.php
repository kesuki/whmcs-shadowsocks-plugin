<?
define('DB_NAME', '');//数据库名字
define('DB_USER', '');//用户名
define('DB_PASS', '');//密码
define('DB_HOST', 'localhost');//IP

define('api_domain','127.0.0.1/includes/api.php');//API
define('api_name','');//adminname
define('api_pass','');//adminpass

function API_call($array){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, api_domain);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,
		http_build_query(
			$array
		)
	);
	$response = curl_exec($ch);
	curl_close($ch);
	return $response;
}

function get_client_products($id = null){
	$array = array(
				'action' => 'GetClientsProducts',
				'username' => api_name,
				'password' => md5(api_pass),
				'clientid' => $id,
				'stats' => true,
				'responsetype' => 'json',
			 );
	return API_call($array);
}

function resetband($id){
	$mysql = new mysqli(DB_HOST, DB_USER, DB_PASS , DB_NAME);
	if(!$mysql) {
	  die('Unable to connect to database.');
	} else {
	  $mysql->query("UPDATE `user` SET `u` = '0', `d` = '0' where `need_reset` = 1 AND `sid` = ".$id);
	  $mysql->query("delete from `user_usage` WHERE `sid` = ".$id);
	  echo("ID:".$id." Has been reset</br>");
	}
}

$products = json_decode(get_client_products(),true);
if($products['result'] == "success"){
	$products = $products['products']['product'];
	$product = array();
	foreach($products as $pro){
		if(strtotime($pro['nextduedate']) >= time()){//out of date or not|判断是否过期
			if(date("d", strtotime($pro['nextduedate'])) == date('d')){
				$sid = $pro['id'];
				resetband($sid);
			}
		}
	}
}else{
	die("Cann't Access to API(GetClientsProducts)");
}




