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
            For Deloyment Applicants
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
                        <span class="label label-info">For Deployment</span>
                    </span>
                    
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
                    
                    <div class="table-responsive" style="overflow:scroll; padding:10px;">
                        <table class="table table-bordered table-hover table-applicants">
                            <thead class="bordered-palegreen">
                                <tr>
                                    <th>#</th>
                                    <th>Date created</th>
                                    <th>Applicant #</th>
                                    <th>Name</th>
                                    <th>Company</th>
                                    <th>Job Offer</th>
                                    <th>Passport</th>
                                    <th>Preferred position</th>
                                    <th>Preferred country</th>
                                    <th>Medical result</th>
                                    <th>Trade Test</th>
                                    <th>Trade COE</th>
                                    <th>Picture status</th>
                                    <th>School records</th>
                                    <th>Calling visa</th>
                                    <th>OEC #</th>
                                    <th>OWWA certificate</th>
                                    <th>Contract</th>
                                    <th>Remarks</th>
                                    <th>Date applied</th>
                                    <th>Civil status</th>
                                    <th>Source</th>
                                    <th>Last updated by</th>
                                    <th></th> 
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ( count( $applicants ) == 0 ): ?>
                                <tr>
                                    <td colspan="12" align="center">-- No entries --</td>
                                </tr>
                                <?php endif; ?>
                                <?php $ctr = 1; ?>
                                <?php foreach ( $applicants as $applicant ): ?>
                                <tr class="<?php
                                	echo $applicant['certificate_medical_result'] 
                                		&& $applicant['requirement_trade_test'] 
                                		&& $applicant['requirement_coe']
                                		&& $applicant['requirement_picture_status']
                                		&& $applicant['requirement_school_records'] 
                                		&& $applicant['requirement_visa'] 
                                		&& $applicant['requirement_oec_number'] 
                                		&& $applicant['requirement_owwa_certificate'] 
                                		? 'danger' : '';
                                ?>">
                                    <td>
                                        <?php echo str_pad( $ctr, 5, '0', STR_PAD_LEFT ); ?>
                                    </td>
                                    <td>
                                        <?php echo fdate( 'd-M-Y', $applicant['applicant_created'], '0000-00-00' ); ?>
                                    </td>
                                    <td class="numeric">
                                       <?php echo $_SESSION["settings"]['client_short']; ?>-<?php echo str_pad( $applicant['applicant_id'], 7, '0', STR_PAD_LEFT ); ?> 
                                    </td>
                                    <td class="numeric">
                                        <a class="btn-applicant-review" data-toggle="modal" data-target="#modalApplicantReview" role="button" href="javascript:void();" data-url="<?php echo site_url( 'admin/applicants/review/' . $applicant['applicant_slug'] ); ?>" title="Review">
                                            <strong><?php echo $applicant['applicant_first'].' '.$applicant['applicant_middle'].' '.$applicant['applicant_last']; ?></strong>
                                        </a> 
                                    </td>
                                    <td>
                                        <?php echo ! is_null( $applicant['employer_name'] ) ? $applicant['employer_name'] : '--'; ?> 
                                    </td>
                                    <td>
                                        <?php echo ! is_null( $applicant['job_name'] ) ? $applicant['job_name'] : '--'; ?> 
                                    </td>
                                    <td class="numeric">
                                        <?php if ( ! empty( $applicant['passport'] ) ): ?>
                                        <?php echo $applicant['passport']['passport_number']; ?>
                                        <?php else: ?>
                                        FOR UPDATE
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
                                    <td align="center">
                                        <?php if (  $applicant['requirement_visa'] == 1 ): ?>
                                        <span class="text-success"><i class="fa fa-check"></i></span>
                                        <?php else: ?>
                                        --
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $applicant['requirement_oec_number']; ?>
                                    </td>
                                    <td>
                                        <?php echo $applicant['requirement_owwa_certificate']; ?>
                                    </td>
                                    <td>
                                        <?php echo fdate( 'd M Y', $applicant['requirement_contract'], '0000-00-00' ); ?>
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
                                    <td class="numeric">
                                        <button data-url="<?php echo site_url( 'admin/applicants/review/' . str_pad( $applicant['applicant_id'], 9, '0', STR_PAD_LEFT ) ); ?>" class="btn-applicant-review btn btn-default btn-xs" data-toggle="modal" data-target="#modalApplicantReview">
                                            <i class="fa fa-search"></i> Review
                                        </button>  
                                    </td> 
                                </tr>
                                <?php $ctr++; ?>
                                <?php endforeach; ?>
                                
                            </tbody>
                        </table> 
                    </div><!-- .table-responsive -->
                    <hr>
                    <div class="footer" align="right">
                        <span class="pull-left">
                           <?php echo count( $applicants ); ?> entries
                        </span>
                        <div class="clearfix"></div>
                    </div> 
                </div>

            </div>
            
            
        </div>
    </div>
</div>
<!-- /Page Body -->
