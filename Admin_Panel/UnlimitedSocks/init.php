<?php
error_reporting(E_ALL); 
ini_set('display_errors', '1'); 

session_start();
define( 'DS' , DIRECTORY_SEPARATOR );
define( 'AROOT' , dirname( __FILE__ ) . DS ."lib". DS  );
require('config/config.php');
require('lib/functions/functions.php');