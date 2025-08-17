<!-- Page Body -->
<div class="page-body page-<?php echo $app->getTemplate(); ?>">
	<?php $app->renderAlerts(); ?>
                    
    <div class="row">
        <div class="col-lg-11 col-md-11 col-sm-12 col-xs-12">
            <div class="widget flat radius-bordered">
                <div class="widget-header bg-blue">
                    <span class="widget-caption">Advanced Search</span>
                </div>
                <div class="widget-body">
                    <div id="registration-form">
                        <form class="form" role="form" method="post" enctype="multipart/form-data">
                        	<div class="row">
                            	<div class="col-sm-12">
                                	<div class="form-title">
                                        <strong>Search options</strong>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label>Search keyword</label>
                                                <input type="text" name="applicant[basic][first]" class="form-control" placeholder="Keywords" value="<?php echo isset( $post['applicant']['basic']['first'] ) ? $post['applicant']['basic']['first'] : ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label>Country:</label> 
                                                <select name="applicant[preferred-country]" style="width:100%">
                                                    <option value="">All</option>
                                                    <?php foreach ($countries as $country): ?>
                                                    <option value="<?php echo $country['country_id']; ?>" <?php echo isset( $post['applicant']['preferred-country'] ) && $post['applicant']['preferred-country'] == $country['country_id'] ? 'selected' : '';  ?>><?php echo $country['country_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label>Position</label> 
                                                <select name="applicant[preferred-position]" style="width:100%">
                                                    <option value="">-- Select position --</option>
                                                    <?php foreach ($categories as $category): ?>
                                                    <optgroup label="<?php echo $category['category_name']; ?>">
                                                    <?php foreach ($category['positions'] as $position): ?>
                                                    <option value="<?php echo $position['position_id']; ?>" <?php echo isset( $post['applicant']['preferred-position'] ) && $post['applicant']['preferred-position'] == $position['position_id'] ? 'selected' : '';  ?>><?php echo $position['position_name']; ?></option>
                                                    <?php endforeach; ?>
                                                    </optgroup>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-2">
                                            <div class="form-group">
                                                <label>Gender</label>
                                                <select name="applicant[basic][gender]" class="form-control">
                                                    <option value="">Any</option>
                                                    <option value="Male">Male</option>
                                                    <option value="Female">Female</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <label>Age bracket</label>
                                            <div class="input-group ">
                                                <input type="text" class="form-control" placeholder="0">
                                                <span class="input-group-addon">&minus;</span>
                                                <input type="text" placeholder="0" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <label>Salary range</label>
                                            <div class="input-group ">
                                                <input type="text" class="form-control" placeholder="0.00">
                                                <span class="input-group-addon">&minus;</span>
                                                <input type="text" placeholder="0.00" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <br>
                                            <button class="btn btn-lg btn-primary">Search</button>
                                        </div>
                                                                                              
                                    </div> 
                                    
                                </div>
                            </div>
                        	
                            
                            <hr class="wide" />
                        
                            <div class="form-title">
                                <strong>Search result</strong>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <th>Applicant</th>
                                                <th>Company</th>
                                                <th>Country</th>
                                                <th>Position</th>
                                                <th>Status</th>
                                                <th>Age</th>
                                                <th>Expected salary</th>
                                                <th>&nbsp;</th>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Juan dela Cruz (sample)</td>
                                                    <td>Facebook Inc.</td>
                                                    <td>Saudi Arabia</td>
                                                    <td>Web Developer</td>
                                                    <td>
                                                        <span class="label label-success">Deployed</span>
                                                    </td>
                                                    <td>
                                                        44 <small>y/o</small>
                                                    </td>
                                                    <td>
                                                        P<span class="text-danger">25,000.00</span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-xs btn-default">Review</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                            
                            <hr class="wide" />
                            <button type="reset" class="btn btn-default">Reset Form</button>
                            <button type="submit" class="btn btn-blue">Register</button>
                            
                            <small><span class="pull-right">Signed in as <strong><?php echo $_SESSION['admin']['user']['user_fullname']; ?></strong> (<a href="?logout">Change user</a>)</span></small>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>
<!-- /Page Body -->
