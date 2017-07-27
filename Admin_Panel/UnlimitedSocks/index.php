<?php
require('init.php');
if(!Admin_is_login()){
	header("Location: login.php");
}
render_html_tpl('web','header','');
render_html_tpl('web','sidebar','');

$data = make_index_data();
render_html_tpl('web','index',$data);
render_html_tpl('web','footer','');