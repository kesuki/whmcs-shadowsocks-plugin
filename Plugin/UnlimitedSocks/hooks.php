<?php
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

define("currentVersion", "2.1.0Beta2");
require_once 'lib/functions.php';
multi_language_support();
add_hook('AdminHomeWidgets', 0, function() {
    return new UnlimitedSocksMainWidget();
});

add_hook('AdminHomeWidgets', 1, function() {
    return new UnlimitedSocksRoutesWidget();
});

add_hook('AdminHomeWidgets', 2, function() {
    return new UnlimitedSocksProductsWidget();
});

add_hook('AdminHomeWidgets', 3, function() {
    return new UnlimitedSocksClientsWidget();
});

class UnlimitedSocksMainWidget extends \WHMCS\Module\AbstractWidget
{
    protected $title = 'UnlimitedSocks';
    protected $description = 'UnlimitedSocks-MainPanel';
    protected $columns = 3;
    protected $cache = false;
    protected $cacheExpiry = 120;
    protected $requiredPermission = '';
    
    public function getData()
    {   
        $command = 'getproductsall';
        $postData = array(
            'module' => 'UnlimitedSocks',
        );
        $results = localAPI($command, $postData, 1);
        if($results['result'] != 'success' or !$results){
            return false;
        }
        $products = $results['products']['product'];
        $data = array(
            'proamount' => count($products),
            'routes' => count(prase_routes($results)),
        );
        return $data;
    }

    public function generateOutput($data)
    {   
        if($data){
            render_html_tpl("AdminHomeWidget-Main",$data);   
        }else{
            render_html_tpl("error",get_lang('no_info'));
        }
    }
}

class UnlimitedSocksRoutesWidget extends \WHMCS\Module\AbstractWidget
{
    protected $title = 'UnlimitedSocks-Routes';
    protected $description = 'UnlimitedSocks-AdminPanel(Routes)';
    protected $columns = 3;
    protected $cache = false;
    protected $cacheExpiry = 120;
    protected $requiredPermission = '';
    
    public function getData()
    {   
        $command = 'getproductsall';
        $postData = array(
            'module' => 'UnlimitedSocks',
        );
        $results = localAPI($command, $postData, 1);
        if($results['result'] != 'success' or !$results){
            return false;
        }
        $data = array(
            'routes' => prase_routes($results),
        );
        return $data;
    }

    public function generateOutput($data)
    {   
        if($data){
            render_html_tpl("AdminHomeWidget-Routes",$data);   
        }else{
            render_html_tpl("error",get_lang('no_routes'));
        }
    }
}

class UnlimitedSocksProductsWidget extends \WHMCS\Module\AbstractWidget
{
    protected $title = 'UnlimitedSocks-Products';
    protected $description = 'UnlimitedSocks-AdminPanel(Products)';
    protected $columns = 3;
    protected $cache = false;
    protected $cacheExpiry = 120;
    protected $requiredPermission = '';
    
    public function getData()
    {   
        $command = 'GetProducts';
        $postData = array(
            'module' => 'UnlimitedSocks',
        );
        $results = localAPI($command, $postData, 1);
        if($results['result'] != 'success' or !$results){
            return false;
        }
        $products = $results['products']['product'];
        return $products;
    }

    public function generateOutput($data)
    {   
        if($data){
            render_html_tpl("AdminHomeWidget-Products",$data);   
        }else{
            render_html_tpl("error",get_lang('no_products'));
        }
    }
}

class UnlimitedSocksClientsWidget extends \WHMCS\Module\AbstractWidget
{
    protected $title = 'UnlimitedSocks-ClientsProducts';
    protected $description = 'UnlimitedSocks-AdminPanel(ClientsProducts)';
    protected $columns = 3;
    protected $cache = false;
    protected $cacheExpiry = 120;
    protected $requiredPermission = '';
    public $server = array();
    
    public function getData()
    {   
        $command = 'GetClientsProducts';
        $postData = array(
        );
        $results = localAPI($command, $postData, 1);
        //print_r($results);
        if($results['result'] != 'success' or !$results){
            return false;
        }
        $command = 'GetProductsall';
        $postData = array(
            'module' => 'UnlimitedSocks',
        );
        $resultsp = localAPI($command, $postData, 1);
        if($resultsp['result'] != 'success' or !$resultsp){
            return false;
        }
        
        $command = 'GetServersDetails';
        $postData = array(
            'module' => 'UnlimitedSocks',
        );
        $resultsg = localAPI($command, $postData, 1);
        if($resultsg['result'] != 'success' or !$resultsg){
            print_r('API File(getserversdetails.php) Unfound');
            return false;
        }
        //print_r($resultsg);
        $this->server = $resultsg['servers'];
        $pids = prase_pid($resultsp);
        $pro = get_client_products_with_pids($results,$pids);
        $pro = get_more_client_product_info($pro,$this->server,prase_product_DB($resultsp));
        //print_r($pro);
        return $pro;
    }
    
    public function generateOutput($data)
    {   //print_r($this->server);
        if($data){
            render_html_tpl("AdminHomeWidget-ClientsProducts",$data);   
        }else{
            render_html_tpl("error",get_lang('no_client_product_isset'));
        }
    }
}

function getusername($uid){
    $command = 'GetClientsDetails';
    $postData = array(
        'clientid' => $uid,
    );
    $results = localAPI($command, $postData, 1);
    if($results['result'] != 'success' or !$results){
        return "Unisset";
    }
    return $results['fullname'];
}

function prase_pid($products,$module = 'UnlimitedSocks'){
    $products = $products['products']['product'];
    $product = array();
    foreach($products as $pro){
        if($pro['module'] == $module){
            $product[] = $pro['pid'];
        }
    }
    return $product;
}

function prase_product_DB($products,$module = 'UnlimitedSocks'){
    $products = $products['products']['product'];
    $product = array();
    foreach($products as $pro){
        if($pro['module'] == $module){
            $product[$pro['pid']] = $pro['configoptions']['configoption'][1];
        }
    }
    return $product;
}

function prase_routes($products){
	$products = $products['products']['product'];
	$routes = array();
	foreach($products as $product){
		$route = $product['configoptions']['configoption']['5'];
		foreach(prase_node($route) as $node){
			array_push($routes,$node);
		}
	}
	return array_unique($routes);
}

function prase_node($routes){
	$results = array();
	$noder = explode("\n",$routes);
	return $noder;
}

function get_client_products_with_pids($products,$pids,$status = array('Active')){
	$products = $products['products']['product'];
	$product = array();
	foreach($products as $pro){
		if(in_array($pro['pid'],$pids) && in_array($pro['status'],$status)){
			$product[] = $pro;
		}
	}
	return $product;
}

function render_html_tpl($tpl,$data){
    $rootdir = realpath(dirname(__FILE__) . "/hooktemplates");
	$layout_file = $rootdir . '/' . $tpl . '.hook';
	if( file_exists( $layout_file ) )
	{
		@extract( $data );
		require( $layout_file );
	}else{
        echo('Can\'t find view - '   .  $tpl .'.hook');
    }
}

function get_more_client_product_info($products,$server,$whproduct){
    foreach($server as $ser){
        $mysql = new mysqli($ser['serverhostname'], $ser['serverusername'], $ser['serverpassword']);
        $servername = 'mysqlserver'.$ser['id'];
        $$servername = $mysql;
    }
    $product = array();
    foreach($products as $pro){
        $sid = $pro['serverid'];
        $mysql = 'mysqlserver'.$sid;
        $sql = $$mysql;
        $sql->select_db($whproduct[$pro['pid']]);
        $sqlq = "SELECT * FROM `user` WHERE sid = " . $pro['id'];
        $ssacc = mysqli_fetch_array($sql->query($sqlq),MYSQLI_ASSOC);
        $details = array(
            "Port" => $ssacc['port'],
            "Traffic" => $ssacc['transfer_enable'],
            "U" => round($ssacc['u']/1048576,2),
            "D" => round($ssacc['d']/1048576,2),
            "A" => round(($ssacc['u'] + $ssacc['d'])/1048576,2),
            "Last" => $ssacc['t'],
            "LReset" => $ssacc['updated_at']
        );
        $pro['details'] = $details;
        $product[$pro['id']] = $pro;
    }
    return $product;
}

function  mconvert($number, $from, $to){
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

function MMBGB($tra){
    if($tra >= 1024){
        $tra = round($tra / 1024,2);
        $tra .= 'GB';
    }else{
        $tra .= 'MB';
    }
    return $tra;
}













