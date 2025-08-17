<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
    <ul class="breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url('admin/'); ?>">Home</a>
        </li>
        <li class="active">Applicants</li>
    </ul>
</div>
<!-- /Page Breadcrumb -->
<!-- Page Header -->
<div class="page-header position-relative">
    <div class="header-title">
        <h1>
            <?php echo $recordScope; ?> Applicants
        </h1>
    </div>
    <!--Header Buttons-->
    <div class="header-buttons">
        <a class="sidebar-toggler" href="#">
            <i class="fa fa-arrows-h"></i>
        </a>
        <a class="refresh" id="refresh-toggler" href="<?php echo site_url( 'admin/applicants/all' ); ?>">
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
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <div class="widget">

                <div class="widget-header with-footer">
                    <span class="widget-caption">
                        <?php if ( isset( $_GET['status'] ) && $_GET['status'] == 'Reserved' ): ?>
                        <a href="<?php echo site_url( 'admin/applicants/expired-reservations' ); ?>" class="btn btn-xs btn-danger">See all expired reservations...</a>
                        <?php else: ?>
                        <?php if( ! in_array( $_SESSION['admin']['user']['user_type'], [7]) ):  ?>
                        <a href="<?php echo site_url( 'admin/applicants/add' ); ?>" class="btn btn-xs btn-default"><i class="fa fa-plus"></i> Add Applicant</a>
                        <?php endif;?>
                        <?php endif; ?>
                        &nbsp;
                        <a href="#" class="btn-show-photo btn btn-xs btn-info">Show photos</a>
                    </span>
                    <?php if ( count( $applicants ) > 0 ): ?>
                    <div class="widget-buttons">
                        <a href="#" class="btn-advanced-search">
                            <i class="fa fa-search"></i> Advanced search
                        </a>  
                    </div> 
                    <?php endif; ?>
                    <div class="widget-buttons">
                        <a href="#" data-toggle="maximize">
                            <i class="fa fa-expand"></i>
                        </a>
                        <a href="#" data-toggle="collapse">
                            <i class="fa fa-minus"></i>
                        </a>
                        <a href="#" data-toggle="dispose">
                            <i class="fa fa-times"></i>
                        </a>
                    </div>
                </div>
                <div class="widget-body">
                    <?php $app->renderAlerts(); ?>
                    <div class="row advanced-search <?php echo isset( $get['search'] ) ? '' : 'hide'; ?>">
                        <div class="col-sm-12">
                            <div class="form-title">
                                <strong>Advanced Search</strong>
                            </div>
                            <form method="get" action="<?php echo site_url();?>admin/applicants/all">
                            <div class="row">
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label>Search keyword</label>
                                        <input type="text" name="search[q]" class="form-control input-sm" placeholder="Keywords" value="<?php echo isset( $get['search']['q'] ) ? $get['search']['q'] : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label>Country:</label> 
                                        <select name="search[country]" class="form-control input-sm">
                                            <option value="0">All</option>
                                            <?php foreach ($countries as $country): ?>
                                            <option value="<?php echo $country['country_id']; ?>" <?php echo isset( $get['search']['country'] ) && $get['search']['country'] == $country['country_id'] ? 'selected' : '';  ?>><?php echo $country['country_name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label>Position</label> 
                                        <select name="search[position]" class="form-control input-sm">
                                            <option value="0">-- Select position --</option>
                                            <?php foreach ($categories as $category): ?>
                                            <?php if ( count( $category['positions'] ) == 0 ) continue; ?>
                                            <optgroup label="<?php echo $category['category_name']; ?>">
                                            <?php foreach ($category['positions'] as $position): ?>
                                            <option value="<?php echo $position['position_id']; ?>" <?php echo isset( $get['search']['position'] ) && $get['search']['position'] == $position['position_id'] ? 'selected' : '';  ?>><?php echo $position['position_name']; ?></option>
                                            <?php endforeach; ?>
                                            </optgroup>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label>Employer</label> 
                                        <select name="search[employer]" class="form-control input-sm">
                                            <option value="0">-- Select Employer --</option>
                                            <?php foreach ($employers as $employer): ?>
                                            <option value="<?php echo $employer['employer_id']; ?>" <?php echo isset( $get['search']['employer'] ) && $get['search']['employer'] == $employer['employer_id'] ? 'selected' : '';  ?>><?php echo $employer['employer_name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label>Status</label> 
                                        <select name="search[status]" class="form-control input-sm">
                                            <option value="111">-- Select Status --</option>
                                            <option value="0">Available</option>
                                            <option value="4">Selected</option>
                                            <option value="5">Line Up</option>
                                            <option value="8">For Deployment</option>
                                            <option value="9">Deployed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <label>Date applied</label>
                                    <div class="input-group ">
                                        <input name="search[date-applied][from]" type="text" data-date-format="yyyy-mm-dd" class="form-control input-sm date-picker" placeholder="yyyy-mm-dd" value="">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <span class="input-group-addon">to</span>
                                        <input name="search[date-applied][to]" type="text" data-date-format="yyyy-mm-dd" class="form-control input-sm date-picker" placeholder="yyyy-mm-dd" value="">
                                    </div>
                                </div>
                                <div class="col-sm-1">
                                    <div class="form-group">
                                        <label>Gender</label>
                                        <select name="search[gender]" class="form-control input-sm">
                                            <option value="">Any</option>
                                            <option value="Male" <?php echo isset( $get['search']['gender'] ) && $get['search']['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo isset( $get['search']['gender'] ) && $get['search']['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <label>Age bracket</label>
                                    <div class="input-group ">
                                        <input name="search[age][from]" type="text" class="form-control input-sm" value="<?php echo isset( $get['search']['age']['from'] ) ? $get['search']['age']['from'] : ''; ?>" placeholder="0">
                                        <span class="input-group-addon">&minus;</span>
                                        <input name="search[age][to]" type="text" placeholder="0" class="form-control input-sm" value="<?php echo isset( $get['search']['age']['to'] ) ? $get['search']['age']['to'] : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <label>Salary range</label>
                                    <div class="input-group ">
                                        <input name="search[salary][from]" type="text" class="form-control input-sm" value="<?php echo isset( $get['search']['salary']['from'] ) ? $get['search']['salary']['from'] : ''; ?>" placeholder="0.00">
                                        <span class="input-group-addon">&minus;</span>
                                        <input name="search[salary][to]" type="text" placeholder="0.00" class="form-control input-sm" value="<?php echo isset( $get['search']['salary']['to'] ) ? $get['search']['salary']['to'] : ''; ?>">
                                    </div>
                                </div>

                                <div class="col-sm-1">
                                    <br>
                                    <button type="submit" class="btn btn-md btn-primary">Search</button>
                                </div>
                            </div> 
                            </form>
                            <hr class="wide">    
                        </div>
                    </div>

                    <div class="table-responsive" style="overflow:scroll; padding-bottom:20px;">
                        <table id="cyd_applicant_list" class="table table-bordered table-hover table-applicants">
                            <?php if ( count( $applicants ) == 0 ): ?>
                            <tr>
                                <td>No entries</td>
                            </tr>
                            <?php else: ?>
                            <thead class="bordered-palegreen">
                                <tr>
									<th>#</th>
									<th></th>    
                                    <th>#</th>
                                    <th>Date Applied</th>
                                    <th>Applicant #</th>
                                    <th>Name</th>
									<th>Gender</th>
                                    <th>Status</th>
                                    <th>Employer</th>
									<th>Company</th>
                                    <th>Job Offer</th>
                                    <th>Salary offer</th>
                                    <th>Work Experience</th>
                                    <th>Passport</th>
                                    <th>Preferred position</th>
                                    <th>Sub position</th>
                                    <th>Preferred country</th>
                                    <th>Medical result</th>
                                    <th>NBI</th>
                                    <th>Trade Test</th>
                                    <th>COE</th>
                                    <th>Picture status</th>
                                    <th>School records</th>
                                    <th>Insurance #</th>
                                    <th>Visa</th>
                                    <th>Ticket</th>
                                    <th>OEC #</th>
                                    <th>OWWA certificate</th>
                                    <th>Contract</th>
                                    <th>Accounting Status</th>
                                    <th>Remarks</th>
                                    <th>Date applied</th>
                                    <th>Civil status</th>
                                    <th>Source</th>
                                    <th>Last updated by</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $ctr = $paginationCounter['from']; ?>
                                <?php foreach ( $applicants as $applicant ): ?>
                                <tr class="<?php echo in_array( $_SESSION['admin']['user']['user_type'], [7]) ? 'record-except' : ''; ?>">
									  <td>
                                        <div class="checkbox">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" class="inverted applicants-selection" name="cyd_applicant_select" value="<?php echo $applicant['applicant_id']; ?>" >
                                                    <span class="text"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </td>
                                    
									<td class="numeric">
                                        <?php if( in_array( $_SESSION['admin']['user']['user_type'], [7]) ):  ?>
                                        --
                                        <?php else: ?>
                                        <button data-url="<?php echo site_url( 'admin/applicants/review/' . str_pad( $applicant['applicant_id'], 9, '0', STR_PAD_LEFT ) ); ?>" class="btn-applicant-review btn btn-default btn-xs" data-toggle="modal" data-target="#modalApplicantReview">
                                            <i class="fa fa-search"></i> Review
                                        </button>  
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $ctr; ?>
                                    </td>
									
                                    <td>
                                        <?php echo fdate( 'd-M-Y', $applicant['applicant_date_applied'], '0000-00-00' ); ?>
                                    </td>
                                    <td class="numeric">
                                       <?php echo $_SESSION["settings"]['client_short']; ?>-<?php echo str_pad( $applicant['applicant_id'], 7, '0', STR_PAD_LEFT ); ?> 
                                    </td>
                                    <td class="numeric">
                                        <div class="applicant-photo hide" align="center">
                                        <?php if ( is_file( DIR_UPLOADS.'applicant'.DIRECTORY_SEPARATOR.$applicant['applicant_photo'] ) ): ?>
                                            <img src="<?php echo base_url(); ?>files/applicant/<?php echo $applicant['applicant_photo']; ?>" alt="" height="100"/>
                                        <?php else: ?>
                                            <img src="<?php echo $app->getPath()['images']; ?>avatars/no-picture.jpg" alt=""  height="100" />
                                        <?php endif; ?>
                                        <br>
                                        </div>
                                        <?php if( in_array( $_SESSION['admin']['user']['user_type'], [7]) ):  ?>
                                        <a href="javascript:void();" >
                                            <strong><?php echo $applicant['applicant_first'].' '.$applicant['applicant_middle'].' '.$applicant['applicant_last']; ?></strong>
                                        </a>
                                        <?php else: ?>
                                        <a class="btn-applicant-review" data-toggle="modal" data-target="#modalApplicantReview" role="button" href="javascript:void();" data-url="<?php echo site_url( 'admin/applicants/review/' . $applicant['applicant_slug'] ); ?>" title="Review">
                                            <strong><?php echo $applicant['applicant_first'].' '.$applicant['applicant_middle'].' '.$applicant['applicant_last']; ?></strong>
                                        </a> 
                                        <?php endif; ?> 
                                        <?php 
                                        if($applicants_raw[$applicant['applicant_id']]->applicantNumber != '')
                                            echo '- '.$applicants_raw[$applicant['applicant_id']]->applicantNumber; 
                                        ?>
                                    </td>
									<td>
                                    <?php
                                    if($applicant['applicant_gender']=='Female'){
                                        $gender='F';
                                    }
                                    if($applicant['applicant_gender']=='Male'){
                                        $gender='M';
                                    }
                                    ?>
									<?php echo $gender; ?>
									</td>
									
                                    <td class="numeric">
                                    	<span class="label label-<?php echo $statusColors[ $applicant['applicant_status'] ] ?> graded">
                                        <?php echo $statusText[ $applicant['applicant_status'] ]; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        if($searchEmployer == ''){
                                            echo ! is_null( $applicant['employer_names'] ) ? $applicant['employer_names'].$applicant['remove_employer_link'] : '--'; 
                                        }else{
                                            echo $employers[$searchEmployer]['employer_name'];
                                        }
                                        ?> 
                                    </td>
									
									  <td>
										<?php 
										if($applicants_raw[$applicant['applicant_id']]->sub_employer != '')
										echo ''.$applicants_raw[$applicant['applicant_id']]->sub_employer; 
										?>
                                    </td>
									
                                    <td>
                                        <?php echo ! is_null( $applicant['job_name'] ) ? $applicant['job_name'] : '--'; ?> 
                                    </td>
                                    <td>
                                        <span><?php echo number_format( $applicant['requirement_offer_salary'], 2 ); ?></span>
                                    </td>
                                    <td>
                                        <?php
                                            echo floor($appTotalExp[$applicant['applicant_id']] / 12) . ' Year(s) And '; 
                                            echo ($appTotalExp[$applicant['applicant_id']]) % 12 . ' Month(s)'; 
                                        ?>
                                    </td>
                                    <td class="numeric">
                                        <?php if ( ! empty( $applicant['passport_number'] ) ): ?>
                                        <?php echo $applicant['passport_number']; ?>
                                        <?php else: ?>
                                        --
                                        <?php endif; ?>
                                    </td>                                    
                                    <td>
                                        <?php echo $applicant['position_name']; ?>
                                    </td>                                 
                                    <td>
                                        <?php 
                                        if(isset($subpositions[$applicant['applicant_id']]))
                                            echo $subpositions[$applicant['applicant_id']]; 
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo $applicant['country_name']; ?>
                                    </td>
                                    <td>
                                        <?php echo $applicant['certificate_medical_result']; ?>
                                    </td>
                                    <td>
                                        <?php if (  $applicant['certificate_authenticated_nbi'] == 1 ): ?>
                                        <span class="text-success"><i class="fa fa-check"></i></span>
                                        <?php else: ?>
                                        --
                                        <?php endif; ?>
                                    </td>
                                    <td align="center">
                                        <?php if (  $applicant['requirement_trade_test'] == 1 ): ?>
                                        <span class="text-success"><i class="fa fa-check"></i></span>
                                        <?php else: ?>
                                        --
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td align="center">
                                        <?php if (  $applicant['requirement_coe'] == 1 ): ?>
                                        <span class="text-success"><i class="fa fa-check"></i></span>
                                        <?php else: ?>
                                        --
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $applicant['requirement_picture_status']; ?>
                                    </td>
                                    <td>
                                        <?php echo $applicant['requirement_school_records']; ?>
                                    </td>
                                    <td>
                                        <?php
                                        if(isset($applicant_certificate_raw[$applicant['applicant_id']]->insurance_no))
                                            echo $applicant_certificate_raw[$applicant['applicant_id']]->insurance_no;
                                        ?>
                                    </td>
                                    <td align="center">
                                        <?php if (  $applicant['requirement_visa'] == 1 ): ?>
                                        <span class="text-success"><i class="fa fa-check"></i></span>
                                        <?php else: ?>
                                        --
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $applicant['requirement_ticket']; ?>
                                    </td>
                                    <td>
                                        <?php echo $applicant['requirement_oec_number']; ?>
                                    </td>
                                    <td>
                                        <?php echo $applicant['requirement_owwa_certificate']; ?>
                                    </td>
                                    <td>
                                        <?php echo fdate( 'd M Y', $applicant['requirement_contract'], '--' ); ?>
                                    </td>
                                    <td class="primary">
                                        <?php echo $applicant['applicant_paid'] ? 'PAID' : '--'; ?>
                                    </td>
                                    <td>
                                        <?php echo str_short( $applicant['applicant_remarks'], 100 ); ?>
                                    </td>
                                    <td class="numeric">
                                        <?php echo fdate( 'd M Y', $applicant['applicant_date_applied'], '0000-00-00' ); ?>
                                    </td>
                                    <td class="numeric">
                                        <?php echo $applicant['applicant_civil_status']; ?>
                                    </td>
                                    <td class="numeric">
                                    	<?php if ( $applicant['applicant_source'] == 0 ): ?>
                                        <span class="text-danger">DIRECT HIRE</span>
                                        <?php else: ?>
                                        <?php echo $applicant['agent_first'].' '.$applicant['agent_last']; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="numeric">
                                        <?php if ( ! is_null( $applicant['user_id'] ) ): ?>
                                        <a href="#"><small><?php echo $applicant['user_fullname']; ?></small></a>
                                        <?php else: ?>
                                        <small>-- applicant --</small>
                                        <?php endif; ?>
                                    </td>
                               
                                </tr>
                                <?php $ctr++; ?>
                                <?php endforeach; ?>
                            </tbody>
                            <?php endif; ?>
                        </table> 
                    </div><!-- .table-responsive -->
                    <hr>
                    <div class="footer" align="right">
                        <span class="pull-left">
                           Showing <?php echo $paginationCounter['from']; ?> to <?php echo $paginationCounter['to']; ?> of <?php echo $paginationCounter['total-records']; ?> entries
                        </span>
                        <?php echo $paginationHTML; ?>
                        <div class="clearfix"></div>
                    </div> 
					
					
					
					<hr>

                    <div class="row">

                        <div class="col-sm-3">

                            <p><strong>Send Applicants To:</strong></p>                            

                            

                            <div class="input-group">

                              <select id="employers-selection" class="form-control">

                                    <option value="">-- Select Employer --</option>

                                    <?php foreach ( $employers as $employer ): ?>

                                    <option value="<?php echo $employer['employer_id']; ?>"><?php echo $employer['employer_name']; ?></option>

                                    <?php endforeach; ?>

                                </select>
                              <span class="input-group-btn">
                                <button class="btn btn-sm btn-primary" id="btn-send-applicant">Send Applicants</button>
                              </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /Page Body -->
