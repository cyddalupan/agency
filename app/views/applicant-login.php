<div class="container-fluid">
	<br><br><br><br>
	<div class="col-md-4"></div>
	<div class="col-md-4">
		<h3 class="text-danger"><?=$loginError?></h3>
		<form method="post" action="<?=site_url();?>applicant_system/applicant_login_submit">
			<div class="form-group">
				<label for="applicant-email">Email address</label>
				<input type="email" name="email" class="form-control" id="applicant-email" placeholder="Email">
			</div>
			<div class="form-group">
				<label for="applicant-password">Password</label>
				<input type="password" name="password" class="form-control" id="applicant-password" placeholder="Password">
			</div>
			<button type="submit" class="btn btn-default">Submit</button>
		</form>
	</div>
	<div class="col-md-4"></div>
</div>