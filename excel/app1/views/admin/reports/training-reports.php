<?php
function get_age_local($birthDate){
	$from = new DateTime($birthDate);
	$to   = new DateTime('today');
	return $from->diff($to)->y;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Training Reports</title>

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

  </head>
  <body>
  	<h1>Training Reports</h1>
	<table class="table table-striped">
		<tr>
			<th>
				Name
			</th>
			<th>
				Age
			</th>
			<th>
				Position
			</th>
			<th>
				Branch
			</th>
			<th>
				Training Date
			</th>
			<th>
				End of Training
			</th>
			<th>
				Status
			</th>
		</tr>

		<?php foreach ($reportResult as $report): ?>
		<tr>
			<td>
				<?=$report->applicant_first?> <?=$report->applicant_middle?> <?=$report->applicant_last?>
			</td>
			<td>
				<?=get_age_local($report->applicant_birthdate)?>
			</td>
			<td>
				<?=$categories[$report->applicant_preferred_position]['position_name']?>
			</td>
			<td>
				<?=$trainingBranches[$report->training_branches_id]?>
			</td>
			<td>
				<?=$report->start_training_at?>
			</td>
			<td>
				<?=$report->end_training_at?>
			</td>
			<td>
				<?=$statusText[$report->applicant_status]?>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
</body>
</html>