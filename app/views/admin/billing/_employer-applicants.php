<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
    <ul class="breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url('admin/'); ?>">Home</a>
        </li>
        <li class="">Billing</li>
        <li class="active">Employers' Selected Applicants</li>
    </ul>
</div>
<!-- /Page Breadcrumb -->
<!-- Page Header -->
<div class="page-header position-relative">
    <div class="header-title">
        <h1>
            <?php echo $employer['employer_name']; ?>
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
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <div class="widget">

                <div class="widget-header with-footer">
                    <span class="widget-caption">
                        <?php echo $employer['employer_name']; ?>
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
                                    <th>Applicant</th>
                                    <th>Job Offer</th>
                                    <th>Amount</th>
                                    <th>Remarks</th>
                                    <th>Source</th>
                                    <th>Last updated by</th>
                                    <th></th> 
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ( count( $employer['applicants'] ) == 0 ): ?>
                                <tr>
                                    <td colspan="12" align="center">-- No applicants yet --</td>
                                </tr>
                                <?php endif; ?>
                                <?php foreach ( $employer['applicants'] as $applicant ): ?>
                                <tr>
                                    <td>
                                        <a class="btn-applicant-review" data-toggle="modal" data-target="#modalApplicantReview" role="button" href="javascript:void();" data-url="<?php echo site_url( 'admin/applicants/review/' . $applicant['applicant_slug'] ); ?>" title="Review">
                                            <strong><?php echo $applicant['applicant_name']; ?></strong>
                                        </a>
                                    </td>
                                    <td>
                                        <?php echo $applicant['job_name']; ?>
                                    </td>
                                    <td>
                                    	<strong>&#8369; <span class="text-danger"><?php echo number_format( $applicant['amount'] ); ?></span></strong>
                                    </td>
                                    <td>
                                    	<?php echo str_short( $applicant['applicant_remarks'], 100 ); ?>
                                    </td>
                                    <td class="numeric">
                                    	<?php if ( $applicant['applicant_source'] == 0 ): ?>
                                        <span class="text-danger">DIRECT HIRE</span>
                                        <?php else: ?>
                                        <?php echo $applicant['agent_first'].' '.$applicant['agent_last']; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="numeric">
                                        <a href="#"><small><?php echo $applicant['user_fullname']; ?></small></a>
                                    </td>
                                    <td class="numeric">
                                        <a href="<?php echo site_url( 'admin/billing/worker-soa/'.$applicant['applicant_slug']); ?>" class="btn btn-default btn-xs">
                                            <i class="fa fa-search"></i> Open billing
                                        </a>  
                                    </td> 
                                </tr>
                                <?php endforeach; ?>
                                
                            </tbody>
                        </table> 
                    </div><!-- .table-responsive -->
                    <hr>
                    <div class="footer" align="right">
                        &nbsp;
                        <div class="clearfix"></div>
                    </div> 
                </div>

            </div>
            
            
        </div>
    </div>
</div>
<!-- /Page Body -->
