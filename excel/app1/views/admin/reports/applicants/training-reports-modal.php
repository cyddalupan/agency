<div class="row">
	<div class="col-sm-1"></div>
	<div class="col-sm-10">
		<br>
		<h3>Training Reports</h3>
		<h5>Position</h5>
		<form action="<?php echo site_url(); ?>admin/reports/training_reports" method="post" target="_blank">
			<select name="category" class="form-control">
				<option value="0">-- Select position --</option>
				<?php foreach ($categories as $category): ?>
				<?php if ( count( $category['positions'] ) == 0 ) continue; ?>
				<optgroup label="<?php echo $category['category_name']; ?>">
				<?php foreach ($category['positions'] as $position): ?>
				<option value="<?php echo $position['position_id']; ?>" <?php echo isset( $post['applicant']['preferred-position'] ) && $post['applicant']['preferred-position'] == $position['position_id'] ? 'selected' : '';  ?>><?php echo $position['position_name']; ?></option>
				<?php endforeach; ?>
				</optgroup>
				<?php endforeach; ?>
			</select>

			<br>
			<h5>Age Bracket</h5>
			<div class="row">
				<div class="col-sm-6">
					<div class="form-group">
						<label for="from">from</label>
						<input type="number" name="agestart" class="form-control" placeholder="ex: 25">
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<label for="to">to</label>
						<input type="number" name="ageend" class="form-control" placeholder="ex: 40">
					</div>
				</div>
			</div>
			<h5>Status</h5>
            <select  class="form-control" name="applicant_status">
                <option value="16">Enrolled to training</option>
                <option value="17">Started training</option>
                <option value="18">Failed Training</option>
                <option value="19">Graduate Training</option>
            </select>

            <?php
	            if($_SESSION['admin']['user']['user_type'] == 12){
			        //if trainingSpecialist get branch to get applicants only on that branch
			        $this->db->get('user');
			        $query = $this->db->get_where('user', array('user_id' => $_SESSION['admin']['user']['user_id']));
			        $trainingAdminResult = $query->result();
			        echo '<input type="hidden" name="training_branch" value="'.$trainingAdminResult[0]->branch_id.'">';
			    }else{
		    ?>
			<h5>Training Branch</h5>
			<select class="form-control" name="training_branch">
				<option value="0">All</option>
				<?php foreach($trainingBranches as $trainingKey => $trainingBranch): ?>
					<option value="<?=$trainingKey?>"><?=$trainingBranch?></option>
				<?php endforeach; ?>
			</select>
			<?php }//endis training specialist else ?>
			<br>
			<input type="submit" class="btn btn-primary pull-right" value="Submit" />
		</form>
		<div class="cearfix"></div>
		<br><br><br>
	</div>
	<div class="col-sm-1"></div>
</div>