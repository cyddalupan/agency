<?php $app->renderStyles(); ?>

<?php 
// Get Json Files to array
$subStatus = cydGetJson("addSubStatus.json");
?> 

<!-- Page Body -->
<div class="page-body" style="padding:0px !important;">
    <div class="row">
        <div class="col-md-12">
            <div class="profile-container">
                <div class="profile-header row">
<div class="pull-right" style="padding:2px;font-text:11px">
<a href="<?php echo site_url(); ?>reports/cert/pdos.php?pid=<?=$applicant['applicant_id'];?>" target="_blank" >PRINT PDOS</a>
| <a href="<?php echo site_url(); ?>reports/cert/general-information.php?pid=<?=$applicant['applicant_id'];?>" target="_blank" >GENERAL INFORMATION</a>
</div>
                    <div class="col-lg-2 col-md-2 col-sm-12 text-center">
                        <?php if ( is_file( DIR_UPLOADS.'applicant'.DIRECTORY_SEPARATOR.$applicant['applicant_photo'] ) ): ?>
                            <img src="<?php echo base_url(); ?>files/applicant/<?php echo $applicant['applicant_photo']; ?>" alt="" class="header-avatar profile-photo" />
                        <?php else: ?>
                            <img src="<?php echo $app->getPath()['images']; ?>avatars/no-picture.jpg" alt="" class="header-avatar profile-photo" />
                        <?php endif; ?>
                        
                        <div class="button-container">
                            <p>[<a href="#" role="button" class="btn-photo-browse">Change picture...</a>]</p>
                        </div>
                        <div class="form-container hide">
                            <form id="frmUploadPhoto" class="form-except" method="post" enctype="multipart/form-data" action="<?php echo site_url('admin/applicants/review/'.$applicant['applicant_id']) ?>">
                                <input type="hidden" name="applicant[applicant_id]" value="<?php echo $applicant['applicant_id']; ?>">
                                <input type="file" class="hide" name="applicant[photo]">
                                <button type="submit" class="btn-xs btn-info">Upload</button> <br>or<br> [<a href="#" role="button" class="btn-photo-browse">Change picture...</a>]
                            </form>
                            <p>&nbsp;</p>
                        </div>

                    </div>
                    <div class="col-lg-5 col-md-5 col-sm-12 profile-info">
                        <div class="header-fullname"><?php echo $applicant['applicant_name']; ?></div>
                        <span class="label label-<?php echo $statusColors[ $applicant['applicant_status'] ]; ?> btn-sm pull-right" style="margin-top:25px;"><?php echo $statusText[$applicant['applicant_status']]; ?></span>
                      
                        <div class="header-information">
                            <div class="clearfix"></div>
                            Born <?php echo date( 'd M Y', strtotime( $applicant['applicant_birthdate'] ) ); ?>, <?php echo $applicant['applicant_civil_status']; ?>,
                            <?php if ( ! empty( $applicant['applicant_email'] ) ): ?>
                            <a href="mailto:<?php echo $applicant['applicant_email']; ?>"><?php echo $applicant['applicant_email']; ?></a>
                            <?php endif; ?>
							
							<address><?php echo $applicant['applicant_address']; ?>
							 <div class="clearfix"></div>
								Contact Details: <?php echo $applicant['applicant_contacts']; ?>
								 <div class="clearfix"></div>
								Foreign Principal/Company:  <b> <?php echo $applicant['employer_name']; ?> </b>
								 <div class="clearfix"></div>
							
							</address>
                        
						</div>
                        
                        <?php if ( $applicant['requirement_job_offer'] > 0 && $applicant['applicant_status'] >= $status['Selected'] ): ?>
                        <div class="">
                            <a href="<?php echo site_url( 'admin/billing/worker-soa/'.$applicant['applicant_slug'] ); ?>" class="btn btn-info pull-right">Open Billing</a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 profile-stats">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 stats-col">
                                <div class="stats-value pink">
								    <?php echo $applicant['position_name']; ?>
                               
                                </div>
                                <div class="stats-title">POSITION</div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 stats-col">
							
							 <?php if ( ! $applicant['applicant_source'] ): ?>
                                DIRECT HIRE
                                <?php else: ?>
                                <?php echo $applicant['agent_first'].' '.$applicant['agent_last']; ?>
                                <?php endif;?>
							 <div class="stats-title"><B>SOURCE</B></div>
                                <div class="stats-value pink"><?php echo count( $applicant['files'] ); ?></div>
                                <div class="stats-title">SCANNED FILES</div>
                            </div> 
                        </div>
                        <div class="row">
                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6 inlinestats-col">
                                Date Applied:<br><?php echo date( 'd M Y', strtotime( $applicant['applicant_date_applied'] ) ); ?>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6 inlinestats-col">
                                Expected Salary:<br><strong><?php echo ($applicant['applicant_expected_salary']); ?></strong>
                                <br/><small class="cyd_show_currency"></small>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6 inlinestats-col">
                                Age: <strong><?php echo $applicant['applicant_age']; ?></strong>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6 inlinestats-col">
                                <?php if ( $applicant['applicant_paid'] ): ?>
                                <span class="label label-danger">PAID <i class="fa fa-check"></i></span>
                                <?php else: ?>
                                Balance: <strong><?php echo number_format( $applicant['balance'], 2 ); ?></strong>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="profile-body">
                    <div class="col-lg-12">
                        <div class="tabbable">
                            <ul class="nav nav-tabs tabs-flat  nav-justified" id="myTab11">
                                <li class="<?php echo isset( $_GET['ref_form'] ) && $_GET['ref_form'] != 'profile' ? '' : 'active'; ?>">
                                    <a data-toggle="tab" href="#profile">
                                        Profile
                                    </a>
                                </li>
                                <?php if (!in_array( $_SESSION['admin']['user']['user_type'], [11,12,9999]) ): ?>
                                <li class="tab-red <?php echo isset( $_GET['ref_form'] ) && $_GET['ref_form'] == 'certifications' ? 'active' : ''; ?>">
                                    <a data-toggle="tab" href="#certifications">
                                        Requirements
                                    </a>
                                </li>
                                 <?php endif; ?>
								<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [ 3,4, 6, 7,9,10]) ): ?>
                                <li class="tab-palegreen <?php echo isset( $_GET['ref_form'] ) && $_GET['ref_form'] == 'requirements' ? 'active' : ''; ?>">
                                    <a data-toggle="tab" href="#requirements">
                                        Processing
                                    </a>
                                </li>
								 <?php endif; ?>

                                <?php if ( in_array( $_SESSION['admin']['user']['user_type'], [ 3,4, 6, 7,9,10,9999]) ): ?>
								<li class="tab-yellow <?php echo isset( $_GET['ref_form'] ) && $_GET['ref_form'] == 'documents' ? 'active' : ''; ?>">
                                    <a data-toggle="tab" href="#documents">
                                        Softcopy Documents
                                    </a>
                                </li>
								 <?php endif; ?>
                                 
                                <?php if (!in_array( $_SESSION['admin']['user']['user_type'], [11,12,9999]) ): ?>
                                <li class="tab-yellow <?php echo isset( $_GET['ref_form'] ) && $_GET['ref_form'] == 'status' ? 'active' : ''; ?>">
                                    <a data-toggle="tab" href="#status">
                                        Status
                                    </a>
                                </li>
                                 <?php endif; ?>
                            </ul>
                            <div class="tab-content tabs-flat">
                                <!-- Profile -->
                                <div id="profile" class="tab-pane <?php echo isset( $_GET['ref_form'] ) && $_GET['ref_form'] != 'profile' ? '' : 'active'; ?>">
                                    <form id="frmApplicantReview" class="form" role="form" method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="flag" value="profile">
                                        <input type="hidden" name="applicant_id" value="<?php echo $applicant['applicant_id']; ?>">
                                        
                                        <?php if(($_SESSION["settings"]['withTraining'] == 'yes') && in_array( $_SESSION['admin']['user']['user_type'], [11,12] ) ){ ?>
                                        <div class="form-title">
                                           Training <span>( <a href="#" class="btn-change-designation cyd_load_js" data-target=".training-container">Edit <i class="fa fa-pencil"></i></a> )</span>
                                           <div class="clearfix"></div>
                                        </div>
                                        <div class="training-container hide">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Training Branch</label> 
                                                        <select name="applicant[training-branch]" style="width:100%">
                                                            <?php foreach ($trainingBranches as $trainingKey => $trainingBranch) {

                                                                if($applicant_raw->training_branches_id == $trainingKey )
                                                                    $isBranchSelected = 'selected';
                                                                else
                                                                    $isBranchSelected = '';

                                                                echo '<option value="'.$trainingKey.'" '.$isBranchSelected.'>'.$trainingBranch.'</option>';
                                                            } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Training Status</label> 
                                                        <select name="applicant[training-status]" style="width:100%">
                                                            <option value="<?php echo $applicant['applicant_status']; ?>"><?php echo $statusText[$applicant['applicant_status']]; ?></option>
                                                            <option value="16">Enrolled to training</option>
                                                            <option value="17">Started training</option>
                                                            <option value="18">Failed Training</option>
                                                            <option value="19">Graduate Training</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Training Start date</label>
                                                        <span class="input-icon icon-right">
                                                            <div class="input-group">
                                                                <input name="applicant[training-start]" type="text" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo fdate( 'Y-m-d', $applicant_raw->start_training_at, '0000-00-00' ); ?>">
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-calendar"></i>
                                                                </span>
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Training End date</label>
                                                        <span class="input-icon icon-right">
                                                            <div class="input-group">
                                                                <input name="applicant[training-end]" type="text" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo fdate( 'Y-m-d', $applicant_raw->end_training_at, '0000-00-00' ); ?>">
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-calendar"></i>
                                                                </span>
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>Training Remarks</label>
                                                        <input  type="text" class="form-control" name="applicant[training-remarks]" placeholder="Training Remarks" value="<?php echo $applicant_raw->training_remarks; ?>" maxlength="255" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php }//if training is yes ?>

                                        <div class="form-title">
                                           Preferred Designation <span>( <a href="#" class="btn-change-designation cyd_load_js" data-target=".designation-container">Edit <i class="fa fa-pencil"></i></a> )</span>
                                           <div class="clearfix"></div>
                                        </div>
                                        
                                        <div class="designation-container hide">
                                            <div class="row">
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Preferred position</label> 
                                                    <select name="applicant[preferred-position]" style="width:100%" required>
                                                        <option value="">-- Select position --</option>
                                                        <?php foreach ($categories as $category): ?>
                                                        <optgroup label="<?php echo $category['category_name']; ?>">
                                                        <?php foreach ($category['positions'] as $position): ?>
                                                        <option value="<?php echo $position['position_id']; ?>" <?php echo isset( $post['applicant']['preferred-position'] ) && $post['applicant']['preferred-position'] == $position['position_id'] || $applicant['applicant_preferred_position'] == $position['position_id'] ? 'selected' : '';  ?>><?php echo $position['position_name']; ?></option>
                                                        <?php endforeach; ?>
                                                        </optgroup>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="form-group">
                                                    <label>Other preferred positions <small><em>(Press Enter to accept position)</em></small>:</label> 
                                                    <select name="applicant[other-preferred-positions][]" multiple style="width:100%">
                                                        <option value="">-- Select position --</option>
                                                        <?php foreach ($categories as $category): ?>
                                                        <optgroup label="<?php echo $category['category_name']; ?>">
                                                        <?php foreach ($category['positions'] as $position): ?>
                                                        <option value="<?php echo $position['position_id']; ?>" <?php echo ( isset( $post['applicant']['other-preferred-positions'] ) && in_array( $position['position_id'], $post['applicant']['other-preferred-positions'] ) ) || array_key_exists( $position['position_id'], $applicant['other-preferred-positions'] ) ? 'selected' : '';  ?>><?php echo $position['position_name']; ?></option>
                                                        <?php endforeach; ?>
                                                        </optgroup>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Preferred country</label> 
                                                    <select name="applicant[preferred-country]" style="width:100%" required>
                                                        <option value="">-- Select country --</option>
                                                        <?php foreach ($countries as $country): ?>
                                                        <option value="<?php echo $country['country_id']; ?>" <?php echo isset( $post['applicant']['preferred-country'] ) && $post['applicant']['preferred-country'] == $country['country_id'] || $applicant['applicant_preferred_country'] == $country['country_id'] ? 'selected' : '';  ?>><?php echo $country['country_name']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="form-group">
                                                    <label>Other preferred countries <small><em>(Press Enter to accept country)</em></small>:</label> 
                                                    <select name="applicant[other-preferred-countries][]" multiple style="width:100%">
                                                        <option value="">-- Select country --</option>
                                                        <?php foreach ($countries as $country): ?>
                                                        <option value="<?php echo $country['country_id']; ?>" <?php echo isset( $post['applicant']['other-preferred-countries'] ) && in_array( $country['country_id'], $post['applicant']['other-preferred-countries']) || array_key_exists( $country['country_id'], $applicant['other-preferred-countries'] ) ? 'selected' : '';  ?>><?php echo $country['country_name']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Expected salary</label> 
                                                    <input name="applicant[expected-salary]" type="number" class="form-control" min="0" placeholder="0.00" value="<?php echo $applicant['applicant_expected_salary']; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Currency</label><br/>
                                                    <select name="applicant[currency]">
                                                        <?php foreach ($currencies as $currency) { ?>
                                                            <option><?php echo $currency->currency; ?></option>
                                                        <?php }//end foreach ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                            <div class="row">
                                                <?php show_customField($customFields,"Preferred Designation",$applicant['applicant_id']); ?>
                                            </div>

                                        </div>
                                        
                                        <hr class="wide">
                                        <div class="form-title">
                                           Basic Information <span>( <a href="#" class="btn-change-education" data-target=".basic-container">Edit <i class="fa fa-pencil"></i></a> )</span>
                                           
                                           <div class="clearfix"></div>
                                        </div>
                                        <div class="basic-container hide">
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>First name</label>
                                                        <input type="text" name="applicant[basic][first]" class="form-control" placeholder="First" value="<?php echo $applicant['applicant_first']; ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Middle name</label>
                                                        <input type="text" name="applicant[basic][middle]" class="form-control" placeholder="Middle" value="<?php echo $applicant['applicant_middle']; ?>" >
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Last name</label>
                                                        <input type="text" name="applicant[basic][last]" class="form-control" placeholder="Last" value="<?php echo $applicant['applicant_last']; ?>" required>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>Date of birth</label>
                                                        <span class="input-icon icon-right">
                                                            <div class="input-group">
                                                                <input name="applicant[basic][birthdate]" type="text" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo fdate( 'Y-m-d', $applicant['applicant_birthdate'], '0000-00-00' ); ?>" required>
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-calendar"></i>
                                                                </span>
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>Civil status</label>
                                                        <select name="applicant[basic][status]" class="form-control" required>
                                                            <option value="Single" <?php echo ( isset( $post['applicant']['basic']['status'] ) && $post['applicant']['basic']['status'] == 'Single' ) || $applicant['applicant_civil_status'] == 'Single' ? 'selected' : ''; ?>>Single</option>
                                                            <option value="Married" <?php echo ( isset( $post['applicant']['basic']['status'] ) && $post['applicant']['basic']['status'] == 'Married' ) || $applicant['applicant_civil_status'] == 'Married' ? 'selected' : ''; ?>>Married</option>
                                                            
                                                            <option value="Single With Children" <?php echo ( isset( $post['applicant']['basic']['status'] ) && $post['applicant']['basic']['status'] == 'Single With Children' ) || $applicant['applicant_civil_status'] == 'Single With Children' ? 'selected' : ''; ?>>Single With Children</option>
                                                            
                                                            <option value="Separated" <?php echo ( isset( $post['applicant']['basic']['status'] ) && $post['applicant']['basic']['status'] == 'Separated' ) || $applicant['applicant_civil_status'] == 'Separated' ? 'selected' : ''; ?>>Separated</option>
                                                            
                                                        </select>
                                                    </div>
                                                </div>
                                                
                                            
                                                
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>Gender</label>
                                                        <select name="applicant[basic][gender]" class="form-control" required>
                                                            <option value="Male" <?php echo ( isset( $post['applicant']['basic']['gender'] ) && $post['applicant']['basic']['status'] == 'Male' ) || $applicant['applicant_gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                                            <option value="Female" <?php echo ( isset( $post['applicant']['basic']['gender'] ) && $post['applicant']['basic']['status'] == 'Female' ) || $applicant['applicant_gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            

                                            
                                            <div class="row">
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>Nationality</label>
                                                        <input type="text" name="applicant[basic][nationality]" class="form-control" placeholder="Nationality" value="<?php echo isset( $post['applicant']['basic']['nationality'] ) ? $post['applicant']['basic']['nationality'] : $applicant['applicant_nationality']; ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>Religion / Belief</label>
                                                        <input type="text" name="applicant[basic][religion]" class="form-control" placeholder="Religion" value="<?php echo isset( $post['applicant']['basic']['religion'] ) ? $post['applicant']['basic']['religion'] : $applicant['applicant_religion']; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-1">
                                                    <div class="form-group">
                                                        <label>Height</label>
                                                        <input type="text" name="applicant[basic][height]" class="form-control" placeholder="Height" value="<?php echo isset( $post['applicant']['basic']['height'] ) ? $post['applicant']['basic']['height'] : $applicant['applicant_height']; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-1">
                                                    <div class="form-group">
                                                        <label>Weight</label>
                                                        <input type="text" name="applicant[basic][weight]" class="form-control" placeholder="Weight" value="<?php echo isset( $post['applicant']['basic']['weight'] ) ? $post['applicant']['basic']['weight'] : $applicant['applicant_weight']; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            
                                            
                                               <div class="row">
                                                 <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Email <small><em>(optional)</em></small></label>
                                                        <input type="email" name="applicant[basic][email]" class="form-control" placeholder="example@domain.com" value="<?php echo isset( $post['applicant']['basic']['email'] ) ? $post['applicant']['basic']['email'] : $applicant['applicant_email']; ?>">
                                                    </div>
                                                </div>
                                            
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label for="">Contact number(s) <small><em>(Press Enter to accept number)</em></small></label>
                                                        <input type="text" name="applicant[basic][contacts]" data-role="tagsinput" placeholder="Add contact number" value="<?php echo isset( $post['applicant']['basic']['contacts'] ) ? $post['applicant']['basic']['contacts'] : $applicant['applicant_contacts']; ?>" />
                                                    </div>
                                                </div>
                                                
                                                 <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label for="">Languages <small><em>(Press Enter to accept language)</em></small></label>
                                                        <input name="applicant[basic][languages]" type="text" data-role="tagsinput" placeholder="Add language" value="<?php echo isset( $post['applicant']['basic']['languages'] ) ? $post['applicant']['basic']['languages'] : $applicant['applicant_languages']; ?>" />
                                                    </div>
                                                </div>
                                                
                                            </div>                                         
                                           
                                            

                                            
                                                 <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="form-group">
                                                        <label>Address</label>
                                                        <input type="text" name="applicant[basic][address]" class="form-control" placeholder="Address" value="<?php echo isset( $post['applicant']['basic']['address'] ) ? $post['applicant']['basic']['address'] : $applicant['applicant_address']; ?>" required>
                                                    </div>
                                                </div>
                                            </div>




                                        
                                                <h5>  <strong>Spouse Information</strong> </h5> 
                                         
                                            
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Name of Partner</label>
                                                        <input type="text" name="applicant[basic][partner_husband]" class="form-control" placeholder="Name of Partner" value="<?php if(isset($applicant_alphatomo->nam_of_fat))echo $applicant_alphatomo->partner_husband; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Occupation of Partner</label>
                                                        <input type="text" name="applicant[basic][partner_occupation]" class="form-control" placeholder="Occupation of Partner" value="<?php if(isset($applicant_alphatomo->nam_of_fat))echo $applicant_alphatomo->partner_occupation; ?>">
                                                    </div>
                                                </div>
                                                
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Children(s)</label>
                                                        <input type="text" name="applicant[basic][children]" class="form-control" placeholder="" value="<?php echo $applicant_raw->applicant_children; ?>">
                                                    </div>
                                                </div>
                                                
                                            </div>
                                            

                                    
                                                 <h5> <strong>Family Background</strong> </h5> 
                                          
                                                
                                           <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Name of Mother</label>
                                                        <input type="text" name="applicant[basic][applicant_mothers]" class="form-control" placeholder="Mother Name" value="<?php if(isset($applicant_raw->applicant_mothers))echo $applicant_raw->applicant_mothers; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Occupation of Mother</label>
                                                        <input type="text" name="applicant[basic][occ_of_mom]" class="form-control" placeholder="Mother Occupation" value="<?php if(isset($applicant_alphatomo->occ_of_mom))echo $applicant_alphatomo->occ_of_mom; ?>">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Name of Father</label>
                                                        <input type="text" name="applicant[basic][nam_of_fat]" class="form-control" placeholder="Name of Father" value="<?php if(isset($applicant_alphatomo->nam_of_fat))echo $applicant_alphatomo->nam_of_fat; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Occupation of Father</label>
                                                        <input type="text" name="applicant[basic][occ_of_fat]" class="form-control" placeholder="Father Occupation" value="<?php if(isset($applicant_alphatomo->occ_of_fat))echo $applicant_alphatomo->occ_of_fat; ?>">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>Your Position in your Family</label>
                                                        <input type="text" name="applicant[basic][pos_in_fam]" class="form-control" placeholder="Your Position in your Family" value="<?php if(isset($applicant_alphatomo->pos_in_fam))echo $applicant_alphatomo->pos_in_fam; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>No. of Brothers</label>
                                                        <input type="text" name="applicant[basic][no_of_bro]" class="form-control" placeholder="No. of Brothers" value="<?php if(isset($applicant_alphatomo->occ_of_fat))echo $applicant_alphatomo->no_of_bro; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>No. of Sisters</label>
                                                        <input type="text" name="applicant[basic][no_of_sis]" class="form-control" placeholder="No. of Sisters" value="<?php if(isset($applicant_alphatomo->occ_of_fat))echo $applicant_alphatomo->no_of_sis; ?>">
                                                    </div>
                                                </div>
                                            </div>

                                               <h5> <strong>Upon Deployment</strong>  </h5> 
                                           
                                            <div class="row">
                                                <div class="col-sm-5">
                                                    <div class="form-group">
                                                        <label>Who will look after the children when you will be overseas?</label>
                                                        <input type="text" name="applicant[basic][relative_name]" class="form-control" placeholder="Who will look after the children when you will be overseas?" value="<?php if(isset($applicant_alphatomo->relative_name))echo $applicant_alphatomo->relative_name; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Name and Mobile No. of relative to contact?</label>
                                                        <input type="text" name="applicant[basic][relative_mobile]" class="form-control" placeholder="Name and Mobile No. of relative to contact?" value="<?php if(isset($applicant_alphatomo->relative_mobile))echo $applicant_alphatomo->relative_mobile; ?>">
                                                    </div>
                                                </div>
                                            </div>

                                            <br><br>
                                            
                                            <div class="row">
                                                <?php show_customField($customFields,"Basic Information",$applicant['applicant_id']); ?>
                                            </div>
                                        </div>


                                        <hr class="wide">
                                        
                                        <div class="form-title">
                                            <div class="checkbox pull-right">
                                                <label>
                                                    <input type="checkbox" class="colored-blue chk-visible" data-scope="passport" data-applicant="<?php echo $applicant['applicant_id']; ?>" <?php echo $applicant['passport_visible'] ? 'checked' : ''; ?>>
                                                    <span class="text"><small>Visible to employer</small></span>
                                                </label>
                                            </div>
                                            Incase of Emergency <span>( <a href="#" class="btn-change-designation" data-target=".emergency-container"> Edit <i class="fa fa-pencil"></i> </a> )</span>
                                            <div class="clearfix"></div>
                                        </div>
                                        
                                        <div class="emergency-container hide">
                                            <div class="row">
												
												 <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>Name</label>
                                                        <input type="text" name="applicant[basic][applicant_incase_name]" class="form-control" value="<?php echo $applicant_raw->applicant_incase_name; ?>">
                                                    </div>
                                                </div>
												
												<div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>Relationship</label>
                                                        <input type="text" name="applicant[basic][applicant_incase_relation]" class="form-control" value="<?php echo $applicant_raw->applicant_incase_relation; ?>">
                                                    </div>
                                                </div>
												
												
												<div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>Contact No.</label>
                                                        <input type="text" name="applicant[basic][applicant_incase_contact]" class="form-control" value="<?php echo $applicant_raw->applicant_incase_contact; ?>">
                                                    </div>
                                                </div>
                                         
												<div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>Address</label>
                                                        <input type="text" name="applicant[basic][applicant_incase_address]" class="form-control" value="<?php echo $applicant_raw->applicant_incase_address; ?>">
                                                    </div>
                                                </div>
										    </div>
                                            <div class="row">
                                                <?php show_customField($customFields,"Incase of Emergency",$applicant['applicant_id']); ?>
                                            </div>
                                        </div>
										
										
                                        <hr class="wide">
                                        
                                        <div class="form-title">
                                            <div class="checkbox pull-right">
                                                <label>
                                                    <input type="checkbox" class="colored-blue chk-visible" data-scope="passport" data-applicant="<?php echo $applicant['applicant_id']; ?>" <?php echo $applicant['passport_visible'] ? 'checked' : ''; ?>>
                                                    <span class="text"><small>Visible to employer</small></span>
                                                </label>
                                            </div>
                                            Passport Information <span>( <a href="#" class="btn-change-designation" data-target=".passport-container"> Edit <i class="fa fa-pencil"></i> </a> )</span>
                                            <div class="clearfix"></div>
                                        </div>
                                        
                                        <div class="passport-container hide">
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Passport number</label>
                                                        <input type="text" name="applicant[passport][number]" class="form-control" placeholder="Passport #" value="<?php echo isset( $post['applicant']['passport']['number'] ) ? $post['applicant']['passport']['number'] : ( isset( $applicant['passport_number'] ) ? $applicant['passport_number'] : '' ); ?>">
                                                    </div>
                                                </div>
												  <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>Date issued</label>
                                                        <span class="input-icon icon-right">
                                                            <div class="input-group">
                                                                <input name="applicant[passport][issue]" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo isset( $applicant['passport_issue'] ) ? fdate( 'Y-m-d', $applicant['passport_issue'], '0000-00-00' ) : '0000-00-00'; ?>">
                                                              
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
                                               
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>place Issue </label>
                                                        <input type="text" name="applicant[passport][issue-place]" class="form-control" placeholder="Issue place" value="<?php echo isset( $post['applicant']['passport']['issue-place'] ) ? $post['applicant']['passport']['issue-place'] : ( isset( $applicant['passport_issue_place'] ) ? $applicant['passport_issue_place'] : '' ); ?>">
                                                    </div>
                                                </div>
                                               <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>Expiration</label>
                                                        <span class="input-icon icon-right">
                                                            <div class="input-group">
                                                                <input name="applicant[passport][expiration]" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo isset( $applicant['passport_expiration'] ) ? fdate( 'Y-m-d', $applicant['passport_expiration'], '0000-00-00' ) : '0000-00-00'; ?>">
                                                               
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <?php show_customField($customFields,"Passport Information",$applicant['applicant_id']); ?>
                                            </div>
                                        </div>
                                        
                                        <hr class="wide">
                                        
                                        <div class="form-title">
                                           Educational Background <span>( <a href="#" class="btn-change-education" data-target=".education-container">Edit <i class="fa fa-pencil"></i></a> )</span>
                                           <div class="clearfix"></div>
                                        </div>
                                        
                                        <div class="education-container hide">
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>MBA</label>
                                                        <input type="text" name="applicant[education][mba]" class="form-control" placeholder="MBA" value="<?php echo isset( $post['applicant']['education']['mba'] ) ? $post['applicant']['education']['mba'] : $applicant['education_mba']; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Course</label>
                                                        <input type="text" name="applicant[education][mba-course]" class="form-control" placeholder="Course" value="<?php echo isset( $post['applicant']['education']['mba-course'] ) ? $post['applicant']['education']['mba-course'] : $applicant['education_mba_course']; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>Year</label>
                                                        <input type="text" name="applicant[education][mba-year]" class="form-control" placeholder="yyyy" value="<?php echo isset( $post['applicant']['education']['mba-year'] ) ? $post['applicant']['education']['mba-year'] : $applicant['education_mba_year']; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>College</label>
                                                        <input type="text" name="applicant[education][college]" class="form-control" placeholder="College" value="<?php echo isset( $post['applicant']['education']['college'] ) ? $post['applicant']['education']['college'] : $applicant['education_college']; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Course</label>
                                                        <input type="text" name="applicant[education][college-skills]" class="form-control" placeholder="Course" value="<?php echo isset( $post['applicant']['education']['college-skills'] ) ? $post['applicant']['education']['college-skills'] : $applicant['education_college_skills']; ?>">
                                                    </div>
                                                </div>
    											
    											
    											
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Year</label>
                                                        <input type="text" name="applicant[education][college-year]" class="form-control" placeholder="yyyy" value="<?php echo isset( $post['applicant']['education']['college-year'] ) ? $post['applicant']['education']['college-year'] : $applicant['education_college_year']; ?>">
                                                    </div>
                                                </div>
												
												 <div class="col-sm-5">
                                                    <div class="form-group">
                                                        <label>Address/others</label>
                                                        <input type="text" name="applicant[education][others]" class="form-control" placeholder="Address" value="<?php echo isset( $post['applicant']['education']['others'] ) ? $post['applicant']['education']['others'] : $applicant['education_others']; ?>">
                                                    </div>
                                                </div>
												
												
                                            </div>
                                            
                                            <div class="row">
                                               
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label></label>
                                                        <input type="hidden" name="applicant[education][others-year]" class="form-control" placeholder="yyyy" value="<?php echo isset( $post['applicant']['education']['others-year'] ) ? $post['applicant']['education']['others-year'] : $applicant['education_others_year']; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-sm-8">
                                                    <div class="form-group">
                                                        <label>Highschool</label>
                                                        <input type="text" name="applicant[education][highschool]" class="form-control" placeholder="Highschool" value="<?php echo isset( $post['applicant']['education']['highschool'] ) ? $post['applicant']['education']['highschool'] : $applicant['education_highschool']; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>Year</label>
                                                        <input type="text" name="applicant[education][highschool-year]" class="form-control" placeholder="yyyy" value="<?php echo isset( $post['applicant']['education']['highschool-year'] ) ? $post['applicant']['education']['highschool-year'] : $applicant['education_highschool_year']; ?>">
                                                    </div>
                                                </div>
                                            </div>
										<!-- /Page Body	
										   <div class="row">
                                                <div class="col-sm-8">
                                                    <div class="form-group">
                                                        <label>Vocational</label>
                                                        <input type="text" name="applicant[education][others]" class="form-control" placeholder="Highschool" value="<?php echo isset( $post['applicant']['education']['others'] ) ? $post['applicant']['education']['others'] : $applicant['education_others']; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>Year</label>
                                                        <input type="text" name="applicant[education][others-year]" class="form-control" placeholder="yyyy" value="<?php echo isset( $post['applicant']['education']['others-year'] ) ? $post['applicant']['education']['others-year'] : $applicant['education_others_year']; ?>">
                                                    </div>
                                                </div>
                                            </div> -->	

                                            <div class="row">
                                                <?php show_customField($customFields,"Educational Background",$applicant['applicant_id']); ?>
                                            </div>
                                        </div>

                                        
                                        <hr class="wide">
                                        
                                        <div class="form-title">
                                           Work Experiences <span>( <a href="#" class="btn-change-experience" data-target=".experience-container">Edit <i class="fa fa-pencil"></i></a> )</span>
                                           <div class="clearfix"></div>
                                        </div>
                                        
                                        <div class="experience-container hide">
                                            <div class="work-experience-start">
                                                <?php $prev_work_cnt = 0; ?>
                                                <?php if ( count( $applicant['experiences'] ) == 0 ): ?>
                                                <?php $prev_work_cnt++; ?>
                                                <div class="row work-experience">
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label>Company</label>
                                                            <input type="text" name="applicant[work-experience][company][]" class="form-control" placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience1'); ?>>
                                                        <div class="form-group">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience1'] ?></label>
                                                            <input type="text" name="applicant[work-experience][experience_position][]" class="form-control" placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience2'); ?>>
                                                        <div class="form-group">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience2'] ?></label>
                                                            <input type="text" name="applicant[work-experience][salary][]" class="form-control" placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label>Country / Address</label>
                                                           <input type="text" name="applicant[work-experience][country][]" class="form-control" placeholder="">
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
                                                            <input name="applicant[work-experience][to][]" type="text" data-date-format="yyyy-mm-dd" class="form-control input-sm date-picker work-ended cyd-work-ended-1" placeholder="yyyy-mm-dd" value="">                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label>Years</label>
                                                            <input type="text" name="applicant[work-experience][years][]" class="form-control cyd-work-years-1" placeholder="0" readonly>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience3'); ?>>
                                                        <div class="form-group review-bedcapacity">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience3'] ?></label>
                                                            <input type="text" name="applicant[work-experience][bed_capacity][]" class="form-control" placeholder="">
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience4'); ?>>
                                                        <div class="form-group review-levelofhospital">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience4'] ?></label>
                                                            <input type="text" name="applicant[work-experience][hospital_level][]" class="form-control" placeholder="">
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience5'); ?>>
                                                        <div class="form-group">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience5'] ?></label>
                                                            <input type="text" name="applicant[work-experience][experience_salary][]" class="form-control" placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience6'); ?>>
                                                        <div class="form-group">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience6'] ?></label>
                                                           <input type="text" name="applicant[work-experience][reasonOfLeaving][]" class="form-control" placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience7'); ?>>
                                                        <div class="form-group">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience7'] ?></label>
                                                           <input type="text" name="applicant[work-experience][typeOfResidence][]" class="form-control" placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience8'); ?>>
                                                        <div class="form-group">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience8'] ?></label>
                                                           <input type="text" name="applicant[work-experience][nationality][]" class="form-control" placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience9'); ?>>
                                                        <div class="form-group">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience9'] ?></label>
                                                           <input type="text" name="applicant[work-experience][NoFamilyMembers][]" class="form-control" placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience10'); ?>>
                                                        <div class="form-group">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience10'] ?></label>
                                                           <input type="text" name="applicant[work-experience][extraExperience10][]" class="form-control" placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience11'); ?>>
                                                        <div class="form-group">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience11'] ?></label>
                                                           <input type="text" name="applicant[work-experience][extraExperience11][]" class="form-control" placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience12'); ?>>
                                                        <div class="form-group">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience12'] ?></label>
                                                           <input type="text" name="applicant[work-experience][extraExperience12][]" class="form-control" placeholder="">
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-1">
                                                        <a href="#" role="button" class="btn btn-danger btn-xs">Remove</a>
                                                    </div>
                                                </div>
                                                <?php else: ?>
                                                <?php $totalYears = 0; ?>
                                                <?php foreach ( $applicant['experiences'] as $experience ): ?>
                                                <?php $totalYears += (float) $experience['experience_years']; ?>
                                                <?php $prev_work_cnt++; ?>

                                                <div class="row work-experience">
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label>Company</label>
                                                            <input type="text" name="applicant[work-experience-old][company][<?php echo $experience['experience_id']; ?>]" class="form-control" placeholder="" value="<?php echo $experience['experience_company']; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience1'); ?>>
                                                        <div class="form-group">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience1'] ?></label>
                                                            <input type="text" name="applicant[work-experience-old][experience_position][<?php echo $experience['experience_id']; ?>]" class="form-control" placeholder="" value="<?php echo $experience['experience_position']; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience2'); ?>>
                                                        <div class="form-group">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience2'] ?></label>
                                                            <input type="text" name="applicant[work-experience-old][salary][<?php echo $experience['experience_id']; ?>]" class="form-control" placeholder="" value="<?php echo $experience['salary']; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label>Country / Address</label>
                                                            <input type="text" name="applicant[work-experience-old][country][<?php echo $experience['experience_id']; ?>]" class="form-control" placeholder="" value="<?php echo $experience['experience_country']; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <div class="form-group">
                                                            <label>From</label>
                                                            <input type="text" name="applicant[work-experience-old][from][<?php echo $experience['experience_id']; ?>]" class="form-control input-sm date-picker work-started cyd-work-started-<?php echo $prev_work_cnt; ?>" data-date-format="yyyy-mm-dd" placeholder="yyyy-mm-dd" value="<?php echo $experience['experience_from']; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <div class="form-group">
                                                            <label>To</label>
                                                            <input name="applicant[work-experience-old][to][<?php echo $experience['experience_id']; ?>]" type="text" data-date-format="yyyy-mm-dd" class="form-control input-sm date-picker work-ended cyd-work-ended-<?php echo $prev_work_cnt; ?>" placeholder="yyyy-mm-dd" value="<?php echo $experience['experience_to']; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                    <div class="col-sm-4 cyd-work-years">
                                                        <div class="form-group">
                                                            <label>Years</label>
                                                            <input type="text" name="applicant[work-experience-old][years][]" class="form-control cyd-work-years-1" placeholder="0" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience3'); ?>>
                                                        <div class="form-group review-bedcapacity">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience3'] ?></label>
                                                            <input type="text" name="applicant[work-experience-old][bed_capacity][<?php echo $experience['experience_id']; ?>]" class="form-control" placeholder="" value="<?php echo $experience['bed_capacity']; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience4'); ?>>
                                                        <div class="form-group review-levelofhospital">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience4'] ?></label>
                                                            <input type="text" name="applicant[work-experience-old][hospital_level][<?php echo $experience['experience_id']; ?>]" class="form-control" placeholder="" value="<?php echo $experience['hospital_level']; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience5'); ?>>
                                                        <div class="form-group">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience5'] ?></label>
                                                            <input type="text" name="applicant[work-experience-old][experience_salary][<?php echo $experience['experience_id']; ?>]" class="form-control" placeholder="" value="<?php echo $experience['experience_salary']; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience6'); ?>>
                                                        <div class="form-group">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience6'] ?></label>
                                                           <input type="text" name="applicant[work-experience-old][reasonOfLeaving][<?php echo $experience['experience_id']; ?>]" class="form-control" placeholder="" value="<?php echo $experience['reasonOfLeaving']; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience7'); ?>>
                                                        <div class="form-group">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience7'] ?></label>
                                                           <input type="text" name="applicant[work-experience-old][typeOfResidence][<?php echo $experience['experience_id']; ?>]" class="form-control" placeholder="" value="<?php echo $experience['typeOfResidence']; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience8'); ?>>
                                                        <div class="form-group">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience8'] ?></label>
                                                           <input type="text" name="applicant[work-experience-old][nationality][<?php echo $experience['experience_id']; ?>]" class="form-control" placeholder="" value="<?php echo $experience['nationality']; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience9'); ?>>
                                                        <div class="form-group">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience9'] ?></label>
                                                           <input type="text" name="applicant[work-experience-old][NoFamilyMembers][<?php echo $experience['experience_id']; ?>]" class="form-control" placeholder="" value="<?php echo $experience['NoFamilyMembers']; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience10'); ?>>
                                                        <div class="form-group">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience10'] ?></label>
                                                           <input type="text" name="applicant[work-experience-old][extraExperience10][<?php echo $experience['experience_id']; ?>]" class="form-control" placeholder="" value="<?php echo $experience['extraExperience10']; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience11'); ?>>
                                                        <div class="form-group">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience11'] ?></label>
                                                           <input type="text" name="applicant[work-experience-old][extraExperience11][<?php echo $experience['experience_id']; ?>]" class="form-control" placeholder="" value="<?php echo $experience['extraExperience11']; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4" <?php echo wrkhde('extraExperience12'); ?>>
                                                        <div class="form-group">
                                                            <label><?php echo $_SESSION["settings"]['extraExperience12'] ?></label>
                                                           <input type="text" name="applicant[work-experience-old][extraExperience12][<?php echo $experience['experience_id']; ?>]" class="form-control" placeholder="" value="<?php echo $experience['extraExperience12']; ?>">
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-1">
                                                        <div class="form-group">
                                                            <label></label>
                                                            <input type="hidden" name="applicant[work-experience-old][years][<?php echo $experience['experience_id']; ?>]" class="form-control work-years" placeholder="" value="<?php echo $experience['experience_years']; ?>" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-1">
                                                        <a href="#" role="button" class="btn btn-danger btn-xs">Remove</a>
                                                    </div>
                                                </div>
                                                <div class="work-experience-separator"></div>
                                                <?php endforeach; ?>
                                                <?php endif; ?>
                                                <input type="hidden" class="prev_work_cnt" value="<?php echo $prev_work_cnt; ?>">
                                            </div><!-- .work-experience-start -->
                                            
                                            <div>
                                                <div style="border:1px solid #ccc; margin-bottom:15px;"></div>
                                                <a href="#" id="btn-add-work-experience" role="button" class="btn btn-xs btn-default "><i class="fa fa-plus"></i> Add work experience</a>
                                                &nbsp;<span class="pull-right">Total years experience: <span class="text-danger cyd-work-experience-years"><?php //echo isset( $totalYears ) ? (float) $totalYears : 0; ?></span></span>
                                                <div class="clearfix"></div>
                                            </div>
                                        </div>

                                        <div <?php echo wrkhde('customCategory1'); ?>>
                                            <hr class="wide">
                                            <div class="form-title">
                                            <?php echo $_SESSION["settings"]['customCategory1'] ?> <span>( <a href="#" class="btn-change-experience" data-target=".customCategory1-container">Edit <i class="fa fa-pencil"></i></a> )</span>
                                            <div class="clearfix"></div>
                                            </div>
                                            <div class="row customCategory1-container hide">
                                                <?php show_customField($customFields,"customCategory1",$applicant['applicant_id']); ?>
                                            </div>
                                        </div>
                                        <div <?php echo wrkhde('customCategory2'); ?>>
                                            <hr class="wide">
                                            <div class="form-title">
                                            <?php echo $_SESSION["settings"]['customCategory2'] ?> <span>( <a href="#" class="btn-change-experience" data-target=".customCategory2-container">Edit <i class="fa fa-pencil"></i></a> )</span>
                                            <div class="clearfix"></div>
                                            </div>
                                            <div class="row customCategory2-container hide">
                                                <?php show_customField($customFields,"customCategory2",$applicant['applicant_id']); ?>
                                            </div>
                                        </div>
                                        <div <?php echo wrkhde('customCategory3'); ?>>
                                            <hr class="wide">
                                            <div class="form-title">
                                            <?php echo $_SESSION["settings"]['customCategory3'] ?> <span>( <a href="#" class="btn-change-experience" data-target=".customCategory3-container">Edit <i class="fa fa-pencil"></i></a> )</span>
                                            <div class="clearfix"></div>
                                            </div>
                                            <div class="row customCategory3-container hide">
                                                <?php show_customField($customFields,"customCategory3",$applicant['applicant_id']); ?>
                                            </div>
                                        </div>

                                        <hr class="wide">
                                        
                                        <div class="form-title">
                                            Other Skills <span>( <a href="#" class="btn-change-designation" data-target=".other-skills-container"> Edit <i class="fa fa-pencil"></i> </a> )</span>
                                            <div class="clearfix"></div>
                                        </div>
                                        
                                        <div class="other-skills-container hide">
                                            <div class="row">
                                                <div class="col-sm-10">
                                                    <div class="form-group">
                                                        <label>Skills <small><em>(Press Enter to accept Skills)</em></small></label>
                                                        <input  type="text"name="applicant[other-skills]" data-role="tagsinput" placeholder="Type skills" value="<?php echo isset( $post['applicant']['other-skills'] ) ? $post['applicant']['other-skills'] : $applicant['applicant_other_skills']; ?>" />
                                                    </div>
                                                </div>
                                            </div> 
                                        </div>

                                        <div class="row">
                                            <?php show_customField($customFields,"Other",$applicant['applicant_id']); ?>
                                        </div>
                                        
                                        <hr class="wide">
                                        <div class="row">
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input name="applicant[type]" type="radio" value="Household" class="" <?php echo ( isset( $post['applicant']['type'] ) && $post['applicant']['type'] == 'Household' ) || $applicant['applicant_position_type'] == 'Household' ? 'checked' : ''; ?>>
                                                            <span class="text">Household</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input name="applicant[type]" type="radio" value="Skilled" class="" <?php echo ( isset( $post['applicant']['type'] ) && $post['applicant']['type'] == 'Skilled' ) || $applicant['applicant_position_type'] == 'Skilled' ? 'checked' : ''; ?>>
                                                            <span class="text">Skilled</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
											
											  <div class="col-sm-2">
                                                <div class="form-group">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input name="applicant[type]" type="radio" value="Pro" class="" <?php echo ( isset( $post['applicant']['type'] ) && $post['applicant']['type'] == 'Pro' ) || $applicant['applicant_position_type'] == 'Pro' ? 'checked' : ''; ?>>
                                                            <span class="text">Professional</span>
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
                                                            <input name="applicant[date-applied]" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo fdate( 'Y-m-d', $applicant['applicant_date_applied'] ); ?>">
                                                            
                                                        </div>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label>Source</label>
                                                    <select name="applicant[source]" class="form-control">
                                                        <option value="0" disabled>-- Source --</option>
                                                        <option value="0" <?php echo ( isset( $post['applicant']['source'] ) && $post['applicant']['source'] == 0 ) || ! $applicant['applicant_source'] ? 'selected' : ''; ?>>DIRECT HIRE</option>
                                                        <?php foreach ( $agents as $agent ): ?>
                                                        <option value="<?php echo $agent['agent_id']; ?>" <?php echo ( isset( $post['applicant']['source'] ) && $post['applicant']['source'] == $agent['agent_id'] ) || $applicant['applicant_source'] == $agent['agent_id'] ? 'selected' : '';  ?>><?php echo $agent['agent_first'].' '.$agent['agent_last']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
											
											
											 <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>Other Source</label>
                                                        <input type="text" name="applicant[basic][other_source]" class="form-control" value="<?php echo $applicant_raw->other_source; ?>">
                                                    </div>
                                                </div>
											
                                        </div>
                                        
                                        <hr class="wide">
                                        
                                        <div class="row">
                                            <div class="col-sm-10">
                                                <div class="form-group">
                                                    <label>Remarks <small><em>(optional)</em></small></label>
                                                    <span class="input-icon icon-right">
                                                        <textarea name="applicant[remarks]" class="form-control auto-text-area" spellcheck="false" rows="4" placeholder="Write some remarks..."><?php echo isset( $post['applicant']['applicant_remarks'] ) ? $post['applicant']['applicant_remarks'] : $applicant['applicant_remarks']; ?></textarea>
                                                        <i class="fa fa-briefcase darkorange"></i>
                                                    </span>
                                                    <!--<p class="help-block">Write some remarks</p>-->
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <hr class="wide">
                                        
                                        <button type="submit" class="btn btn-primary" data-text="Save Profile">Save Profile</button>
										<div style="clear:both;height:10px"></div>
										


                                        <span class="pull-right">
                                            <a href="<?php echo site_url( 'public/applicants/pdf/'.$applicant['applicant_slug'] ); ?>" class="btn btn-sm btn-default" target="_blank">Download PDF</a>
											
											
											
											
                                            <?php if ( in_array( $_SESSION['admin']['user']['user_type'], [3, 4] ) ): ?>
                                            <a href="<?php echo site_url( 'admin/applicants/all'); ?>?archive=<?php echo $applicant['applicant_id']; ?>" onclick="return confirm('Are you sure?')">Delete applicant record</a>
                                            &nbsp;
                                            |&nbsp;
                                            <a href="<?php echo site_url( 'admin/applicants/all2'); ?>?unselect=<?php echo $applicant['applicant_id']; ?>" onclick="return confirm('Are you sure?')">Backout</a>
                                           
                                            <?php endif; ?>
                                        </span>
                                    </form>
                                </div>
                                <!-- endOf: Profile -->
                                
                                <!-- Certifications -->
                                <div id="certifications" class="tab-pane <?php echo isset( $_GET['ref_form'] ) && $_GET['ref_form'] == 'certifications' ? 'active' : ''; ?>">
                                    <form class="form" role="form" method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="flag" value="certificate">                                  
                                        <input type="hidden" name="applicant_id" value="<?php echo $applicant['applicant_id']; ?>">
										
										
										
										
										
										 <div class="row">
											<h3 style="margin-left:20px;color:blue;font-size:20px">Examination Taken</h3>
                                           
											 <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>Licensure Examination Type</label>
                                                    <input type="text" name="applicant[certificate][prc_type]" class="form-control" placeholder="ex. NURSING,Pharma" value="<?php echo $applicant_certificate_direct->certificate_prc_type; ?>">
                                                </div>	
                                            </div>
											
											  <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>PRC Rating</label>
                                                    <input type="text" name="applicant[certificate][prc_rating]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_prc_rating; ?>">
                                                </div>
                                            </div>
											
											
											 <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>Date Taken</label>
                                                    <input type="MONTH" name="applicant[certificate][prc_take]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_prc_take; ?>">
                                                </div>
                                            </div>
											
											<div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>Saudi Counsil ID</label>
                                                    <input type="text" name="applicant[certificate][saudi_ids]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_saudi_id; ?>">
                                                </div>
                                            </div>
											
												
											  <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>DHA</label>
                                                    <input type="text" name="applicant[certificate][dha]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_dha; ?>">
                                                </div>
                                            </div>
											
											
											
											
											<div style="clear:both;height:5px"></div>
											
											 
											 
											<div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>KSA Prometrics</label>
                                                    <input type="text" name="applicant[certificate][ksa]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_ksa; ?>">
                                                </div>
                                            </div>
											
											
											 <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>HAAD</label>
                                                    <input type="text" name="applicant[certificate][haad]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_haad; ?>">
                                                </div>
                                            </div>
											
											<div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>QATAR Prometrics</label>
                                                    <input type="text" name="applicant[certificate][qatar]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_qatar; ?>">
                                                </div>
                                            </div>
											
											<div style="clear:both;height:5px"></div>
											<div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>NCLEX</label>
                                                    <input type="text" name="applicant[certificate][nclex]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_nclex; ?>">
                                                </div>
                                            </div>
											
											
											
											<div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>NCLEX Date Taken</label>
                                                    <input type="MONTH" name="applicant[certificate][nclex_exam]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_nclex_exam; ?>">
                                                </div>
												</div>
											<div style="clear:both;height:5px"></div>
											<div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>IELTS Score</label>
                                                    <input type="text" name="applicant[certificate][ielts]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_ielts; ?>">
                                                </div>
                                            </div>
											
											
											<div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>IELTS Overall</label>
                                                    <input type="text" name="applicant[certificate][ielts_overall]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_ielts_overall; ?>">
                                                </div>
                                            </div>
											
											
											<div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>IELTS Date Taken</label>
                                                    <input type="MONTH" name="applicant[certificate][ielts_exam]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_ielts_exam; ?>">
                                                </div>
												</div>
											
											
												<div style="clear:both;height:5px"></div>
										
												<div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>CGFNS</label>
                                                    <input type="text" name="applicant[certificate][cgfns]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_cgfns; ?>">
                                                </div>
												</div>
												
												<div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>CGFNS ID #</label>
                                                    <input type="text" name="applicant[certificate][cgfns_id]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_cgfns_id; ?>">
                                                </div>
												</div>
											
												<div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>CGFNS Date Taken</label>
                                                    <input type="MONTH" name="applicant[certificate][cgfns_exam]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_cgfns_exam; ?>">
                                                </div>
												</div>
											
											
												
												<div class="col-sm-3">
                                                <div class="form-group">
                                                    <label>Visa Screen Holder Exam Date</label>
                                                    <input type="MONTH" name="applicant[certificate][vsh_exam]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_vsh; ?>">
                                                </div>
												</div>
											
											
											
											
												<div style="clear:both;height:5px"></div>
											
                                         </div> 
                                         
                                        <div class="row">
                                            <?php show_customField($customFields,"Examination Taken",$applicant['applicant_id']); ?>
                                        </div>

                                         <div class="dashed"></div>
										
										
										<h3 style="margin-left:20px;color:blue;font-size:20px">Medical Examination</h3>
										
											<div style="clear:both;height:15px"></div>
											
										   <div class="row">
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Medical clinic</label>
                                                    <input type="text" name="applicant[certificate][medical-clinic]" class="form-control" placeholder="Medical clinic" value="<?php echo $applicant['certificate_medical_clinic']; ?>">
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Exam date</label>
                                                    <span class="input-icon icon-right">
                                                        <div class="input-group">
                                                            <input name="applicant[certificate][medical-exam-date]" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker cyd-exam-date" placeholder="yyyy-mm-dd" value="<?php echo fdate( 'Y-m-d', $applicant['certificate_medical_exam_date'], '0000-00-00' ); ?>">
                                                           
                                                        </div>
                                                    </span>
                                                </div>
                                            </div>
                                         </div> 
                                         
                                         <div class="row">
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Medical result</label> 
                                                    <select name="applicant[certificate][medical-result]" style="width:100%">
                                                        <option value="">-- Select --</option>
                                                        <option value="FIT TO WORK" <?php echo $applicant['certificate_medical_result'] == 'FIT TO WORK' ? 'selected' : ''; ?>>FIT TO WORK</option>
                                                        <option value="PENDING" <?php echo $applicant['certificate_medical_result'] == 'PENDING' ? 'selected' : ''; ?>>PENDING</option>
                                                        <option value="UNFIT" <?php echo $applicant['certificate_medical_result'] == 'UNFIT' ? 'selected' : ''; ?>>UNFIT</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Medical remarks</label>
                                                    <textarea name="applicant[certificate][medical-remarks]" rows="1" class="form-control" placeholder=""><?php echo $applicant['certificate_medical_remarks']; ?></textarea>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Medical expiration</label>
                                                    <span class="input-icon icon-right">
                                                        <div class="input-group">
                                                            <input name="applicant[certificate][medical-expiration]" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker cyd-medical-expiration" placeholder="yyyy-mm-dd" value="<?php echo fdate( 'Y-m-d', $applicant['certificate_medical_expiration'], '0000-00-00' ); ?>">
                                                          
                                                        </div>
                                                    </span>
                                                </div>
                                            </div>
											
											     <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Pre-Departure PT result</label> 
                                                    <select name="applicant[certificate][pt-result]" style="width:100%">
                                                        <option value="">-- Select --</option>
                                                        <option value="NEGATIVE" <?php echo $applicant['certificate_pt_result'] == 'NEGATIVE' ? 'selected' : '';  ?>>NEGATIVE</option>
                                                        <option value="POSITIVE" <?php echo $applicant['certificate_pt_result'] == 'POSITIVE' ? 'selected' : '';  ?>>POSITIVE</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>PT date result</label>
                                                    <span class="input-icon icon-right">
                                                        <div class="input-group">
                                                            <input name="applicant[certificate][pt-result-date]" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo fdate( 'Y-m-d', $applicant['certificate_pt_result_date'], '0000-00-00' ); ?>">
                                                          
                                                        </div>
                                                    </span>
                                                </div>
                                            </div>
											
										<div class="col-sm-2">
										<div class="form-group">
											<label>VACCINE</label>
											<input type="text" name="applicant[certificate][mmr]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_mmr; ?>">
										</div>
										</div>
											
											
											
                                         </div>
										
                                        <div class="row">
                                            <?php show_customField($customFields,"Medical Examination",$applicant['applicant_id']); ?>
                                        </div>
										
										   <div class="dashed"></div>
										
										
										
                                        <div class="row">
										
										
										
										
										
											
										 <!-- Status 	
											
                                            <div class="col-sm-2">
                                                <div class="checkbox">
                                                    <label>
                                                        <input name="applicant[certificate][authenticated]" type="checkbox" class="inverted" <?php //echo $applicant['certificate_authenticated'] ? 'checked' : ''; ?>>
                                                        <span class="text">Red Ribbon</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>Red Ribbon Filed Date</label>
                                                    <span class="input-icon icon-right">
                                                        <div class="input-group">
                                                            <input name="applicant[certificate][red-filed-date]" type="text" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php //echo fdate( 'Y-m-d', $applicant_certificate_direct->red_ribbon_file_date, '0000-00-00' ); ?>">
                                                            <span class="input-group-addon">
                                                                <i class="fa fa-calendar"></i>
                                                            </span>
                                                        </div>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>Red Ribbon Expired Date</label>
                                                    <span class="input-icon icon-right">
                                                        <div class="input-group">
                                                            <input name="applicant[certificate][red-expired-date]" type="text" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php //echo fdate( 'Y-m-d', $applicant_certificate_direct->red_ribbon_expired_date, '0000-00-00' ); ?>">
                                                            <span class="input-group-addon">
                                                                <i class="fa fa-calendar"></i>
                                                            </span>
                                                        </div>
                                                    </span>
                                                </div>
                                            </div>
											-->
											<div style="clear:both;height:5px"></div>
										
											<h3 style="margin-left:20px;color:blue;font-size:20px">Aunthenticated Documents</h3>
									
											
											 <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>TOR/DIP</label>
                                                    <input type="text" name="applicant[certificate][tor]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_tor; ?>">
                                                </div>
                                            </div>
                                           
											
											
										   <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>PRC CERTIFICATE</label>
                                                    <input type="text" name="applicant[certificate][prc_cert]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_prc_cert; ?>">
                                                </div>	
                                            </div>
											
											  <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>PRC ID</label>
                                                    <input type="text" name="applicant[certificate][prc_id]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_prc_id; ?>">
                                                </div>
                                            </div>
											
											
											
											  <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>Birth Certificate</label>
                                                    <input type="text" name="applicant[certificate][bc]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_bc; ?>">
                                                </div>
                                            </div>
											
											
											  <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>Marriage Contract</label>
                                                    <input type="text" name="applicant[certificate][mc]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_mc; ?>">
                                                </div>
                                            </div>
											
											
											 <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>Certificate of NO Marriage</label>
                                                     <input type="date" name="applicant[certificate][no_marriage]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->applicant_certificate_no_marriage; ?>">
                                                </div>
                                            </div>
											
											
											
                                         </div>
										
										 
										 
										 
										 
                                         <div class="dashed"></div>
                                         
                                         <div class="row">
                                            <div class="col-sm-2">
                                                <div class="checkbox">
                                                    <label>
                                                        <input name="applicant[certificate][authenticated-nbi]" type="checkbox" class="inverted" <?php echo $applicant['certificate_authenticated_nbi'] ? 'checked' : ''; ?>>
                                                        <span class="text">Authenticated NBI</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>NBI Expired Date</label>
                                                    <span class="input-icon icon-right">
                                                        <div class="input-group">
                                                            <input name="applicant[certificate][nbi-expired-date]" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo fdate( 'Y-m-d', $applicant_certificate_direct->nbi_expired_date, '0000-00-00' ); ?>">
                                                           
                                                        </div>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label>Insurance</label>
                                                    <input type="text" name="applicant[certificate][insurance]" class="form-control" placeholder="" value="<?php echo $applicant['certificate_insurance']; ?>">
                                                </div>
                                            </div>
										<div class="col-sm-3">
											<div class="form-group">
												<label>Insurance No.</label>
												<input type="text" name="applicant[certificate][insurance-no]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->insurance_no; ?>">
											</div>
										</div>
										
										<div class="col-sm-2">
											<div class="form-group">
												<label>COE</label>
												<input type="text" name="applicant[certificate][coe]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_coe; ?>">
											</div>
										</div>
										
                                 </div> 
								 
								 
                                            <div class="dashed"></div>
                                         
                                         <div class="dashed"></div>
                                         
                                         <div class="row">
                                            <div class="col-sm-2">
                                                <div class="checkbox">
                                                    <label>
                                                        <input name="applicant[certificate][pdos]" type="checkbox" class="inverted" <?php echo $applicant['certificate_pdos'] ? 'checked' : ''; ?>>
                                                        <span class="text">PDOS</span>
                                                    </label>
                                                </div>
                                            </div>
											
										<div class="col-sm-2">
											<div class="form-group">
											<label>PDOS DATE</label>
											<input name="applicant[certificate][pdos_date]" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo fdate( 'Y-m-d', $applicant_certificate_direct->certificate_pdos_date, '0000-00-00'); ?>">
											</div>
										</div>
										
										
												
												
											
										
                                            <div class="col-sm-2">
                                                <div class="checkbox">
                                                    <label>
                                                        <input name="applicant[certificate][tesda]" type="checkbox" class="inverted" <?php echo $applicant['certificate_tesda'] ? 'checked' : ''; ?>>
                                                        <span class="text">TESDA</span>
                                                    </label>
                                                </div>
                                            </div>
											
											<div class="col-sm-2">
											<div class="form-group">
											<label>TESDA DATE</label>
											
											<input name="applicant[certificate][tesda_date]" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo fdate( 'Y-m-d', $applicant_certificate_direct->certificate_tesda_date, '0000-00-00'); ?>">
											</div>
											</div>
											
											
                                            <div class="col-sm-2">
                                                <div class="checkbox">
                                                    <label>
                                                        <input name="applicant[certificate][info-sheet]" type="checkbox" class="inverted" <?php echo $applicant['certificate_info_sheet'] ? 'checked' : ''; ?>>
                                                        <span class="text">Info sheet</span>
                                                    </label>
                                                </div>
                                            </div>
											
											
                                       
                                         </div> 

                                        <div class="row">
                                            <?php show_customField($customFields,"Aunthenticated Documents",$applicant['applicant_id']); ?>
                                        </div>
                                         
                                         <div class="dashed"></div>
                                         
                                         <div class="row">
                                            <div class="col-sm-2">
                                                <div class="checkbox">
                                                    <label>
                                                        <input name="applicant[certificate][philhealth]" type="checkbox" class="inverted" <?php echo $applicant['certificate_philhealth'] ? 'checked' : ''; ?>>
                                                        <span class="text">Philhealth</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="checkbox">
                                                    <label>
                                                        <input name="applicant[certificate][m1b]" type="checkbox" class="inverted" <?php echo $applicant['certificate_m1b'] ? 'checked' : ''; ?>>
                                                        <span class="text">PAG-IBIG</span>
                                                    </label>
                                                </div>
                                            </div>
											
											
									  <div class="col-sm-2">
										<div class="checkbox">
											<label>
												<input name="applicant[certificate][owwa]" type="checkbox" class="inverted" <?php echo $applicant_certificate_direct->certificate_owwa ? 'checked' : ''; ?>>
												<span class="text">OWWA</span>
											</label>
										</div>
									</div>
											
											
											<div class="col-sm-2">
											<div class="form-group">
											<label>OWWA FROM</label>
											<input type="date" name="applicant[certificate][owwafrom]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_owwa_from; ?>">
											</div>
											</div>
											
											
											<div class="col-sm-2">
											<div class="form-group">
											<label>OWWA TO</label>
											<input type="date" name="applicant[certificate][owwato]" class="form-control" placeholder="" value="<?php echo $applicant_certificate_direct->certificate_owwa_to; ?>">
											</div>
											</div>
											
											
											
                                            
                                        </div> 
                                        <div class="row">
                                            <?php show_customField($customFields,"Other Requirements",$applicant['applicant_id']); ?>
                                        </div>

                                        <hr class="wide">
                                        <button type="reset" class="btn btn-default">Reset</button>
                                        <button type="submit" class="btn btn-primary" data-text="Save Certificates">Save Certificates</button>
                                    </form>
                                </div>
                                <!-- endOf: Certifications -->
                                
                                <!-- Requirements -->
                                <div id="requirements" class="tab-pane <?php echo isset( $_GET['ref_form'] ) && $_GET['ref_form'] == 'requirements' ? 'active' : ''; ?>">
                                    <form class="form" role="form" method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="flag" value="requirement">
                                        <input type="hidden" name="applicant_id" value="<?php echo $applicant['applicant_id']; ?>">

                                        <div class="visa-container">
                                            <div class="row">
                                                <div class="col-sm-2">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input name="applicant[requirement][trade-test]" type="checkbox" class="inverted" <?php echo $applicant['requirement_trade_test'] ? 'checked' : ''; ?>>
                                                            <span class="text">Trade test</span>
                                                        </label>
                                                    </div>
                                                </div>
												
                                                
												<div class="col-sm-3"> 
                                                    <div class="form-group">
                                                        <label>Trade Test Remarks</label>
                                                        <input type="text" name="applicant[requirement][tesda-remarks]" class="form-control" value="<?php echo $applicant_requirements_direct->requirement_trade_remarks; ?>">
                                                    </div>
                                                </div>
												
												<div class="col-sm-3"> 
                                                    <div class="form-group">
                                                        <label>Picture status</label>
                                                        <input type="text" name="applicant[requirement][picture-status]" class="form-control" placeholder="" value="<?php echo $applicant['requirement_picture_status']; ?>">
                                                    </div>
                                                </div>
                                                
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>School records</label>
                                                        <input type="text" name="applicant[requirement][school-records]" class="form-control" placeholder="" value="<?php echo $applicant['requirement_school_records']; ?>">
                                                    </div>
                                                </div>
                                                
                                            </div>
                                            
                                            <div class="dashed"></div>
                                            
											
											
											
											
										
											     <div class="row" style="display:none;">
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>OWWA certificate</label>
                                                        <input type="text" name="applicant[requirement][owwa-certificate]" class="form-control" placeholder="" value="<?php echo $applicant['requirement_owwa_certificate']; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>OWWA schedule</label>
                                                        <span class="input-icon icon-right">
                                                            <div class="input-group">
                                                                <input name="applicant[requirement][owwa-schedule]" type="text" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo fdate( 'Y-m-d', $applicant['requirement_owwa_schedule'], '0000-00-00' ); ?>">
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-calendar"></i>
                                                                </span>
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                            
                                            <div class="dashed"></div>
											
											<h3 style="margin-left:20px;color:blue;font-size:20px">Job Offer</h3>
											 <div class="row">
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>Job offer</label> 
                                                        <select name="applicant[requirement][job-offer]" style="width:100%">
                                                            <option value="0">-- No job offer --</option>
                                                            <?php foreach ( $jobOffers as $job ): ?>
                                                            <option value="<?php echo $job['job_id']; ?>" <?php echo $applicant['requirement_job_offer'] == $job['job_id'] ? 'selected' : ''; ?>><?php echo $job['job_name']; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
												
												
												<div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>J.O Received </label>
                                                        <span class="input-icon icon-right">
                                                            <div class="input-group">
                                                                <input name="applicant[requirement][jo-received]" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo fdate( 'Y-m-d', $applicant_requirements_direct->requirement_job_received, '0000-00-00'); ?>">
                                                                
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
												
												
												
												<div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>J.O Accepted </label>
                                                        <span class="input-icon icon-right">
                                                            <div class="input-group">
                                                                <input name="applicant[requirement][jo-accept]" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo fdate( 'Y-m-d', $applicant_requirements_direct->requirement_job_accepted, '0000-00-00'); ?>">
                                                              
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
													
												
												
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>Offer Letter</label>
                                                        <input type="text" name="applicant[requirement][offer-letter]" class="form-control" placeholder="" value="<?php echo $applicant_requirements_direct->offer_letter; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>Offer Salary</label>
                                                        <input type="text" name="applicant[requirement][offer-salary]" class="form-control"  value="<?php echo $applicant['requirement_offer_salary']; ?>" >
                                                    </div>
                                                </div>
                                            </div>
											
											  <div class="dashed"></div>
												<h3 style="margin-left:20px;color:blue;font-size:20px">Visa</h3>
                                            <div class="row">
                                                <div class="col-sm-1">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input name="applicant[requirement][visa]" type="checkbox" class="inverted" <?php echo $applicant['requirement_visa'] ? 'checked' : ''; ?>>
                                                            <span class="text">VISA</span>
                                                        </label>
                                                    </div>
                                                </div>
												
												   <div class="col-sm-2">
														<div class="form-group">
                                                        <label>Visa #</label>
                                                        <input type="text" name="applicant[requirement][visa-no]" class="form-control" placeholder="" value="<?php echo $applicant_requirements_direct->requirement_visa_no; ?>">
														</div>
													</div>
													
													 <div class="col-sm-2">
														<div class="form-group">
                                                        <label>Category</label>
                                                        <input type="text" name="applicant[requirement][visa-category]" class="form-control" placeholder="" value="<?php echo $applicant_requirements_direct->requirement_visa_category;?>">
														</div>
													</div>
													
										<div class="col-sm-3">
										<div class="form-group">
										<label>VISA REMARKS</label>
										<input type="text" name="applicant[requirement][visaremarks]" class="form-control" placeholder="" value="<?php echo $applicant_requirements_direct->applicant_requirement_visaremarks	; ?>">
										</div>
										</div>
										
										
											<div class="col-sm-2">
										<div class="form-group">
										<label>ECODE</label>
										<input type="text" name="applicant[requirement][ecode]" class="form-control" placeholder="" value="<?php echo $applicant_requirements_direct->applicant_requirement_ecode	; ?>">
										</div>
										</div>
										
										
											<div class="col-sm-2">
										<div class="form-group">
										<label>PAID</label>
										<input type="text" name="applicant[requirement][paid]" class="form-control" placeholder="" value="<?php echo $applicant_requirements_direct->applicant_requirement_paid	; ?>">
										</div>
										</div>
												

													
														<div style="clear:both;height:5px"></div>
													
												<div class="col-sm-2" style="margin-left:100px">
                                                    <div class="form-group">
                                                        <label>Visa Stamp Submission</label>
                                                        <span class="input-icon icon-right">
                                                            <div class="input-group">
                                                                <input name="applicant[requirement][visa-stamp]" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php if(isset($applicant_requirements_direct->requirement_visa_stamp)){echo fdate( 'Y-m-d', $applicant_requirements_direct->requirement_visa_stamp, '0000-00-00' );} ?>">
                                                              
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
													
												
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>VISA Date</label>
                                                        <span class="input-icon icon-right">
                                                            <div class="input-group">
                                                                <input name="applicant[requirement][visa-date]" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker cyd-visa-date" placeholder="yyyy-mm-dd" value="<?php echo fdate( 'Y-m-d', $applicant['requirement_visa_date'], '0000-00-00' ); ?>">
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>Date release</label>
                                                        <span class="input-icon icon-right">
                                                            <div class="input-group">
                                                                <input name="applicant[requirement][visa-release-date]" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo fdate( 'Y-m-d', $applicant['requirement_visa_release_date'], '0000-00-00' ); ?>">
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>Date expired</label>
                                                        <span class="input-icon icon-right">
                                                            <div class="input-group">
                                                                <input name="applicant[requirement][visa-expiration]" type="date" data-date-format="yyyy-mm-dd"  placeholder="yyyy-mm-dd" value="<?php echo fdate( 'Y-m-d', $applicant['requirement_visa_expiration'], '0000-00-00' ); ?>">
                                                           
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                            
                                            <div class="dashed"></div>

											<h3 style="margin-left:20px;color:blue;font-size:20px">OEC Information</h3>
                                            <div class="row">
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>OEC #</label>
                                                        <input type="text" name="applicant[requirement][oec-number]" class="form-control" placeholder="" value="<?php echo $applicant['requirement_oec_number']; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>OEC submission</label>
                                                        <span class="input-icon icon-right">
                                                            <div class="input-group">
                                                                <input name="applicant[requirement][oec-submission-date]" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo fdate( 'Y-m-d', $applicant['requirement_oec_submission_date'], '0000-00-00' ); ?>">
                                                               
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>OEC release</label>
                                                        <span class="input-icon icon-right">
                                                            <div class="input-group">
                                                                <input name="applicant[requirement][oec-release-date]" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo fdate( 'Y-m-d', $applicant['requirement_oec_release_date'], '0000-00-00' ); ?>">
                                                               
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
												
												
												

                                            </div>
                                            
							   
					<div class="row">			
						<div class="col-sm-3">
						<div class="form-group">
						<label>OEC EXPIRED DATE</label>
						<input type="date" name="applicant[requirement][oecexpired]" class="form-control" placeholder="" value="<?php echo $applicant_requirements_direct->applicant_requirement_oec_expired	; ?>">
						</div>

						</div>
						<div class="col-sm-3">
						<div class="form-group">
						<label>RFP</label>
						<input type="text" name="applicant[requirement][rfp]" class="form-control" placeholder="" value="<?php echo $applicant_requirements_direct->applicant_requirement_rfp	; ?>">
						</div>
						</div>
					</div>		


										   <div class="dashed"></div>
                                           
                                            
                                            <div class="row">
											
											
											
											
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Contract Received</label>
                                                        <span class="input-icon icon-right">
                                                            <div class="input-group">
                                                                <input name="applicant[requirement][contract]" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo fdate( 'Y-m-d', $applicant['requirement_contract'], '0000-00-00' ); ?>">
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-calendar"></i>
                                                                </span>
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
                                             <!-- endOf:   
											   <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>MOFA</label>
                                                        <input type="text" name="applicant[requirement][mofa]" class="form-control" placeholder="" value="<?php echo $applicant['requirement_mofa']; ?>">
                                                    </div>
                                                </div>
											-->	
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label>Coordinator/Remarks/Update</label>
                                                        <input type="text" name="applicant[requirement][remarks]" class="form-control" placeholder="" value="<?php echo $applicant['requirement_remarks']; ?>">
                                                    </div>
                                                </div>
                                                
                                            </div>
                                            
                                            <div class="dashed"></div>
                                            
                                           
											
												<h3 style="margin-left:20px;color:blue;font-size:20px">Flight Details</h3>
                                            <div class="row">
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>Ticket</label>
                                                        <input type="text" name="applicant[requirement][ticket]" class="form-control" placeholder="" value="<?php echo $applicant['requirement_ticket']; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>Ticket No.</label>
                                                        <input type="text" name="applicant[requirement][ticket-no]" class="form-control" placeholder="" value="<?php echo $applicant_requirements_direct->ticket_no; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-sm-2">
                                                    <div class="form-group">
                                                        <label>ETD / Flight Date</label>
                                                        <span class="input-icon icon-right">
                                                            <div class="input-group">
                                                                <input name="applicant[requirement][flight-date]" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="yyyy-mm-dd" value="<?php echo fdate( 'Y-m-d', $applicant_requirements_direct->flight_date, '0000-00-00' ); ?>">
                                                            
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                        <label>Flight Remarks.<!--ticket remarks--></label>
                                                        <input type="text" name="applicant[requirement][flight-remarks]" class="form-control" placeholder="" value="<?php echo $applicant_requirements_direct->ticket_remarks; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                        
                                        <hr class="wide">
                                        <button type="reset" class="btn btn-default">Reset</button>
                                        <button type="submit" class="btn btn-primary" data-text="Save Requirements">Save Requirements</button>
                                    </form>
                                </div>
                                <!-- endOf: Requirements -->
                                
                                <!-- Softcopy Documents -->
                                <div id="documents" class="tab-pane <?php echo isset( $_GET['ref_form'] ) && $_GET['ref_form'] == 'documents' ? 'active' : ''; ?>">

                                        <div class="form-title">
                                           Upload file
                                        </div>
                                        
                                        <form class="form" role="form" method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="flag" value="file">
                                        <input type="hidden" name="applicant_id" value="<?php echo $applicant['applicant_id']; ?>">
                                        <div class="row">
                                            <div class="col-sm-2"> 
                                                <br>
                                                <input name="applicant[file][file]" class="hide" type="file" />
                                                <button type="button" class="btn btn-md btn-default btn-file-browse">Browse file ...</button>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>File name</label>
                                                    <input name="applicant[file][name]" type="text" class="form-control cyd_filename">
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label>Document type</label>
                                                    <select name="applicant[file][type]" class="form-control cyd_type">
                                                        <option value="0" disabled>-- Select --</option>
                                                        <?php foreach ( $documentTypes as $fileType => $typeName ): ?>
                                                        <option class="hideme_<?php echo $fileType; ?>" value="<?php echo $fileType; ?>"><?php echo $typeName; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-1">
                                                <br>
                                                <button type="submit" class="btn btn-md btn-info cyd_upload" data-text="Upload">Upload</button>
                                            </div>
                                        </div>
                                        </form>
                                        
                                        <hr class="wide">
                                        
                                        <a name="history"></a>
                                        <div class="form-title">
                                           Uploaded files
                                           
                                           <div class="clearfix"></div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="table-responsive" style="overflow:scroll;">
                                                    <table class="table table-bordered table-condensed table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Date uploaded</th>
                                                                <th>Title</th>
                                                                <th>Type</th>
                                                                <th>Size</th>
                                                                <th>File</th>
                                                                <th>Preview</th>
                                                                <th>Uploaded by</th>
                                                                <th>&nbsp;</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="cyd_illu">
                                                            <?php if ( count( $applicant['files'] ) == 0 ): ?>
                                                            <tr>
                                                                <td align="center" colspan="7">-- No files --</td>
                                                            </tr>
                                                            <?php endif; ?>
                                                            <?php foreach ( $applicant['files'] as $file ): ?>
                                                            <tr class="file_row_<?php echo $file['file_id']; ?>">
                                                               <td><?php echo fdate( 'Y-m-d h:ia', $file['file_created'] ); ?></td>
                                                               <td>
                                                                <?php echo $file['file_name']; ?>
                                                               </td>
                                                               <td><?php echo $file['file_type']; ?></td>
                                                               <td><?php echo number_format( $file['file_size'], 0, '', ' ' ); ?> kb</td>
                                                               <td style="word">
                                                                <a style="word-wrap:break-word" href="<?php echo base_url().$file['file_path']; ?>" title="Open file" target="_blank">
                                                                    <code><?php echo pathinfo( $file['file_path'], PATHINFO_BASENAME ); ?></code>
                                                                </a>
                                                               </td>
                                                               <td align="center">

                                                                <?php if ( in_array( pathinfo( $file['file_name'], PATHINFO_EXTENSION ), [ 'jpg', 'jpeg', 'gif', 'png', 'bmp' ] ) ): ?>
                                                                <!-- <img src="<?php echo base_url().$file['file_path']; ?>" alt="<?php echo $file['file_type']; ?>" class="img-rounded" height="80"> -->
                                                                <a href="<?php echo base_url().$file['file_path']; ?>" data-lightbox="preview-slide" data-title="<?php echo $file['file_name']; ?>">
                                                                    <img src="<?php echo base_url().$file['file_path']; ?>" alt="<?php echo $file['file_type']; ?>" class="img-rounded" height="80">
                                                                </a>
                                                                <?php endif; ?>
                                                               </td>
                                                               <td><small><?php echo $file['user_fullname']; ?></small></td>
                                                               <td><a onClick="return confirm('Do you want to remove this file?')" file-id="<?php echo $file['file_id']; ?>" class="btn btn-xs btn-danger cyd_file_delete">Remove</a></td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div> 
                                </div>
                                <!-- endOf: Softcopy Documents -->
                                
                                <!-- Status -->
                                <div id="status" class="tab-pane <?php echo isset( $_GET['ref_form'] ) && $_GET['ref_form'] == 'status' ? 'active' : ''; ?>">
                                    <form class="form" role="form" method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="flag" value="status">
                                        <input type="hidden" name="applicant_id" value="<?php echo $applicant['applicant_id']; ?>">
                                        <div class="form-title">
                                           UPDATE STATUS
                                           
                                           <div class="clearfix"></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Applicant No.</label>
                                                    <input type="text" name="applicant[status][applicant-no]" class="form-control" placeholder="Applicant No. (Optional)" value="<?php echo $applicant_raw->applicantNumber; ?>">
                                                </div>
                                            </div>
											
											 <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Employer</label>
                                                    <input type="text" name="applicant[status][sub_employer]" class="form-control" value="<?php echo $applicant_raw->sub_employer; ?>">
                                                </div>
											</div>
											
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Agency/Employer</label> 
                                                    <select name="applicant[status][employer]" style="width:100%">
                                                        <option value="0">-- No employer --</option>
                                                        <?php foreach ( $employers as $employer ): ?>
                                                        <option value="<?php echo $employer['employer_id']; ?>" <?php echo $applicant['status']['employer'] == $employer['employer_id'] ? 'selected' : ''; ?>><?php echo $employer['employer_name']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
												
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Preferred country</label>
                                                    <input type="text" class="form-control" readonly disabled value="<?php echo $applicant['status']['country']; ?>">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Status</label> 
                                                    <select name="applicant[status][status]" style="width:100%" id="addSubStatus">
                                                        <?php foreach ( $statusText as $statusId => $string ): ?>
                                                            <?php if($string != 'Line Up'){ ?>
                                                                <option value="<?php echo $statusId; ?>" <?php echo $statusId == $applicant['status']['status'] ? 'selected' : ''; ?> <?php echo $statusId == $status['Deployed'] && ! $applicant['applicant_job'] ? 'disabled' : ''; ?>><?php echo $string; ?><?php echo $statusId == $status['Deployed'] && ! $applicant['applicant_job'] ? ' (No selected job)' : ''; ?></option>
                                                            <?php }else{ ?>
                                                                <option value="<?php echo $statusId; ?>" <?php echo $statusId == $applicant['status']['status'] ? 'selected' : ''; ?> <?php echo $statusId == $status['Deployed'] && ! $applicant['applicant_job'] ? 'disabled' : ''; ?> disabled><?php echo $string; ?><?php echo $statusId == $status['Deployed'] && ! $applicant['applicant_job'] ? ' (No selected job)' : ''; ?></option>
                                                            <?php }//endif ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                             <script type="text/javascript">
                                            $("#addSubStatus").change(function () {
                                                newstatus = $("#addSubStatus").val();
                                                if(newstatus == 4){
                                                    $('.selectSubstatus').slideDown('fast');
                                                }else{
                                                    $('.selectSubstatus').slideUp('fast');
                                                }
                                            });
                                            </script>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                 <label>Status date <small style="color:red"><I>( date DEPLOYED,date SELECTED etc. )</I></small></label>
                                                    <span class="input-icon icon-right">
                                                        <div class="input-group">
                                                            <input name="applicant[status][date]" type="date"  class="form-control date-picker" placeholder="yyyy-mm-dd" required>
                                                        </div>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                         <div class="row selectSubstatus"
                                        <?php if($applicant['status']['status'] != 4){ ?>
                                        style="display:none;"
                                        <?php }//dont show if not selected ?>
                                        >
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Sub Status</label> 
                                                    <select name="applicant[status][substatus]" style="width:100%">
                                                        <option><?php if(isset($applicant_raw->sub_status)) echo $applicant_raw->sub_status; ?></option>
                                                        <option>To Undergo Medical</option>
														 <option>Medical Under Process</option>
														  <option>Fit to Work</option>
													<option>For Insurance</option>
													  <option>For Encoding Enjaz</option>
													   <option>Waiting for job Offer</option>
                                                        <option>For job offer Signing</option>
														  <option>Contract Signing</option>
                                                        <option>Waiting For Visa</option>
														<option>Visa in Process</option>
                                                        <option>Visa Approved</option>
														 <option>Visa Stamping</option>
														 <option>OEC FILING</option>
														  <option>OWWA Training</option>
                                                        <option>Unfit</option>
                                                        <option>Canceled/backout</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>  
                                        <div class="row">
                                            <div class="col-sm-8">
                                                <div class="form-group">
                                                    <label>Remarks</label>
                                                    <textarea name="applicant[status][remarks]" rows="2" class="form-control auto-text-area" spellcheck="false" placeholder=""><?php echo $applicant['status']['remarks']; ?></textarea>
                                                </div>
                                            </div> 
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4 col-sm-offset-4" align="right">
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-primary" data-text="Update status">Update status</button>
                                                </div>
                                            </div>
                                        </div>
                                        </form>
                                        
                                        <div class="dashed"></div>

                                        <?php if ( in_array( $_SESSION['admin']['user']['user_type'], [ 3, 4 ] ) ): ?>
                                        <div class="form-title">
                                           <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" class="colored-blue" id="chk-accounting-status" data-applicant="<?php echo $applicant['applicant_id']; ?>" <?php echo $applicant['applicant_paid'] ? 'checked' : ''; ?>>
                                                    <span class="text">ACCOUNTING STATUS</span>
                                                </label>
                                            </div>
                                           <div class="clearfix"></div>
                                        </div> 
                                        <?php endif; ?>
                                        
                                        <a name="history"></a>
                                        <div class="form-title">
                                           STATUS HISTORY
                                           
                                           <div class="clearfix"></div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-condensed table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Created</th>
                                                                <th>Status</th>
                                                                <th>Agency/Employer</th>
                                                                <th>Country</th>
                                                                <th>Remarks</th>
                                                                <th>Handled by</th>
                                                                <th>Status date</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ( $applicant['logs'] as $log ): ?>
                                                            <tr>
                                                               <td><?php echo fdate( 'Y-m-d h:ia', $log['log_created'] ); ?></td>
                                                               <td><span class="label label-<?php echo $statusColors[$log['log_status']]; ?>"><?php echo $statusText[$log['log_status']]; ?></span></td>
                                                               <td><?php echo $log['employer_name']; ?></td>
                                                               <td><?php echo $log['country_name']; ?></td>
                                                               <td><?php echo $log['log_remarks']; ?></td>
												<?php if (  in_array( $log['user_type'], [5]) ): ?>
													<td><?php echo $log['employer_name']; ?></td>
												<?php endif; ?>
												
												<?php if ( ! in_array(  $log['user_type'], [5]) ): ?>
													<td><?php echo $log['user_fullname']; ?></td>
												<?php endif; ?>
											   
											   <td><?php echo date( 'Y-m-d', strtotime( $log['log_date'] ) ); ?></td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div> 
                                </div>
                                <!-- endOf: Status -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <p>&nbsp;</p>
</div>
<!-- /Page Body -->
<script type="text/javascript">
    extraExperience1 = '<?php echo $_SESSION["settings"]["extraExperience1"]; ?>';
    extraExperience2 = '<?php echo $_SESSION["settings"]["extraExperience2"]; ?>';
    extraExperience3 = '<?php echo $_SESSION["settings"]["extraExperience3"]; ?>';
    extraExperience4 = '<?php echo $_SESSION["settings"]["extraExperience4"]; ?>';
    extraExperience5 = '<?php echo $_SESSION["settings"]["extraExperience5"]; ?>';
    extraExperience6 = '<?php echo $_SESSION["settings"]["extraExperience6"]; ?>';
    extraExperience7 = '<?php echo $_SESSION["settings"]["extraExperience7"]; ?>';
    extraExperience8 = '<?php echo $_SESSION["settings"]["extraExperience8"]; ?>';
    extraExperience9 = '<?php echo $_SESSION["settings"]["extraExperience9"]; ?>';
    extraExperience10 = '<?php echo $_SESSION["settings"]["extraExperience10"]; ?>';
    extraExperience11 = '<?php echo $_SESSION["settings"]["extraExperience11"]; ?>';
    extraExperience12 = '<?php echo $_SESSION["settings"]["extraExperience12"]; ?>';
</script>
<?php $app->renderScripts(); ?>