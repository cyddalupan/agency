<!-- Header starts -->
<header>
	<div class="container">
		<div class="row">
			<div class="col-md-4 col-sm-4">
				<!-- Logo and site link -->
				<div class="logo">
					<!--
						<h1><a href="<?php echo site_url( 'employer' ); ?>"> STEPUP-MANPOWER<span class="color">.</span></a></h1>
					-->
                            <img src="<?php echo site_url().$_SESSION["settings"]['icon_link']; ?>" alt="" style="border:0px solid black;width:140px;height:45px"/>
				</div>
			</div>
			<div class="col-md-4 col-sm-4 col-sm-offset-4 col-md-offset-4" align="right">
				<div class="list" align="left">
					<!-- Add your phone number here -->
					
					<hr/>
					<!-- Add your address here -->
					<div class="">
						<i class="fa fa-user"></i> <strong><?php echo $_SESSION['employer']['user']['user_fullname']; ?></strong> (<a href="?logout">sign out</a>)
					</div>
					
				</div>
			</div>
		</div>
	</div>
</header>
<!-- Header ends -->