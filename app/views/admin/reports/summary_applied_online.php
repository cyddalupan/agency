<div id="summary-applied-online">
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
					<?php foreach($weekly as $week): ?>
					<li class="list-group-item">
						<?php echo $week['applied_date']; ?> <br>
						<?php echo $week['count']; ?>
					</li>
					<?php endforeach; ?>
				</ul>
			</td>
			<td>
				<ul class="list-group">
					<?php foreach($monthly as $month): ?>
					<li class="list-group-item">
						<?php echo $month['applied_date']; ?> <br>
						<?php echo $month['count']; ?>
					</li>
					<?php endforeach; ?>
				</ul>
			</td>
			<td>
				<ul class="list-group">
					<?php foreach($yearly as $year): ?>
					<li class="list-group-item">
						<?php echo $year['applied_date']; ?> <br>
						<?php echo $year['count']; ?>
					</li>
					<?php endforeach; ?>
				</ul>
			</td>
		</tr>
		<tr>
			<th>
				<?php echo count($weekly); ?> <br>
				WEEK TOTAL 
			</th>
			<th>
				<?php echo count($monthly); ?> <br>
				MONTH TOTAL
			</th>
			<th>
				<?php echo count($yearly); ?> <br>
				YEAR TOTAL 
			</th>
		</tr>
	</table>
</div>
