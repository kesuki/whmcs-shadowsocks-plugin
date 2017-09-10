<?php
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

define("currentVersion", "2.1.0Beta3");
require_once 'lib/functions.php';
multi_language_support();
maincontroll();
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
        generateMainscript();
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
        $this->server = $resultsg['servers'];
        $pids = prase_pid($resultsp);
        $pro = get_client_products_with_pids($results,$pids,array('Active','Suspended'));
        $pro = get_more_client_product_info($pro,$this->server,prase_product_DB($resultsp));
        return $pro;
    }
    
    public function generateOutput($data)
    {
        if($data){
            render_html_tpl("AdminHomeWidget-ClientsProducts",$data);   
        }else{
            render_html_tpl("error",get_lang('no_client_product_isset'));
        }
    }
}

function maincontroll(){
    if(isset($_REQUEST['UnlimitedSocksAction']) and isset($_REQUEST['times']) and isset($_REQUEST['id'])){
        if(time() - $_REQUEST['times'] <= 60 * 10){
            switch($_REQUEST['UnlimitedSocksAction']){
                case 'Reset':
                    $command = 'ModuleCustom';
                    $postData = array(
                        'accountid' => $_REQUEST['id'],
                        'func_name' => 'resetbandwidth',
                    );
                    break;
                case 'Suspend':
                    $command = 'ModuleSuspend';
                    $postData = array(
                        'accountid' => $_REQUEST['id'],
                    );
                    break;  
                case 'Unsuspend':
                    $command = 'ModuleUnsuspend';
                    $postData = array(
                        'accountid' => $_REQUEST['id'],
                    );
                    break;  
                default:
                    die('No Action');
                    break;
            }
            $results = localAPI($command, $postData,1);
            die('Success');
        }else{
            die('Timeout');
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

function mconvert($number, $from, $to){
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

function generateMainscript(){
    echo('<script>
    function send(arg) {
      CreateXMLHttpRequest();
      xmlhttp.onreadystatechange = callhandle;
      xmlhttp.open("GET","index.php?" + arg,true);
      xmlhttp.onreadystatechange = processResponse;
      xmlhttp.send(null);
    }

    function CreateXMLHttpRequest() {
      if (window.ActiveXObject) {
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
      }
      else if (window.XMLHttpRequest) {
        xmlhttp = new XMLHttpRequest();
      }
    }
    
    function callhandle() {
      if (xmlhttp.readyState == 4) {
        if (xmlhttp.status == 200) {
          alert(xmlhttp.responseText);
        }
      }
    }
    
    function processResponse(){
        if(xmlhttp.readyState == 4){     //判断对象状态
            if(xmlhttp.status == 200){
            }else{
                alert("HTTP 200");
            }
        }
    }
    </script>');
}

function makebutton($sid,$user,$port,$status){
    if($status == "Active"){
        $button = makelayoutscript("Reset",$sid,$user,$port);
        $button .= makelayoutscript("Suspend",$sid,$user,$port);
    }else{
        $button = makelayoutscript("Unsuspend",$sid,$user,$port);
    }
    return $button;
}

function makelayoutscript($res,$id,$user = null,$port = null){
    switch($res){
        case "Reset":
            $scr = "<button type='button' class='btn btn-danger btn-block' onclick='Reset".$id."()'>".get_lang('resetbandwidth')."</button>
                    <script>
                        function Reset".$id."(){
                            layer.confirm('".get_lang('are_you_sure_to_reset').":".$user."(SID:".$id.",".get_lang('port').$port.")?', {
                              btn: ['".get_lang('knowledgebaseyes')."','".get_lang('knowledgebaseno')."']
                            }, function(){
                              send('UnlimitedSocksAction=Reset&id=".$id."&times=".time()."');
                              layer.msg('".get_lang('success')."', {icon: 1});
                              location.reload();
                            });
                        }
                    </script>";
            break;
        case "Suspend":
            $scr = "<button type='button' class='btn btn-warning btn-block' onclick='Suspend".$id."()'>".get_lang('suspendacc')."</button>
                    <script>
                        function Suspend".$id."(){
                            layer.confirm('".get_lang('are_you_sure_to_suspend').":".$user."(SID:".$id.",".get_lang('port').$port.")?', {
                              btn: ['".get_lang('knowledgebaseyes')."','".get_lang('knowledgebaseno')."']
                            }, function(){
                              send('UnlimitedSocksAction=Suspend&id=".$id."&times=".time()."');
                              layer.msg('".get_lang('success')."', {icon: 1});
                              location.reload();
                            });
                        }
                    </script>";
            break;
        case "Unsuspend":
            $scr = "<button type='button' class='btn btn-warning btn-block' onclick='Suspend".$id."()'>".get_lang('unsuspendacc')."</button>
                    <script>
                        function Suspend".$id."(){
                            layer.confirm('".get_lang('are_you_sure_to_unsuspend').":".$user."(SID:".$id.",".get_lang('port').$port.")?', {
                              btn: ['".get_lang('knowledgebaseyes')."','".get_lang('knowledgebaseno')."']
                            }, function(){
                              send('UnlimitedSocksAction=Unsuspend&id=".$id."&times=".time()."');
                              layer.msg('".get_lang('success')."', {icon: 1});
                              location.reload();
                            });
                        }
                    </script>";
            break;    
    }
    return $scr;
}











