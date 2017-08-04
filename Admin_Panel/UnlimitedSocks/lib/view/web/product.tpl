<link rel="stylesheet" href="<?echo(get_web_path());?>/assets/bower_components/bootstrap/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="<?echo(get_web_path());?>/style.css">
<style>
.table-container
{
width: 100%;
overflow-y: auto;
_overflow: auto;
margin: 0 0 1em;
}

.table-container::-webkit-scrollbar
{
-webkit-appearance: none;
width: 14px;
height: 14px;
}

.table-container::-webkit-scrollbar-thumb
{
border-radius: 8px;
border: 3px solid #fff;
background-color: rgba(0, 0, 0, .3);
}

.table-container-outer { position: relative; }

.table-container-fade
{
	position: absolute;
	right: 0;
	width: 30px;
	height: 100%;
	background-image: -webkit-linear-gradient(0deg, rgba(255,255,255,.5), #fff);
	background-image: -moz-linear-gradient(0deg, rgba(255,255,255,.5), #fff);
	background-image: -ms-linear-gradient(0deg, rgba(255,255,255,.5), #fff);
	background-image: -o-linear-gradient(0deg, rgba(255,255,255,.5), #fff);
	background-image: linear-gradient(0deg, rgba(255,255,255,.5), #fff);
}
</style>
		<?
			$product = $data;
			foreach($product['products']['product'] as $productt){
				$details = '{"result":"success"'.get_module_custom($product['serviceid']);
				$details = json_decode($details,true);
				if(!$details){
					$details = array(
									"message" => array(
										"Port" => "ERROR",
										"Traffic" => "ERROR",
										"U" => "ERROR",
										"D" => "ERROR",
										"All" => "ERROR",
										"Last" => "ERROR",
										"LReset" => "ERROR",
									),
								);
				}
				$details = array_values($details['message']);
		?>
			<div class="plugin">
				<div class="row">
					<div class="col-md-12">
						<aside class="profile-nav alt hidden-xs">
							<section class="panel">
								<ul class="nav nav-pills nav-stacked">
									<li><a href="javascript:;"> Regdate : <?echo $productt['regdate']?> </a></li>
									<li><a href="javascript:;"> Product/Service : <?echo $productt['name']?></a></li>
									<li><a href="javascript:;"> Duedate : <?echo $productt['nextduedate']?></a></li>
									<li><a href="javascript:;"> LastUseTime : <?echo $details[5]?></a></li>
									<li><a href="javascript:;"> Status : <?echo $productt['status']?> </a></li>
									<li><a href="javascript:;"> Port : <?echo $details[0]?></a></li>
									<li><a href="javascript:;"> Traffic : <?echo $details[1]?></a></li>
									<li><a href="javascript:;"> Upload : <?echo $details[2]?></a></li>
									<li><a href="javascript:;"> Download : <?echo $details[3]?></a></li>
									<li><a href="javascript:;"> AllUsed : <?echo $details[4]?></a></li>
									<li><a href="javascript:;"> Password : <?echo $productt['customfields']['customfield'][0]['value']?> </a></li>
								</ul>
							</section>
						</aside>
					</div>
				</div>
			</div>
		<?
	}
?>	
