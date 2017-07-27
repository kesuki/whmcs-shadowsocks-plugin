<?php
require('init.php');
$res = Admin_quit();
if($res){
	header("Location: login.php");
}