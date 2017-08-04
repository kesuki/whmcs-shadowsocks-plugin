<?
require('init.php');
if(!Admin_is_login()){
	header("Location: login.php");
}
if($_GET['id']){
	$product = get_client_products(null,$_GET['id']);
	$product = json_decode($product,true);
	if($product['result'] == 'success' and $product['products'] != ""){
	}else{
		?>
			<div class="alert alert-danger">
				Product Unisset
			</div>
		<?
		die();
	}
}
render_html_tpl('web','product',$product);