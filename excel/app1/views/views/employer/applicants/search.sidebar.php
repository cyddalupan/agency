<div class="sidebar">
	<h4>Search Applicants</h4>
	
	<hr>

	<form class="form" role="form" method="get" action="">
	<h5>Keywords:</h5>
	<input name="q" type="text" class="form-control" placeholder="Search applicant" value="<?php echo isset( $queryString['q'] ) ? $queryString['q'] : ''; ?>">
	<h5>Position:</h5>
	<select name="position" class="form-control">
		<option value="">All</option>
        <?php foreach ($categories as $category): ?>
        <optgroup label="<?php echo $category['category_name']; ?>">
        <?php if ( count( $category['positions'] ) == 0 ): ?>
        <option value="" disabled>-- n/a --</option>
        <?php endif; ?>
        <?php foreach ($category['positions'] as $position): ?>
        <option value="<?php echo $position['position_id']; ?>" <?php echo isset( $_GET['position'] ) && $_GET['position'] == $position['position_id'] ? 'selected' : '';  ?>><?php echo $position['position_name']; ?></option>
        <?php endforeach; ?>
        </optgroup>
        <?php endforeach; ?>
	</select>
	<h5>Prefered country:</h5>
	<select name="country" class="form-control">
		<option value="">All</option>
        <?php foreach ($countries as $country): ?>
        <option value="<?php echo $country['country_id']; ?>" <?php echo isset( $_GET['country'] ) && $_GET['country'] == $country['country_id'] ? 'selected' : '';  ?>><?php echo $country['country_name']; ?></option>
        <?php endforeach; ?>
	</select>
	<div class="button">
		<button type="submit" class="btn btn-md btn-primary">Search</button>
	</div>
	</form>
    	
	<div class="clearfix"></div>
</div>