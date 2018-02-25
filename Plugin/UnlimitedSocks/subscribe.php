<?php
require(dirname(dirname(dirname(dirname(__FILE__)))).'/init.php');
require('UnlimitedSocks.php');
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
	$usage = $db->prepare($query['USERINFO']);
	$usage->bindValue(':sid', $params['serviceid']);
	$usage->execute();
	$usage = $usage->fetch();

	$servers = $package->configoption5;
	$noder = explode("\n",$servers);
	$x = 0;
	$results = "";
	foreach($noder as $nodee){
		$nodee = explode('|', $nodee);
        if(!strstr($nodee[7], 'stop')){
            $b64 = makeb64($nodee,$usage['port'],$usage['passwd'],$package->name);
            $results[$x] = $b64;
            $x++;
        }
	}
	$finalresult = '';
	foreach($results as $result){
		if(strstr($result[7], 'ss&ssr')){
        	$finalresult .= $result[8]['ssr'] . PHP_EOL;
	    }elseif(strstr($result[7], 'ssr')){
			$finalresult .= $result[8] . PHP_EOL;
		}
	}
	echo(base64_encode($finalresult));

}else{
	die('Invaild');
}