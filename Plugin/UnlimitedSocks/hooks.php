<?php
use WHMCS\Database\Capsule;
add_hook('AfterCronJob', 1, function(){
	try {
		$query = \WHMCS\Database\Capsule::table('tblproducts')->where('servertype', 'UnlimitedSocks')->get();
	    $query2 = \WHMCS\Database\Capsule::table('tblhosting')->get();
	    $query3 = \WHMCS\Database\Capsule::table('tblservers')->where('type', 'UnlimitedSocks')->get();
		$products = UnlimitedSocks_QueryToArray($query);
	    $clients = UnlimitedSocks_QueryToArray($query2);
	    $servers = UnlimitedSocks_QueryToArray($query3);
	    $pids = UnlimitedSocks_prase_pid($products);
	    $pro = UnlimitedSocks_get_client_products_with_pids($clients,$pids,array('Active','Suspended'));
	    $pro = UnlimitedSocks_update_network($pro,$servers,UnlimitedSocks_prase_product_DB($products),$products);
	}catch (Exception $e){
	}
});

add_hook('DailyCronJob', 1, function() {
	try {
		$query = \WHMCS\Database\Capsule::table('tblproducts')->where('servertype', 'UnlimitedSocks')->get();
	    $query2 = \WHMCS\Database\Capsule::table('tblhosting')->get();
	    $query3 = \WHMCS\Database\Capsule::table('tblservers')->where('type', 'UnlimitedSocks')->get();
		$products = UnlimitedSocks_QueryToArray($query);
	    $clients = UnlimitedSocks_QueryToArray($query2);
	    $servers = UnlimitedSocks_QueryToArray($query3);
	    $pids = UnlimitedSocks_prase_pid($products);
	    $pro = UnlimitedSocks_get_client_products_with_pids($clients,$pids,array('Active','Suspended'));
	    $rproducts = UnlimitedSocks_RebuildProductArray($products);
	    $pro = UnlimitedSocks_CalcBandReset($pro,$rproducts,$servers);
	}catch (Exception $e){
	}
});

function UnlimitedSocks_QueryToArray($query){
    $products = array();
    foreach ($query as $product) {
        $producta = array();
        foreach($product as $k => $produc){
            $producta[$k] = $produc;
        }
        $products[] = $producta;
    }
    return $products;
}

function UnlimitedSocks_RebuildProductArray($query){
    $products = array();
    foreach ($query as $product) {
        $products[$product['id']] = $product;
    }
    return $products;
}

function UnlimitedSocks_prase_pid($products,$module = 'UnlimitedSocks'){
    $product = array();
    foreach($products as $pro){
        if($pro['servertype'] == $module){
            $product[] = $pro['id'];
        }
    }
    return $product;
}

function UnlimitedSocks_get_client_products_with_pids($products,$pids,$status = array('Active')){
	$product = array();
	foreach($products as $pro){
		if(in_array($pro['packageid'],$pids) && in_array($pro['domainstatus'],$status)){
			$product[] = $pro;
		}
	}
	return $product;
}

function UnlimitedSocks_update_network($products,$server,$whproduct,$oldproducts){
    foreach($server as $ser){
        $mysql = new mysqli($ser['ipaddress'], $ser['username'], decrypt($ser['password']));
        $servername = 'mysqlserver'.$ser['id'];
        $$servername = $mysql;
    }
    $product = array();
    foreach($products as $pro){
        $sid = $pro['server'];
        $mysql = 'mysqlserver'.$sid;
        $sql = $$mysql;
        $sql->select_db($whproduct[$pro['packageid']]);
        $sqlq = "SELECT * FROM `user` WHERE sid = " . $pro['id'];
        $ssacc = mysqli_fetch_array($sql->query($sqlq),MYSQLI_ASSOC);
        $uasql = "SELECT * FROM `user_usage` WHERE sid = " . $pro['id'] ." ORDER BY `date` DESC LIMIT 1";
        $usagee = mysqli_fetch_array($sql->query($uasql),MYSQLI_ASSOC);
        $writeable = false;
        if(empty($usagee)){
            $writeable = true;
        	$dataa = $ssacc['u'].",".$ssacc['d'].",".$ssacc['u'].",".$ssacc['d'].",".time().",".$pro['id'];
        }else{
            $writeable = false;
            if(time() - $usagee['date'] >= 60 * 60 * 3){
                $writeable = true;
                $dataa = ($ssacc['u'] - $usagee['tupload']).",".($ssacc['d'] - $usagee['tdownload']).",".$ssacc['u'].",".$ssacc['d'].",".time().",".$pro['id'];
            }
        }
        if($writeable){
            $upmysql = "INSERT INTO `user_usage` (`upload`,`download`,`tupload`,`tdownload`,`date`,`sid`) VALUES(".$dataa.")";
            $sql->query($upmysql);
        }
    }
    return $product;
}

function UnlimitedSocks_prase_product_DB($products,$module = 'UnlimitedSocks'){
    $product = array();
    foreach($products as $pro){
        if($pro['servertype'] == $module){
            $product[$pro['id']] = $pro['configoption1'];
        }
    }
    return $product;
}

function UnlimitedSocks_CalcBandReset($pro,$products,$server){
	foreach($server as $ser){
        $mysql = new mysqli($ser['ipaddress'], $ser['username'], decrypt($ser['password']));
        $servername = 'mysqlserver'.$ser['id'];
        $$servername = $mysql;
    }
    $product = array();
	foreach($pro as $por){	
        $sid = $por['server'];
        $mysql = 'mysqlserver'.$sid;
        $sql = $$mysql;
        $sql->select_db($products[$por['packageid']]['configoption1']);
		$days = UnlimitedSocks_daysInmonth(date('y'),date('m'));
		UnlimitedSocks_calcreset($por,$products[$por['packageid']],$days,$sql);
    }
}

function UnlimitedSocks_resetband($id,$sqlserver){
    $sqlserver->query("UPDATE `user` SET `u` = '0', `d` = '0' where `sid` = ".$id);
    $sqlserver->query("DELETE from `user_usage` WHERE `sid` = ".$id);
    echo("ID:".$id." Has been reset</br>");
}

function UnlimitedSocks_daysInmonth($year='',$month=''){  
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

function UnlimitedSocks_calcreset($product,$whmcs,$day,$sqlserver){
    switch($whmcs['configoption2']){
        case 0:
            break;
        case 1:
            if(date("d", strtotime($product['nextduedate'])) == date('d')){
                UnlimitedSocks_resetband($product['id'],$sqlserver);
            }
            if(date('d') == $day){
                if(date("d", strtotime($product['nextduedate'])) > $day){
                    UnlimitedSocks_resetband($product['id'],$sqlserver);
                } 
            }
            break;
        case 2:
            if(date('d') == 1){
                UnlimitedSocks_resetband($product['id'],$sqlserver);
            }
            break;
        case 3:
            if(date('d') == $day){
                UnlimitedSocks_resetband($product['id'],$sqlserver);
            }
            break;
    }
}
?>