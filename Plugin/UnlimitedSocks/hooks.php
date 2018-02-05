<?php
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use WHMCS\Database\Capsule;
define("currentVersion", "2.1.0Beta5");
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
        $products = array();
        $query = \WHMCS\Database\Capsule::table('tblproducts')->where('servertype', 'UnlimitedSocks')->get();
        if(empty($query)){
            return false;
        }
        $products = QueryToArray($query);
        $data = array(
            'proamount' => count($products),
            'routes' => count(prase_routes($products)),
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
        $products = array();
        $query = \WHMCS\Database\Capsule::table('tblproducts')->where('servertype', 'UnlimitedSocks')->get();
        if(empty($query)){
            return false;
        }
        $products = QueryToArray($query);
        $data = array(
            'routes' => prase_routes($products),
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
        $products = array();
        $query = \WHMCS\Database\Capsule::table('tblproducts')->where('servertype', 'UnlimitedSocks')->get();
        if(empty($query)){
            return false;
        }
        $products = QueryToArray($query);
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
        $query = \WHMCS\Database\Capsule::table('tblproducts')->where('servertype', 'UnlimitedSocks')->get();
        $query2 = \WHMCS\Database\Capsule::table('tblhosting')->get();
        $query3 = \WHMCS\Database\Capsule::table('tblservers')->where('type', 'UnlimitedSocks')->get();
        $products = QueryToArray($query);
        $clients = QueryToArray($query2);
        $servers = QueryToArray($query3);
        $pids = prase_pid($products);
        $pro = get_client_products_with_pids($clients,$pids,array('Active','Suspended'));
        $pro = get_more_client_product_info($pro,$servers,prase_product_DB($products),$products);
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

function QueryToArray($query){
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
                case 'ResetSystemPorts':
                    $result = ChangeSystemPorts();
                    die($result);
                    break;
                case 'EditProduct':
                    if(isset($_REQUEST['details']) && isset($_REQUEST['announcements'])){
                        $result = \WHMCS\Database\Capsule::table('tblproducts')->where('id', $_REQUEST['id'])->update(['description' => $_REQUEST['details']]);
                        $result = \WHMCS\Database\Capsule::table('tblproducts')->where('id', $_REQUEST['id'])->update(['configoption7' => $_REQUEST['announcements']]);
                        if(empty($result)){
                            echo('<script>
                                    window.location.href="index.php";
                                </script>');
                        }else{
                            echo('<script>
                                    window.location.href="index.php";
                                </script>');
                        }
                        die();
                    }
                    break;    
                default:
                    //die('No Action');
                    break;
            }
            $results = localAPI($command, $postData,1);
            die('Success, '.json_encode($results));
        }else{
            die('Timeout');
        }
    }
}

function getusername($uid){
    $query = \WHMCS\Database\Capsule::table('tblclients')->where('id', $uid)->first();
    return $query->firstname.$query->lastname;
}

function prase_pid($products,$module = 'UnlimitedSocks'){
    $product = array();
    foreach($products as $pro){
        if($pro['servertype'] == $module){
            $product[] = $pro['id'];
        }
    }
    return $product;
}

function prase_product_DB($products,$module = 'UnlimitedSocks'){
    $product = array();
    foreach($products as $pro){
        if($pro['servertype'] == $module){
            $product[$pro['id']] = $pro['configoption1'];
        }
    }
    return $product;
}

function prase_routes($products){
	$routes = array();
	foreach($products as $product){
		$route = $product['configoption5'];
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
	$product = array();
	foreach($products as $pro){
		if(in_array($pro['packageid'],$pids) && in_array($pro['domainstatus'],$status)){
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

function get_more_client_product_info($products,$server,$whproduct,$oldproducts){
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
        $pro['productname'] = GetNameFromProduct($oldproducts,$pro['packageid']);
        $product[$pro['id']] = $pro;
    }
    return $product;
}

function GetNameFromProduct($products,$packageid){
    foreach ($products as $product) {
        if($product['id'] == $packageid){
            return $product['name'];
        }
    }
    return 'Error';
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

function makemainresetbutton(){
    $scr = "<button type='button' class='btn btn-danger btn-block' onclick='Reset".$id."()'>".get_lang('resetallports')."</button>
                    <script>
                        function Reset".$id."(){
                            layer.confirm('".get_lang('are_you_sure_to_reset_p').get_lang('all_port')."?', {
                              btn: ['".get_lang('knowledgebaseyes')."','".get_lang('knowledgebaseno')."']
                            }, function(){
                              layer.confirm('".get_lang('are_you_really_sure_to_reset_p').get_lang('all_port')."?', {
                                  btn: ['".get_lang('knowledgebaseyes')."','".get_lang('knowledgebaseno')."']
                                }, function(){
                                  send('UnlimitedSocksAction=ResetSystemPorts&times=".time()."&id=all');
                                  layer.msg('".get_lang('success')."', {icon: 1});
                                  location.reload();
                                });
                            });
                        }
                    </script>";
    return $scr;
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

function MakeProductButton($datas){
    $html = '<form action="index.php" method="get">
                <p>'.get_lang('clientareaproductdetails').': <textarea rows="6" cols="20" name="details" class="form-control">'.$datas['description'].'</textarea></p>
                <p>'.get_lang('announcements').': <textarea name="announcements" rows="6" cols="20" class="form-control">'.$datas['configoptions']['configoption'][7].'</textarea></p>
                <input type="hidden" name="UnlimitedSocksAction" value="EditProduct"></input>
                <input type="hidden" name="times" value="'.time().'"></input>
                <input type="hidden" name="id" value="'.$datas['pid'].'"></input>
                <input class="btn btn-warning btn-block" type="submit" value="'.get_lang('submit').'" />
            </form>';
    $html = str_replace(array("\r\n", "\r", "\n"), "", $html);
    $html = str_replace("   ", '', $html);
    $scr = "<button type='button' class='btn btn-warning btn-block' onclick='EditProduct".$datas['pid']."()'>".get_lang('edit')."</button>
                <script>
                    function EditProduct".$datas['pid']."(){
                        layer.open({
                          type: 1,
                          skin: 'layui-layer-rim', //加上边框
                          maxmin: true, //开启最大化最小化按钮
                          area: ['893px', '600px'],
                          content: '".$html."'
                        });
                    }
                </script>";
    return $scr;
}

function GetStartPortFromProduct($products,$packageid){
    foreach ($products as $product) {
        if($product['id'] == $packageid){
            return $product['configoption4'];
        }
    }
    return '10000';
}

function ChangeSystemPorts(){
    $query = \WHMCS\Database\Capsule::table('tblproducts')->where('servertype', 'UnlimitedSocks')->get();
    $query2 = \WHMCS\Database\Capsule::table('tblhosting')->get();
    $query3 = \WHMCS\Database\Capsule::table('tblservers')->where('type', 'UnlimitedSocks')->get();
    $products = QueryToArray($query);
    $clients = QueryToArray($query2);
    $servers = QueryToArray($query3);
    $pids = prase_pid($products);
    $pro = get_client_products_with_pids($clients,$pids,array('Active','Suspended'));
    $pro = get_more_client_product_info($pro,$servers,prase_product_DB($products),$products);
    $whproduct = prase_product_DB($products);
    $unuseableport = array();

    foreach($servers as $ser){
        $mysql = new mysqli($ser['ipaddress'], $ser['username'], decrypt($ser['password']));
        $servername = 'mysqlserver'.$ser['id'];
        $$servername = $mysql;
    }
    foreach($pro as $proo){
        if($proo['domainstatus'] == "Active"){
            $startport = GetStartPortFromProduct($products,$proo['packageid']);
            $port = mt_rand($startport,65400);
            while(in_array($unuseableport,$port)){
                $port = mt_rand($startport,65400);
            }
            array_push($unuseableport,$port);
            $mysql = 'mysqlserver'.$proo['server'];
            $sql = $$mysql;
            $sql->select_db($whproduct[$proo['packageid']]);
            $sqlq = "Update `user` set `port`=".$port." WHERE sid =".$proo['id'];
            $ssacc = $sql->query($sqlq);
        }
    }
    return 'success';
}



