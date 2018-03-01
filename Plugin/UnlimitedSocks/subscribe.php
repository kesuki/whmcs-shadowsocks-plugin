<?php
require(dirname(dirname(dirname(dirname(__FILE__)))).'/init.php');
use WHMCS\Database\Capsule;
if(isset($_GET['sid']) && isset($_GET['token'])){
	$sid = $_GET['sid'];
	$token = $_GET['token'];
	$service = \WHMCS\Database\Capsule::table('tblhosting')->where('id', $sid)->where('username', $token)->first();
	if (empty($service)){
		die('Unisset or Uncorrect Token');
	}
	if ($service->domainstatus != 'Active' ) {
        die('Not Active');
    }
	$package = Capsule::table('tblproducts')->where('id', $service->packageid)->first();
	$server = Capsule::table('tblservers')->where('id', $service->server)->first();

	$dbhost = $server->ipaddress ? $server->ipaddress : 'localhost';
	$dbname = $package->configoption1;
	$dbuser = $server->username;
	$dbpass = decrypt($server->password);
	$db = new PDO('mysql:host=' . $dbhost . ';dbname=' . $dbname, $dbuser, $dbpass);
	$usage = $db->prepare('SELECT `id`,`passwd`,`port`,`t`,`u`,`d`,`transfer_enable`,`enable`,`created_at`,`updated_at`,`need_reset`,`sid` FROM `user` WHERE `sid` = :sid');
	$usage->bindValue(':sid', $sid);
	$usage->execute();
	$usage = $usage->fetch();
	$servers = $package->configoption5;
	$noder = explode("\n",$servers);
	$results = "";
	foreach($noder as $nodee){
		$nodee = explode('|', $nodee);
        if(!strstr($nodee[7], 'stop')){
			if(strstr($nodee[7], 'ss&ssr') or strstr($nodee[7], 'ssr')){
				$results .= make_sssr($nodee,$usage['passwd'],$usage['port'],$package->name).PHP_EOL;
			}
        }
	}
	echo(str_replace('=','',base64_encode($results)));

}else{
	die('Invaild');
}

function make_sssr($node,$pass,$port,$group = null){
    $ssrs = "";
    $pass = str_replace('=','',base64_encode($pass));
    $ssrs = $node[1].":".$port.":".$node[3].":".$node[2].":".$node[5].":".$pass;
    if($node[0] or $node[4] or $node[6]){
        $ssrs .= "/?";
        if($node[4]){
        	$ssrs .= "protoparam=";
            $data = str_replace('=','',base64_encode($node[4]));
            $ssrs .= $data;
            $need = 1;
        }
        if($node[6]){
        	if($need){
        		$ssrs .= "&";
        	}
        	$ssrs .= "obfsparam=";
            $data = str_replace('=','',base64_encode($node[6]));
            $ssrs .= $data;
            $need = 1;
        }
        if($node[0]){
        	if($need){
        		$ssrs .= "&";
        	}
       		$ssrs .= "remarks=";
            $data = str_replace('=','',base64_encode($node[0]));
            $ssrs .= $data;
            $need = 1;
        }
        if($group){
        	if($need){
        		$ssrs .= "&";
        	}
       		$ssrs .= "group=";
            $data = str_replace('=','',base64_encode($group));
            $ssrs .= $data;
        }
    }
    $data = base64_encode($ssrs);
    $data = "ssr://".str_replace('=','',$data);
    return $data;  
}
