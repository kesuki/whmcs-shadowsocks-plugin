<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Dashboard
        <small>Version 1.0</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Dashboard</li>
      </ol>
    </section>
	
	<section class="content">
      <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="ion ion-ios-gear-outline"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Routes</span>
              <span class="info-box-number"><?echo($data['routesamount'])?></span>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-red"><i class="ion ion-stats-bars"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Servers</span>
              <span class="info-box-number"><?echo($data['serversamount'])?></span>
            </div>
          </div>
        </div>
        <div class="clearfix visible-sm-block"></div>
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-green"><i class="ion ion-ios-cart-outline"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Products</span>
              <span class="info-box-number"><?echo($data['productamount'])?></span>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-yellow"><i class="ion ion-ios-people-outline"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Active Orders</span>
              <span class="info-box-number"><?echo($data['amount'])?></span>
            </div>
          </div>
        </div>
      </div>
	  
	  <div class="row">
        <div class="col-md-12">
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Latest Orders</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body">
              <div class="table-responsive">
                <table class="table no-margin">
                  <thead>
                  <tr>
                    <th>Order ID</th>
                    <th>Item</th>
                    <th class="hidden-xs hidden-sm">Status</th>
                    <th class="hidden-xs hidden-sm">Server</th>
					<th>Amount</th>
					<th class="hidden-xs hidden-sm">Port</th>
					<th>Duedate</th>
					<th>Action</th>
                  </tr>
                  </thead>
                  <tbody>
					<?
						$x = 0;
						$products = array_reverse($data['products'],true);
						foreach($products as $product){
							//if($x <= 6){
								?>
									<tr>
										<td><a href=""><?echo($product['id'])?></a></td>
										<td><?echo($product['name'])?></td>
										<td class="hidden-xs hidden-sm"><span class="label label-<?if($product['status'] == 'Active') echo('success'); else echo('danger');?>"><?echo($product['status'])?></span></td>
										<td class="hidden-xs hidden-sm">
										  <div class="sparkbar" data-color="#00a65a" data-height="20"><?echo($product['servername'])?></div>
										</td>
										<td><?echo($product['recurringamount'])?></td>
										<td class="hidden-xs hidden-sm"><?echo($product['message'][0])?></td>
										<td><?echo($product['nextduedate'])?></td>
										<td>
											<button name="Manager" class="btn btn-primary btn-xs" data-type="Info" data-id="<?echo $product['id']?>">
											<i class="fa fa-qrcode"></i>
												Manage
											</button>
										</td>
									</tr>
								<?
							}
							$x ++;
						//}
					?>
					  
                  </tbody>
                </table>
              </div>
            </div>
            <div class="box-footer clearfix">
              <a href="javascript:void(0)" class="btn btn-sm btn-default btn-flat pull-right">View All Orders</a>
            </div>
          </div>
		
		  <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Servers</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <ul class="products-list product-list-in-box">
				<?
					foreach($data['servers'] as $server){
						?>
						<li class="item">
						  <div class="product-info">
							<a href="javascript:void(0)" class="product-title"><?echo($server['name'])?>
							  <span class="label label-warning pull-right"><?echo($server['monthlycost'])?></span></a>
								<span class="product-description">
								  <?echo($server['numaccounts'])?>/<?echo($server['maxaccounts'])?>,<?if($server['serverhostname']) echo($server['serverhostname']); else echo 'None';?>
								</span>
						  </div>
						</li>
						<?
					}
				?>	
              </ul>
            </div>
          </div>
		  
		  <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Routes</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <ul class="products-list product-list-in-box">
				<?
					foreach($data['routes'] as $route){
						$dat = prase_route($route);
						//var_dump($dat);
						?>
						<li class="item">
						  <div class="product-info">
							<a href="javascript:void(0)" class="product-title"><?echo($dat['0'])?>(<?echo($dat['1'])?>)
							  <span class="label label-warning pull-right"><?echo($dat['7'])?></span></a>
								<span class="product-description">
								  <?echo($dat['2'])?>,<?echo($dat['3'])?>,<?echo($dat['5'])?>
								</span>
						  </div>
						</li>
						<?
					}
				?>	
              </ul>
            </div>
          </div>
		  
		  
		  
		</div>  
	</div>  
  </section> 
 </div> 