<?php
function get_client_products($id = null){
	$array = array(
				'action' => 'GetClientsProducts',
				'username' => s('adminname'),
				'password' => s('adminpass'),
				'clientid' => $id,
				'stats' => true,
				'responsetype' => 'json',
			 );
	return API_call($array);
}

function get_products($id = null,$module = 'UnlimitedSocks'){
	$array = array(
				'action' => 'GetProducts',
				'username' => s('adminname'),
				'password' => s('adminpass'),
				'pid' => $id,
				'module' => $module,
				'responsetype' => 'json',
			);
	return API_call($array);
}

function get_products_Fix($id = null,$module = 'UnlimitedSocks'){
	$array = array(
				'action' => 'getproductsall',
				'username' => s('adminname'),
				'password' => s('adminpass'),
				'pid' => $id,
				'module' => $module,
				'responsetype' => 'json',
			);
	return API_call($array);
}

function get_servers(){
	$array = array(
				'action' => 'GetServersDetails',
				'username' => s('adminname'),
				'password' => s('adminpass'),
				'responsetype' => 'json',
			);
	return API_call($array);
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

function prase_server($servers,$module = 'UnlimitedSocks'){
	$servers = $servers['servers'];
	$serverarray = array();
	foreach($servers as $server){
		if($server['type'] == $module){
			$serverarray[] = $server; 
		}
	}
	return $serverarray;
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

function prase_route($nodee){
	$nodee = explode('|', $nodee);
	$y = 0;
	$ress = array();
	foreach($nodee as $nodet){
		$ress[$y] = $nodet;
		$y ++;
	}
	return $ress;
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

function make_index_data(){
	$pids = prase_pid(json_decode(get_products(),true));
	$products = json_decode(get_client_products(),true);
	$product = get_client_products_with_pids($products,$pids);
	$allproduct = get_client_products_with_pids($products,$pids,array('Active,Canceled,Terminated'));
	$servers = prase_server(json_decode(get_servers(),true));
	$routes = prase_routes(json_decode(get_products_Fix(),true));
	$return = array(
		'products' => $product,
		'allproducts' => $allproduct,
		'amount' => count($product),
		'productamount' => count($pids),
		'servers' => $servers,
		'serversamount' => count($servers),
		'routes' => $routes,
		'routesamount' => count($routes),
	);
	return $return;
}














