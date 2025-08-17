<!-- Navigation Starts -->
<!-- Note down the syntax before editing. It is the default twitter bootstrap navigation -->
<div class="navbar bs-docs-nav" role="banner">
	<div class="container">
		<div class="navbar-header">
			<button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			</button>
		</div>
		<nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
		<!-- Navigation links starts here -->
		<ul class="nav navbar-nav">
			<li class="dropdown <?php echo controllerActive('dashboard'); ?>">
				<a href="<?php echo site_url( 'employer/dashboard' ); ?>" >Dashboard</a>
			</li>
			
				<li class="dropdown <?php echo subControllerActive('applicants/line-up'); ?>">

				<a href="<?php echo site_url( 'employer/applicants/line-up' ); ?>" >Line Up</a>

			</li>
			
			<li class="dropdown <?php echo subControllerActive('applicants/reserved'); ?>">
				<a href="<?php echo site_url( 'employer/applicants/reserved' ); ?>" >Reserved</a>
			</li>
			<li class="dropdown">
				<a href="<?php echo site_url( 'employer/applicants/pre-selected' ); ?>" >Pre Selected</a>
			</li>
			
			<li class="dropdown <?php echo subControllerActive('applicants/selected'); ?>">
				<a href="<?php echo site_url( 'employer/applicants/selected' ); ?>" >Selected</a>
			</li>
			<li class="dropdown">
				<a href="<?php echo site_url( 'employer/applicants/for-booking' ); ?>" >For Booking</a>
			</li>
			
			<li class="dropdown <?php echo subControllerActive('applicants/deployed'); ?>">
				<a href="<?php echo site_url( 'employer/applicants/deployed' ); ?>" >Deployed</a>
			</li>
				<!-- <li class="dropdown <?php //echo subControllerActive('jobs/all'); ?>">
				<a href="<?php //echo site_url( 'employer/jobs/all' ); ?>" >Job Orders</a>
			</li>
			<li class="dropdown <?php //echo subControllerActive('applicants/search'); ?>">
				<a href="<?php //echo site_url( 'employer/applicants/search' ); ?>" >Search Applicants</a>
			</li>-->
			<li class="dropdown <?php echo controllerActive('account', true); ?>">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown">Account <b class="caret"></b></a>
				<!-- Submenus -->
				<ul class="dropdown-menu">
					<li class="<?php echo subControllerActive( 'account/settings' ); ?>"><a href="<?php echo site_url( 'employer/account/settings' ); ?>">Account settings</a></li>
					<li class="<?php echo subControllerActive( 'account/change-password' ); ?>"><a href="<?php echo site_url( 'employer/account/change-password' ); ?>">Change password</a></li>
					<li><a href="?logout">Sign out</a></li>
				</ul>
			</li>
		</ul>
		</nav>
	</div>
</div>
<!--/ Navigation Ends -->