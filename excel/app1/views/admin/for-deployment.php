<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
    <ul class="breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url('admin/'); ?>">Home</a>
        </li>
        <li>Applicants</li>
        <li class="active">For Deployment</li>
    </ul>
</div>
<!-- /Page Breadcrumb -->
<!-- Page Header -->
<div class="page-header position-relative">
    <div class="header-title">
        <h1>
            For Deployment Applicants
        </h1>
    </div>
   <!--Header Buttons-->
    <div class="header-buttons">
        <a class="sidebar-toggler" href="#">
            <i class="fa fa-arrows-h"></i>
        </a>
        <a class="refresh" id="refresh-toggler" href="<?php echo site_url( 'admin/applicants/deployed' ); ?>">
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
                            <form method="get">
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
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <label>Date applied</label>
                                    <div class="input-group ">
                                        <input name="search[date-applied][from]" type="text" data-date-format="yyyy-mm-dd" class="form-control input-sm date-picker" placeholder="yyyy-mm-dd" value="<?php echo isset( $get['search']['date-applied']['from'] ) ? $get['search']['date-applied']['from'] : date( 'Y-m-01', time() ); ?>">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <span class="input-group-addon">to</span>
                                        <input name="search[date-applied][to]" type="text" data-date-format="yyyy-mm-dd" class="form-control input-sm date-picker" placeholder="yyyy-mm-dd" value="<?php echo isset( $get['search']['date-applied']['to'] ) ? $get['search']['date-applied']['to'] : date( 'Y-m-t', time() ); ?>">
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
                        <table class="table table-bordered table-hover table-applicants">
                            <?php if ( count( $applicants ) == 0 ): ?>
                            <tr>
                                <td>No entries</td>
                            </tr>
                            <?php else: ?>
                            <thead class="bordered-palegreen">
                                <tr>
									 <th></th>
                                    <th>#</th>
                               
                                    <th>Applicant #</th>
                                    <th>Name</th>
                                    <th>Company</th>
                                    <th>Job Offer</th>
                                    <th>Passport</th>
                                    <th> Position</th>
                                    <th> Country</th>
                                    <th>Medical result</th>
                                    <th>VISA</th>
									 <th>Contract</th>
                                    <th>Tesda</th>
									<th>PDOS</th>
									<th>VISA STAMP SUBMISSION</th>
									<th>VISA STAMP RELEASE</th>
									<th>OWWA Submission</th>
									
                                    <th>OWWA certificate</th>
                                    <th>Insurance #</th>
									<th>OEC</th>
									<th>Ticket</th>
									<th>Remarks</th>
                             
                                    <th>Accounting Status</th>
                                    
                                    <th>Source</th>
                                    <th>Last updated by</th>
                               
                                </tr>
                            </thead>
                            <tbody>
                                <?php $ctr = $paginationCounter['from']; ?>
                                <?php foreach ( $applicants as $applicant ): ?>
                                <tr>
									  <td class="numeric">
                                        <button data-url="<?php echo site_url( 'admin/applicants/review/' . str_pad( $applicant['applicant_id'], 9, '0', STR_PAD_LEFT ) ); ?>" class="btn-applicant-review btn btn-default btn-xs" data-toggle="modal" data-target="#modalApplicantReview">
                                            <i class="fa fa-search"></i> Review
                                        </button>  
                                    </td>
								
                                    <td>
                                        <?php echo $ctr; ?>
                                    </td>
                                 
                                    <td class="numeric">
                                       ATP-<?php echo str_pad( $applicant['applicant_id'], 7, '0', STR_PAD_LEFT ); ?> 
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
                                    </td>
                                    <td>
                                        <?php echo ! is_null( $applicant['employer_name'] ) ? $applicant['employer_name'] : '--'; ?> 
                                    </td>
                                    <td>
                                        <?php echo ! is_null( $applicant['job_name'] ) ? $applicant['job_name'] : '--'; ?> 
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
                                        <?php echo $applicant['country_name']; ?>
                                    </td>
                                    <td>
                                        <?php echo $applicant['certificate_medical_result']; ?>
                                    </td>
                                 
                                  
                                    
                                    <td align="center">
                                        <?php if (  $applicant['requirement_visa'] == 1 ): ?>
                                        <span class="text-success"><i class="fa fa-check"></i></span>
                                        <?php else: ?>
                                        --
                                        <?php endif; ?>
                                    </td>
									 <td>
                                        <?php echo fdate( 'd M Y', $applicant['requirement_contract'], '--' ); ?>
                                    </td>
									
									 <td>
                                    
										 <?php if (  $applicant['certificate_tesda'] == 1 ): ?>
                                        <span class="text-success"><i class="fa fa-check"></i></span>
                                        <?php else: ?>
                                        --
                                        <?php endif; ?>
                                    </td>
									
										 <td>
                                    
										 <?php if (  $applicant['certificate_pdos'] == 1 ): ?>
                                        <span class="text-success"><i class="fa fa-check"></i></span>
                                        <?php else: ?>
                                        --
                                        <?php endif; ?>
                                    </td>
									
									<td>
                                       <?php echo fdate( 'd-M-Y', $applicant['requirement_visa_date'], '0000-00-00' ); ?>
                                    </td>
									
									<td>
                                       <?php echo fdate( 'd-M-Y', $applicant['requirement_visa_release_date'], '0000-00-00' ); ?>
                                    </td>
									
									
                                    
									 <td>
                                       <?php echo fdate( 'd-M-Y', $applicant['requirement_owwa_schedule'], '0000-00-00' ); ?>
                                    </td>
									
                                    <td>
                                        <?php echo $applicant['requirement_owwa_certificate']; ?>
                                    </td>
									
									 <td>
                                        <?php
                                        if(isset($applicant_certificate_raw))
                                            echo $applicant_certificate_raw[$applicant['applicant_id']]->insurance_no;
                                        ?>
                                    </td>
									
									<td>
                                        <?php echo $applicant['requirement_oec_number']; ?>
                                    </td>
								    
									<td>
										<?php
										if(isset($applicant_requirement_raw))
                                            echo $applicant_requirement_raw[$applicant['applicant_id']]->flight_date;
										?>
                                    </td>
								   
								     <td>
                                        <?php echo str_short( $applicant['applicant_remarks'], 100 ); ?>
                                    </td>
                                   
                                    <td class="primary">
                                        <?php echo $applicant['applicant_paid'] ? 'PAID' : '--'; ?>
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
                </div>

            </div>
            
            
        </div>
    </div>
</div>
<!-- /Page Body -->
