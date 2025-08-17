<?php 
// Get Json Files to array
$survey_array = cydGetJson("survey.json");
$wexp_array = cydGetJson("working_experience.json");
$wabl_array = cydGetJson("working_ability.json");
?> 
<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
    <ul class="breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url('admin/'); ?>">Home</a>
        </li>
        <li>Applicants</li>
        <li class="active">New</li>
    </ul>
</div>
<!-- /Page Breadcrumb -->
<!-- Page Header -->
<div class="page-header position-relative">
    <div class="header-title">
        <h1>
            Add New Applicant
        </h1>
    </div>
    <!--Header Buttons-->
    <div class="header-buttons">
        <a class="sidebar-toggler" href="#">
            <i class="fa fa-arrows-h"></i>
        </a>
        <a class="refresh" id="refresh-toggler" href="#">
            <i class="fa fa-refresh"></i>
        </a>
        <a class="fullscreen" id="fullscreen-toggler" href="#">
            <i class="fa fa-arrows-alt"></i>
        </a>
    </div>
    <!--Header Buttons End-->
</div>
<!-- /Page Header -->
<!-- Page Body -->
<div class="page-body page-<?php echo $app->getTemplate(); ?>">
	<?php $app->renderAlerts(); ?>
    <div class="row">
        <div class="col-lg-12 col-sm-12 col-xs-12">
              
            <div class="row">
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    
                    <div class="row">
                        <div class="col-lg-11 col-md-11 col-sm-12 col-xs-12">
                            <div class="widget flat radius-bordered">
                                <div class="widget-header bg-blue">
                                    <span class="widget-caption">Registration Form</span>
                                </div>
                                <div class="widget-body">
                                    <div id="registration-form">
                                        <form class="form" role="form" method="post" enctype="multipart/form-data">
                                        	<div class="row">
                                            	<div class="col-sm-8">
                                                	<div class="form-title">
                                                        <strong>Preferred Designation</strong>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-4">
                                                            <div class="form-group">
                                                                <label>Position</label> 
                                                                <select name="applicant[preferred-position]" style="width:100%" required>
                                                                    <option value="">-- Select position --</option>
                                                                    <?php foreach ($categories as $category): ?>
                                                                    <?php if ( count( $category['positions'] ) == 0 ) continue; ?>
                                                                    <optgroup label="<?php echo $category['category_name']; ?>">
                                                                    <?php foreach ($category['positions'] as $position): ?>
                                                                    <option value="<?php echo $position['position_id']; ?>" <?php echo isset( $post['applicant']['preferred-position'] ) && $post['applicant']['preferred-position'] == $position['position_id'] ? 'selected' : '';  ?>><?php echo $position['position_name']; ?></option>
                                                                    <?php endforeach; ?>
                                                                    </optgroup>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <label>Currency</label> 
                                                            <select name="applicant[currency]" required>
                                                                <option value="">-- Select Currency --</option>
                                                                <?php foreach ($currencies as $currency) { ?>
                                                                    <option><?php echo $currency->currency; ?></option>
                                                                <?php }//end foreach ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <div class="form-group">
                                                                <label>Expected Salary</label>
                                                                <input type="text" name="applicant[expected-salary]" class="form-control" placeholder="00.00" value="<?php echo isset( $post['applicant']['expected-salary'] ) ? $post['applicant']['expected-salary'] : ''; ?>" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-8">
                                                            <div class="form-group">
                                                                <label>Other preferred positions <small><em>(Press Enter to accept position)</em></small>:</label> 
                                                                <select name="applicant[other-preferred-positions][]" multiple style="width:100%">
                                                                    <option value="">-- Select position --</option>
                                                                    <?php foreach ($categories as $category): ?>
                                                                    <optgroup label="<?php echo $category['category_name']; ?>">
                                                                    <?php foreach ($category['positions'] as $position): ?>
                                                                    <option value="<?php echo $position['position_id']; ?>" <?php echo isset( $post['applicant']['other-preferred-positions'] ) && in_array( $position['position_id'], $post['applicant']['other-preferred-positions']) ? 'selected' : '';  ?>><?php echo $position['position_name']; ?></option>
                                                                    <?php endforeach; ?>
                                                                    </optgroup>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>                                                        
                                                    </div>
                                                    <div class="row"> 
                                                        <div class="col-sm-8">
                                                        	<hr>
                                                            <div class="form-group">
                                                                <label>Preferred country:</label> 
                                                                <select name="applicant[preferred-country]" style="width:100%" required>
                                                                    <option value="">-- Select country --</option>
                                                                    <?php foreach ($countries as $country): ?>
                                                                    <option value="<?php echo $country['country_id']; ?>" <?php echo isset( $post['applicant']['preferred-country'] ) && $post['applicant']['preferred-country'] == $country['country_id'] ? 'selected' : '';  ?>><?php echo $country['country_name']; ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>                                                        
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-8">
                                                            <div class="form-group">
                                                                <label>Other preferred countries <small><em>(Press Enter to accept country)</em></small>:</label> 
                                                                <select name="applicant[other-preferred-countries][]" multiple style="width:100%">
                                                                    <option value="">-- Select country --</option>
                                                                    <?php foreach ($countries as $country): ?>
                                                                    <option value="<?php echo $country['country_id']; ?>" <?php echo isset( $post['applicant']['other-preferred-countries'] ) && in_array( $country['country_id'], $post['applicant']['other-preferred-countries']) ? 'selected' : '';  ?>><?php echo $country['country_name']; ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>                                                        
                                                    </div>
                                                    
                                                </div>
                                                <div class="col-sm-4">
                                                
                                                	<div class="row" >
                                                    	<div class="col-sm-10 col-sm-offset-2" align="center" style="border-left:1px dashed #999">
                                                        	<img width="100" class="img-responsive img-rounded profile-photo" src="<?php echo $app->getPath()['images']; ?>avatars/no-picture.jpg" alt="Upload photo" /><br />
                                                            <input class="hide" type="file" name="applicant[photo]"/>
                                                            <button type="button" class="btn btn-md btn-info btn-photo-browse">Browse photo ...</button>
                                                        </div> 
                                                    </div>                                                     
                                                     
                                                </div>
                                            </div>
                                        	
                                            
                                            <hr class="wide" />
                                        
                                            <div class="form-title">
                                                <strong>Basic Information</strong>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>First name</label>
                                                        <input type="text" name="applicant[basic][first]" class="form-control input-uppercase" placeholder="" value="<?php echo isset( $post['applicant']['basic']['first'] ) ? $post['applicant']['basic']['first'] : ''; ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Middle name</label>
                                                        <input type="text" name="applicant[basic][middle]" class="form-control input-uppercase" placeholder="" value="<?php echo isset( $post['applicant']['basic']['middle'] ) ? $post['applicant']['basic']['middle'] : ''; ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Last name</label>
                                                        <input type="text" name="applicant[basic][last]" class="form-control input-uppercase" placeholder="" value="<?php echo isset( $post['applicant']['basic']['last'] ) ? $post['applicant']['basic']['last'] : ''; ?>" required>
                                                    </div>
                                                </div>
                                            </div>
											
											
											
											  <div class="row">
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>Date of birth</label>
                                                        <span class="input-icon icon-right">
                                                            <div class="input-group">
                                                                <input name="applicant[basic][birthdate]" type="text" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo isset( $post['applicant']['basic']['birthdate'] ) ? $post['applicant']['basic']['birthdate'] : date( 'Y-m-d', time() ); ?>" required>
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-calendar"></i>
                                                                </span>
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>Civil status</label>
                                                        <select name="applicant[basic][status]" class="form-control" required>
                                                            <option value="Single">Single</option>
															<option value="Single With Children">Single With Children</option>
															 <option value="Separated">Separated</option>
                                                            <option value="Married">Married</option>
                                                        </select>
                                                    </div>
                                                </div>
												
												 
												
												
												
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>Gender</label>
                                                        <select name="applicant[basic][gender]" class="form-control" required>
                                                            <option value="Male">Male</option>
                                                            <option value="Female">Female</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
											
                                          
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Nationality</label>
                                                        <input type="text" name="applicant[basic][nationality]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['basic']['nationality'] ) ? $post['applicant']['basic']['nationality'] : ''; ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Religion / Belief</label>
                                                        <input type="text" name="applicant[basic][religion]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['basic']['religion'] ) ? $post['applicant']['basic']['religion'] : ''; ?>" >
                                                    </div>
                                                </div>
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>Height</label>
                                                        <input type="text" name="applicant[basic][height]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['basic']['height'] ) ? $post['applicant']['basic']['height'] : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>Weight</label>
                                                        <input type="text" name="applicant[basic][weight]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['basic']['weight'] ) ? $post['applicant']['basic']['weight'] : ''; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            
											
											
											
											
											
											
                                          
											
											  
                                                
                                            
											
											  <div class="row">
												 <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Email <small><em>(optional)</em></small></label>
                                                        <input type="email" name="applicant[basic][email]" class="form-control" placeholder="example@domain.com" value="<?php echo isset( $post['applicant']['basic']['email'] ) ? $post['applicant']['basic']['email'] : ''; ?>">
                                                    </div>
                                                </div>
											
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label for="">Contact number(s) <small><em>(Press Enter to accept number)</em></small></label>
                                                        <input name="applicant[basic][contacts]" type="text" data-role="tagsinput" placeholder="Add contact number" value="<?php echo isset( $post['applicant']['basic']['contacts'] ) ? $post['applicant']['basic']['contacts'] : ''; ?>" />
                                                    </div>
                                                </div>
												
												<div class="col-sm-4">
                                     				<div class="form-group">
                                                        <label for="">Languages <small><em>(Press Enter to accept language)</em></small></label>
                                                        <input name="applicant[basic][languages]" type="text" value="<?php echo isset( $post['applicant']['basic']['languages'] ) ? $post['applicant']['basic']['languages'] : 'English'; ?>" data-role="tagsinput" placeholder="Add language" />
                                                    </div>
                                                </div>
												
                                            </div>
											
											  <div class="row">
                                                <div class="col-sm-8">
                                                    <div class="form-group">
                                                        <label>Address</label>
                                                        <input type="text" name="applicant[basic][address]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['basic']['address'] ) ? $post['applicant']['basic']['address'] : ''; ?>" required>
                                                    </div>
                                                </div>
                                            </div>
											
											
											
											<div class="form-title">
                                                <strong>Spouse Information</strong>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Name of Partner</label>
                                                        <input type="text" name="applicant[basic][partner_husband]" class="form-control" placeholder="Name of Partner" value="<?php echo isset( $post['applicant']['basic']['partner_husband'] ) ? $post['applicant']['basic']['partner_husband'] : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Occupation of Partner</label>
                                                        <input type="text" name="applicant[basic][partner_occupation]" class="form-control" placeholder="Occupation of Partner" value="<?php echo isset( $post['applicant']['basic']['partner_occupation'] ) ? $post['applicant']['basic']['partner_occupation'] : ''; ?>">
                                                    </div>
                                                </div>
												
												 <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Children</label>
                                                        <input type="text" name="applicant[basic][applicant_children]" class="form-control" placeholder="example: 2 Girl 8 year(s) old,1 boy 2 year(s)" value="<?php echo isset( $post['applicant']['basic']['applicant_children'] ) ? $post['applicant']['basic']['applicant_children'] : ''; ?>">
                                                    </div>
                                              </div>
												
                                            </div>
											
											
											<div class="form-title">
                                                <strong>Family Background</strong>
                                            </div>

                                            <div class="row">
												<div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Name of Mother</label>
                                                        <input type="text" name="applicant[basic][applicant_mothers]" class="form-control" placeholder="Mother Name" value="<?php echo isset( $post['applicant']['basic']['applicant_mothers'] ) ? $post['applicant']['basic']['applicant_mothers'] : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Occupation of Mother</label>
                                                        <input type="text" name="applicant[basic][occ_of_mom]" class="form-control" placeholder="Mother Occupation" value="<?php echo isset( $post['applicant']['basic']['occ_of_mom'] ) ? $post['applicant']['basic']['occ_of_mom'] : ''; ?>">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Name of Father</label>
                                                        <input type="text" name="applicant[basic][nam_of_fat]" class="form-control" placeholder="Name of Father" value="<?php echo isset( $post['applicant']['basic']['nam_of_fat'] ) ? $post['applicant']['basic']['nam_of_fat'] : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Occupation of Father</label>
                                                        <input type="text" name="applicant[basic][occ_of_fat]" class="form-control" placeholder="Father Occupation" value="<?php echo isset( $post['applicant']['basic']['occ_of_fat'] ) ? $post['applicant']['basic']['occ_of_fat'] : ''; ?>">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>Your Position in your Family</label>
                                                        <input type="text" name="applicant[basic][pos_in_fam]" class="form-control" placeholder="Your Position in your Family" value="<?php echo isset( $post['applicant']['basic']['pos_in_fam'] ) ? $post['applicant']['basic']['pos_in_fam'] : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>No. of Brothers</label>
                                                        <input type="text" name="applicant[basic][no_of_bro]" class="form-control" placeholder="No. of Brothers" value="<?php echo isset( $post['applicant']['basic']['no_of_bro'] ) ? $post['applicant']['basic']['no_of_bro'] : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>No. of Sisters</label>
                                                        <input type="text" name="applicant[basic][no_of_sis]" class="form-control" placeholder="No. of Sisters" value="<?php echo isset( $post['applicant']['basic']['no_of_sis'] ) ? $post['applicant']['basic']['no_of_sis'] : ''; ?>">
                                                    </div>
                                                </div>
                                            </div>
											

										<div class="form-title">
                                                <strong>Upon Deployment</strong>
                                            </div>			

                                            <div class="row">
                                                <div class="col-sm-5">
                                                    <div class="form-group">
                                                        <label>Who will look after the children when you will be overseas?</label>
                                                        <input type="text" name="applicant[basic][relative_name]" class="form-control" placeholder="Who will look after the children when you will be overseas?" value="<?php echo isset( $post['applicant']['basic']['relative_name'] ) ? $post['applicant']['basic']['relative_name'] : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Name and Mobile No. of relative to contact?</label>
                                                        <input type="text" name="applicant[basic][relative_mobile]" class="form-control" placeholder="Name and Mobile No. of relative to contact?" value="<?php echo isset( $post['applicant']['basic']['relative_mobile'] ) ? $post['applicant']['basic']['relative_mobile'] : ''; ?>">
                                                    </div>
                                                </div>
                                            </div>
												
                                          
                                         
                                            
                                            <hr class="wide" />
                                        
                                            <div class="form-title">
                                               <strong> Passport Information</strong>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Passport number</label>
                                                        <input type="text" name="applicant[passport][number]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['passport']['number'] ) ? $post['applicant']['passport']['number'] : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Expiration</label>
                                                        <span class="input-icon icon-right">
                                                            <div class="input-group">
                                                                <input name="applicant[passport][expiration]" type="text" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo isset( $post['applicant']['passport']['expiration'] ) ? $post['applicant']['passport']['expiration'] : date( 'Y-m-d', time() ); ?>">
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-calendar"></i>
                                                                </span>
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Issue place</label>
                                                        <input type="text" name="applicant[passport][issue-place]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['passport']['issue-place'] ) ? $post['applicant']['passport']['issue-place'] : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Date issued</label>
                                                        <span class="input-icon icon-right">
                                                            <div class="input-group">
                                                                <input name="applicant[passport][issue]" type="text" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo isset( $post['applicant']['passport']['issue'] ) ? $post['applicant']['passport']['issue'] : date( 'Y-m-d', time() ); ?>">
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-calendar"></i>
                                                                </span>
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <hr class="wide" />
                                            <div class="form-title">
                                                <strong>Educational Background</strong>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>MBA</label>
                                                        <input type="text" name="applicant[education][mba]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['education']['mba'] ) ? $post['applicant']['education']['mba'] : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Course</label>
                                                        <input type="text" name="applicant[education][mba-course]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['education']['mba-course'] ) ? $post['applicant']['education']['mba-course'] : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>Year</label>
                                                        <input type="text" name="applicant[education][mba-year]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['education']['mba-year'] ) ? $post['applicant']['education']['mba-year'] : ''; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>College</label>
                                                        <input type="text" name="applicant[education][college]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['education']['college'] ) ? $post['applicant']['education']['college'] : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Skills</label>
                                                        <input type="text" name="applicant[education][college-skills]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['education']['college-skills'] ) ? $post['applicant']['education']['college-skills'] : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>Year</label>
                                                        <input type="text" name="applicant[education][college-year]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['education']['college-year'] ) ? $post['applicant']['education']['college-year'] : ''; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-sm-8">
                                                    <div class="form-group">
                                                        <label>Others</label>
                                                        <input type="text" name="applicant[education][others]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['education']['others'] ) ? $post['applicant']['education']['others'] : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>Year</label>
                                                        <input type="text" name="applicant[education][others-year]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['education']['others-year'] ) ? $post['applicant']['education']['others-year'] : ''; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-sm-8">
                                                    <div class="form-group">
                                                        <label>Highschool</label>
                                                        <input type="text" name="applicant[education][highschool]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['education']['highschool'] ) ? $post['applicant']['education']['highschool'] : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>Year</label>
                                                        <input type="text" name="applicant[education][highschool-year]" class="form-control" placeholder="" value="<?php echo isset( $post['applicant']['education']['highschool-year'] ) ? $post['applicant']['education']['highschool-year'] : ''; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <hr class="wide" />
                                            <div class="form-title">
                                                <strong>Work Experience</strong>
                                            </div>
                                            <div class="work-experience-start">
                                                <div class="row work-experience">
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label>Company</label>
                                                            <input type="text" name="applicant[work-experience][company][]" class="form-control" placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label>Position</label>
                                                            <div class="cyd-copy-posi">
                                                                <select name="applicant[work-experience][position][]" style="width:100%">
                                                                    <option value="">-- Select position --</option>
                                                                    <?php foreach ($categories as $category): ?>
                                                                    <?php if ( count( $category['positions'] ) == 0 ) continue; ?>
                                                                    <optgroup label="<?php echo $category['category_name']; ?>">
                                                                    <?php foreach ($category['positions'] as $position): ?>
                                                                    <option value="<?php echo $position['position_name']; ?>" <?php echo isset( $post['applicant']['preferred-position'] ) && $post['applicant']['preferred-position'] == $position['position_id'] ? 'selected' : '';  ?>><?php echo $position['position_name']; ?></option>
                                                                    <?php endforeach; ?>
                                                                    </optgroup>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label>Salary</label>
                                                            <input type="text" name="applicant[work-experience][salary][]" class="form-control" placeholder="0.00">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label>Country</label>
                                                            <div class="cyd-copy-country">
                                                                <select name="applicant[work-experience][country][]" style="width:100%">
                                                                    <option value="">-- Select country --</option>
                                                                    <?php foreach ($countries as $country): ?>
                                                                    <option value="<?php echo $country['country_name']; ?>" <?php echo isset( $post['applicant']['preferred-country'] ) && $post['applicant']['preferred-country'] == $country['country_id'] ? 'selected' : '';  ?>><?php echo $country['country_name']; ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <div class="form-group">
                                                            <label>From</label>
                                                            <input name="applicant[work-experience][from][]" type="text" data-date-format="yyyy-mm-dd" class="form-control input-sm date-picker work-started cyd-work-started-1" placeholder="yyyy-mm-dd" value="">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <div class="form-group">
                                                            <label>To</label>
                                                            <input name="applicant[work-experience][to][]" type="text" data-date-format="yyyy-mm-dd" class="form-control input-sm date-picker work-ended cyd-work-ended-1" placeholder="yyyy-mm-dd" value="">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-1">
                                                        <div class="form-group">
                                                            <label>Years</label>
                                                            <input type="text" name="applicant[work-experience][years][]" class="form-control cyd-work-years-1" placeholder="0" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-1">
                                                    	<a href="#" role="button" class="btn btn-danger btn-xs">Remove</a>
                                                    </div>
                                                </div>
                                                <div class="work-experience-separator"></div>
                                            </div><!-- .work-experience-start -->
                                            
                                            <div>
                                            	<hr />
                                            	<a href="#" id="btn-add-work-experience" role="button" class="btn btn-xs btn-default"><i class="fa fa-plus"></i> Add work experience</a>
                                                &nbsp;<span class="pull-right">Total years experience: <span class="text-danger cyd-work-experience-years">0</span></span>
                                            </div>

                                            <?php /*Temporary HIDE GENERAL Info
                                            <hr class="wide" />
                                            <div class="form-title">
                                                <strong>GENERAL INFORMATION</strong>
                                            </div>
                                            <?php 
                                            foreach ($survey_array->survey as $survey_value) { ?>
                                            <div class="row">
                                                <div class="col-sm-10">
                                                    <div class="form-group cyd_survey">
                                                        <label><?php echo $survey_value->name; ?></label>
                                                        <div class="checkbox-inline pull-right">
                                                            <label>
                                                                <input type="radio" name="applicant[survey][<?php echo $survey_value->string ?>]" value="0" checked>
                                                                No
                                                            </label>
                                                        </div>
                                                        <div class="checkbox-inline pull-right">
                                                            <label>
                                                                <input type="radio" name="applicant[survey][<?php echo $survey_value->string ?>]" value="1">
                                                                Yes
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                            <?php }//end survey array ?>
                                            */ ?>

                                            <hr class="wide" />
                                            <div class="form-title">
                                                <strong>WORKING EXPERIENCE</strong>
                                            </div>
                                            <?php 
                                            foreach ($wexp_array->working_experience as $wexp_value) { ?>
                                            <div class="row">
                                                <div class="col-sm-10">
                                                    <div class="form-group">
                                                        <label><?php echo $wexp_value->name; ?> <small><em>(optional)</em></small></label>
                                                        <span class="input-icon icon-right">
                                                            <textarea name="applicant[survey][<?php echo $wexp_value->string; ?>]" class="form-control" rows="4" placeholder="<?php echo $wexp_value->name; ?>"><?php echo isset( $post['applicant'][$wexp_value->string] ) ? $post['applicant'][$wexp_value->string] : ''; ?></textarea>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                            <?php }//end working experience array ?>

                                            <?php /*Temporary hide WORKING ABILITY
                                            <hr class="wide" />
                                            <div class="form-title">
                                                <strong>WORKING ABILITY</strong>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-6">
                                                    WORK
                                                </div>
                                                <div class="col-sm-2">
                                                    EXPERIENCED
                                                </div>
                                                <div class="col-sm-2">
                                                    WILLINGNESS
                                                </div>
                                            </div>
                                            <hr/>
                                            <?php  foreach ($wabl_array->working_ability as $wabl_value) { ?>
                                            <div class="row cyd_working_ability">
                                                <div class="col-sm-6">
                                                    <?php echo $wabl_value->name; ?>
                                                </div>
                                                <div class="col-sm-2">
                                                    <input type="checkbox" name="applicant[survey][<?php echo $wabl_value->exp; ?>]" value="option1"> yes
                                                </div>
                                                <div class="col-sm-2">
                                                    <input type="checkbox" name="applicant[survey][<?php echo $wabl_value->will; ?>]" value="option1"> yes
                                                </div>
                                            </div>
                                            <hr/>
                                            <?php }//end working ability array ?>
                                            */ ?>

                                            <hr class="wide" />
                                            <div class="form-title">
                                                <strong>FUTURE PLANS</strong>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-10">
                                                    <div class="form-group">
                                                        <label>Why do you want to go and work overseas?</label>
                                                        <span class="input-icon icon-right">
                                                            <textarea name="applicant[survey][future_plans]" class="form-control" rows="4" placeholder="Why do you want to go and work overseas?"><?php echo isset( $post['applicant']['future_plans'] ) ? $post['applicant']['future_plans'] : ''; ?></textarea>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr class="wide" />
                                            
                                            <div class="form-title">
                                                <strong>Other Skills </strong>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-sm-10">
                                     				<div class="form-group">
                                                        <label>Skills <small><em>(Press Enter to accept skills)</em></small></label>
                                                        <input name="applicant[other-skills]" type="text"  data-role="tagsinput" value="<?php echo isset( $post['applicant']['other-skills'] ) ? $post['applicant']['other-skills'] : ''; ?>">
                                                    </div>
                                                    
                                                </div>
                                                
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-sm-10">
                                                	<div class="form-group">
                                                        <label>Remarks <small><em>(optional)</em></small></label>
                                                        <span class="input-icon icon-right">
                                                            <textarea name="applicant[remarks]" class="form-control" rows="4" placeholder="Write some remarks..."><?php echo isset( $post['applicant']['remarks'] ) ? $post['applicant']['remarks'] : ''; ?></textarea>
                                                            <i class="fa fa-briefcase darkorange"></i>
                                                        </span>
                                                        <!--<p class="help-block">Write some remarks</p>-->
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <hr class="wide" />
                                            <div class="row">
                                            	<div class="col-sm-2">
                                                    <div class="form-group">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input name="applicant[type]" type="radio" value="Household" class="colored-blue" <?php echo isset( $post['applicant']['type'] ) && $post['applicant']['type'] == 'Skilled' ? '' : 'checked'; ?> >
                                                                <span class="text">Household</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input name="applicant[type]" type="radio" value="Skilled" class="colored-blue" <?php echo isset( $post['applicant']['type'] ) && $post['applicant']['type'] == 'Skilled' ? 'checked' : ''; ?> >
                                                                <span class="text">Skilled</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <hr class="wide" />
                                            <div class="row">
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>Date applied</label>
                                                        <span class="input-icon icon-right">
                                                            <div class="input-group">
                                                                <input name="applicant[date-applied]" type="text" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo isset( $post['applicant']['date-applied'] ) ? $post['applicant']['date-applied'] : date( 'Y-m-d', time() ); ?>">
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-calendar"></i>
                                                                </span>
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>Source</label>
                                                        <select name="applicant[source]" class="form-control">
                                                            <option value="0" disabled>-- Source --</option>
                                                            <option value="0" selected >DIRECT HIRE</option>
                                                            <?php foreach ( $agents as $agent ): ?>
                                                            <option value="<?php echo $agent['agent_id']; ?>" <?php echo isset( $post['applicant']['source'] ) && $post['applicant']['source'] == $agent['agent_id'] ? 'selected' : ''; ?>><?php echo $agent['agent_first'].' '.$agent['agent_last']; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
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
            </div>
        </div>
    </div>
</div>
<!-- /Page Body -->