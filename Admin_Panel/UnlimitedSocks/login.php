<?php
require('init.php');
error_reporting(E_ALL); 
ini_set('display_errors', '1'); 
$data = "";
if(isset($_POST['username']) && isset($_POST['password'])){
	$res = admin_login($_POST);
	if(!$res) $data = false;
}
if(Admin_is_login()){
	header("Location: index.php");
}
render_html_tpl('web','login',$data);