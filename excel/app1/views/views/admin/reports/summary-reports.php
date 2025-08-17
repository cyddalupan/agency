<div id="summary-reports" ng-controller="summary-reports" ng-init="init('<?php echo $employer_id; ?>')">
	<h4 class="text-center">
		<?php echo isset($employer_contact_person) ? $employer_contact_person : 'ALL EMPLOYERS'; ?><br>
		RECRUITMENT STATUS REPORT SUMMARY <br>
		FOR THE PERIOD OF <?php echo date('d F Y'); ?><br>
	</h4>
	<br><br>
	<table class="table table-bordered">
		<tr>
			<th>
				IN PROCESS
			</th>
			<th>
				DEPLOYED
			</th>
			<th>
				line up
			</th>
		</tr>
		<tr>
			<td>
				<table class="table table-bordered">
					<tr>
						<th>
							{{totalSelected}} <br>
							NO. OF IN PROCESS
						</th>
					<tr>
					<tr>
						<td>
							<ul class="list-group">
								<li class="list-group-item" ng-repeat="totalSelectedPosition in totalSelectedPositions">
									{{totalSelectedPosition.name}} <br>
									{{totalSelectedPosition.count}}
								</li>
							</ul>
						</td>
					<tr>
				</table>
			</td>
			<td>
				<table class="table table-bordered">
					<tr>
						<th>
							{{deployed.count_weekly}} <br>
							FOR THE WEEK
						</th>
						<th>
							{{deployed.count_monthly}} <br>
							FOR THE MONTH
						</th>
						<th>
							{{deployed.count_yearly}} <br>
							FOR THE YEAR
						</th>
					<tr>
					<tr>
						<td>
							<ul class="list-group">
								<li class="list-group-item" ng-repeat="job_weekly in deployed.jobs_weekly">
									{{job_weekly.name}} <br>
									{{job_weekly.count}}
								</li>
							</ul>
						</td>
						<td>
							<ul class="list-group">
								<li class="list-group-item" ng-repeat="job_monthly in deployed.jobs_monthly">
									{{job_monthly.name}} <br>
									{{job_monthly.count}}
								</li>
							</ul>
						</td>
						<td>
							<ul class="list-group">
								<li class="list-group-item" ng-repeat="job_yearly in deployed.jobs_yearly">
									{{job_yearly.name}} <br>
									{{job_yearly.count}}
								</li>
							</ul>
						</td>
					<tr>
				</table>
			</td>
			<td>
				<table class="table table-bordered">
					<tr>
						<th>
							{{lineup.count_monthly}} <br>
							FOR THE MONTH
						</th>
						<th>
							{{lineup.count_yearly}} <br>
							FOR THE YEAR
						</th>
					<tr>
					<tr>
						<td>
							<ul class="list-group">
								<li class="list-group-item" ng-repeat="job_monthly in lineup.jobs_monthly">
									{{job_monthly.name}} <br>
									{{job_monthly.count}}
								</li>
							</ul>
						</td>
						<td>
							<ul class="list-group">
								<li class="list-group-item" ng-repeat="job_yearly in lineup.jobs_yearly">
									{{job_yearly.name}} <br>
									{{job_yearly.count}}
								</li>
							</ul>
						</td>
					<tr>
				</table>
			</td>
		</tr>
	</table>
</div>
