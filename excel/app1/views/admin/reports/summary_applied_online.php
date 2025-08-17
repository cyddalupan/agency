<div id="summary-applied-online" ng-controller="summary-applied-online" ng-init="init('<?php echo $agent_id; ?>')">
	<h4 class="text-center">
		<?php echo $recruitment_agent_name; ?> <br>
		APPLIED ONLINE REPORT SUMMARY <br>
		FOR THE PERIOD OF <?php echo date('d F Y'); ?><br>
	</h4>
	<br><br>
	<table class="table table-bordered">
		<tr>
			<th>
				FOR THE WEEK
			</th>
			<th>
				FOR THE MONTH
			</th>
			<th>
				FOR THE YEAR
			</th>
		</tr>
		<tr>
			<td>
				<ul class="list-group">
					<li class="list-group-item" ng-repeat="weekly in applicants.weekly">
						{{weekly.name}} <br>
						{{weekly.count}}
					</li>
				</ul>
			</td>
			<td>
				<ul class="list-group">
					<li class="list-group-item" ng-repeat="monthly in applicants.monthly">
						{{monthly.name}} <br>
						{{monthly.count}}
					</li>
				</ul>
			</td>
			<td>
				<ul class="list-group">
					<li class="list-group-item" ng-repeat="yearly in applicants.yearly">
						{{yearly.name}} <br>
						{{yearly.count}}
					</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th>
				{{applicants.count_weekly}} <br>
				WEEK TOTAL 
			</th>
			<th>
				{{applicants.count_monthly}} <br>
				MONTH TOTAL
			</th>
			<th>
				{{applicants.count_yearly}} <br>
				YEAR TOTAL 
			</th>
		</tr>
	</table>
</div>
