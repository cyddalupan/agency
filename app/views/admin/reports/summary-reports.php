<div id="summary-reports">
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
							<?php echo $totalSelected; ?> <br>
							NO. OF IN PROCESS
						</th>
					<tr>
					<tr>
						<td>
							<ul class="list-group">
								<?php foreach($totalSelectedPositions as $position): ?>
								<li class="list-group-item">
									<?php echo $position['name']; ?> <br>
									<?php echo $position['count']; ?>
								</li>
								<?php endforeach; ?>
							</ul>
						</td>
					<tr>
				</table>
			</td>
			<td>
				<table class="table table-bordered">
					<tr>
						<th>
							<?php echo count($deployed_weekly); ?> <br>
							FOR THE WEEK
						</th>
						<th>
							<?php echo count($deployed_monthly); ?> <br>
							FOR THE MONTH
						</th>
						<th>
							<?php echo count($deployed_yearly); ?> <br>
							FOR THE YEAR
						</th>
					<tr>
					<tr>
						<td>
							<ul class="list-group">
								<?php foreach($deployed_weekly as $job): ?>
								<li class="list-group-item">
									<?php echo $job['name']; ?> <br>
									<?php echo $job['count']; ?>
								</li>
								<?php endforeach; ?>
							</ul>
						</td>
						<td>
							<ul class="list-group">
								<?php foreach($deployed_monthly as $job): ?>
								<li class="list-group-item">
									<?php echo $job['name']; ?> <br>
									<?php echo $job['count']; ?>
								</li>
								<?php endforeach; ?>
							</ul>
						</td>
						<td>
							<ul class="list-group">
								<?php foreach($deployed_yearly as $job): ?>
								<li class="list-group-item">
									<?php echo $job['name']; ?> <br>
									<?php echo $job['count']; ?>
								</li>
								<?php endforeach; ?>
							</ul>
						</td>
					<tr>
				</table>
			</td>
			<td>
				<table class="table table-bordered">
					<tr>
						<th>
							<?php echo count($lineup_monthly); ?> <br>
							FOR THE MONTH
						</th>
						<th>
							<?php echo count($lineup_yearly); ?> <br>
							FOR THE YEAR
						</th>
					<tr>
					<tr>
						<td>
							<ul class="list-group">
								<?php foreach($lineup_monthly as $job): ?>
								<li class="list-group-item">
									<?php echo $job['name']; ?> <br>
									<?php echo $job['count']; ?>
								</li>
								<?php endforeach; ?>
							</ul>
						</td>
						<td>
							<ul class="list-group">
								<?php foreach($lineup_yearly as $job): ?>
								<li class="list-group-item">
									<?php echo $job['name']; ?> <br>
									<?php echo $job['count']; ?>
								</li>
								<?php endforeach; ?>
							</ul>
						</td>
					<tr>
				</table>
			</td>
		</tr>
	</table>
</div>
