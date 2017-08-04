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
	
	<div class="row">
        <div class="col-md-12">
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Login</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body">   
				<form action="login.php" method="post">
				  <p>AdminName: <input type="text" name="username" /></p>
				  <p>Password: <input type="text" name="password" /></p>
				  <input type="submit" value="Submit" />
				</form>
            </div>
			
			<div class="alert alert-danger">
				<?echo($data);?>
			</div>
			
			
          </div>
	    </div>  
	</div>  
  </section> 
 </div> 






