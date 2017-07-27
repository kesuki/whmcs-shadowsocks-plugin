<?
function Admin_quit(){
	session_destroy();
	return true;
}