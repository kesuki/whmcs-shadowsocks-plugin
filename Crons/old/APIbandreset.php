<?php
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
	  $mysql->query("UPDATE `user` SET `u` = '0', `d` = '0' where `sid` = ".$id);
	  $mysql->query("delete from `user_usage` WHERE `sid` = ".$id);
	  echo("ID:".$id." Has been reset</br>");
	}
}

function daysInmonth($year='',$month=''){  
    if(empty($year)) $year = date('Y');  
    if(empty($month)) $month = date('m');  
    if (in_array($month, array(1, 3, 5, 7, 8, '01', '03', '05', '07', '08', 10, 12))) {    
            $text = '31';        //月大  
    }elseif ($month == 2 || $month == '02'){    
        if ( ($year % 400 == 0) || ( ($year % 4 == 0) && ($year % 100 !== 0) ) ) {   //判断是否是闰年    
            $text = '29';        //闰年2月  
        } else {    
            $text = '28';        //平年2月  
        }    
    } else {    
        $text = '30';            //月小  
    }  
      
    return $text;  
}  

function resetd($id){
    resetband($id); 
}

$products = json_decode(get_client_products(),true);
if($products['result'] == "success"){
	$products = $products['products']['product'];
	$product = array();
	foreach($products as $pro){
        if($pro['status'] == "Active"){
            $days = daysInmonth(date('y'),date('m'));
            if(date("d", strtotime($pro['nextduedate'])) == date('d')){
                resetd($pro['id']);
            }
            if(date('d') == $days){
                if(date("d", strtotime($pro['nextduedate'])) > $days){
                    resetd($pro['id']);
                } 
            }
		}
	}
}else{
	die("Cann't Access to API(GetClientsProducts)");
}




