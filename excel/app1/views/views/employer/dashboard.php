<?php $app->renderAlerts(); ?>
<div class="row">
	<div class="col-md-12 col-sm-12">
		<h2><a href="#">Dashboard</a></h2>
	</div> 
</div>
<div class="row">
	<div class="col-md-12 col-sm-12">
		<div class="meta">
			<i class="fa fa-calendar"></i> <?php echo date( 'd-m-Y', time() ); ?>
		</div>
		</br>
		<h1>Welcome! <?php echo $_SESSION['employer']['user']['user_fullname']; ?></h1>
		<div class="clearfix"></div>
	</div>
</div>