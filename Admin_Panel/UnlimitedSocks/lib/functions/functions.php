<?php
load_functions();

function c($str){
	return isset( $GLOBALS['config'][$str] ) ? $GLOBALS['config'][$str] : false;
}

function s($str){
	return isset( $_SESSION[$str] ) ? $_SESSION[$str] : false;
}

function Admin_is_login(){
	if(isset($_SESSION['adminname']) && isset($_SESSION['adminpass'])){
		return true;
	}
	return false;
}

function render_html_tpl($layout = NULL , $tpl,$data){
	$layout_file = AROOT . 'view/' . $layout . '/' . $tpl . '.tpl';
	if( file_exists( $layout_file ) )
	{
		@extract( $data );
		require( $layout_file );
	}else{die('Can\'t find view - '   .  $tpl );}
}

function load_functions(){
	$dir=dirname(__FILE__) . "/functions";
	$dir=opendir($dir);
	while(($file=readdir($dir))!==false){
		if($file!="."&&$file!=".."&&strpos($file,'.php')){
			require_once('functions/'.$file);
		}
	}
}

function get_web_path($ui = 'web'){
	return '//'.dirname($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']).'/lib/view/'.$ui;
}