<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
    <ul class="breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url('admin/'); ?>">Home</a>
        </li>
        <li>Billing</li>
        <li><a href="<?php echo site_url( 'admin/billing/employers' ); ?>">Due to employer</a></li>
        <li class="active"><?php echo $employer['employer_name']; ?></li>
    </ul>
</div>
<!-- /Page Breadcrumb -->
<!-- Page Header -->
<div class="page-header position-relative">
    <div class="header-title">
        <h1>
            Billing due to employer
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
        <div class="col-lg-12 col-sm-12 col-xs-12">
              
            <div class="row">
                <div class="col-lg-12 col-sm-12 col-xs-12">
                   
                    <div class="row">
                        <div class="col-lg-10 col-md-10 col-sm-12 col-xs-12">
                            <div class="widget flat radius-bordered">
                                <div class="widget-header bg-blue">
                                    <span class="widget-caption"><?php echo $employer['employer_name']; ?></span>
                                </div>
                                <div class="widget-body">
									<?php $app->renderAlerts(); ?>
                                    
                                    <table class="table table-hover table-condensed">
                                    	<?php $currentApplicant = 0; ?>
                                    	<?php foreach ( $employer['applicants'] as $applicant ): ?>
                                        <?php $totalCost = 0; ?>
                                    	<thead>
                                        	<tr>
                                            	<th>Applicant</th>
                                                <th>Job Offer</th>
                                                <th>Description</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        	<tr>
                                            	<td><?php echo $applicant['applicant_first'].' '.$applicant['applicant_last']; ?></td>
                                                <td><?php echo $applicant['job_name']; ?></td>
                                                <td>
                                                	<?php foreach ( $applicant['breakdown'] as $fee ): ?>
                                                    <?php if ( ! $fee['bill_employer_cost'] ) continue; ?>
                                                    <?php $totalCost += $fee['bill_employer_cost'] ; ?>
                                                    <p class="fee-text"><?php echo $fee['fee_name']; ?></p>
                                                    <?php endforeach; ?>
                                                </td>
                                                <td>
                                                	<?php foreach ( $applicant['breakdown'] as $fee ): ?>
                                                    <?php if ( ! $fee['bill_employer_cost'] ) continue; ?>
                                                    <p class="fee-amount">&#8369;&nbsp;<span class="text-danger"><?php echo number_format( $fee['bill_employer_cost'], 2 ); ?></span></p>
                                                    <?php endforeach; ?>
                                                </td>
                                            </tr>
                                            
                                            <tr>
                                            	<td colspan="2"></td>
                                                <td>
                                                	<p class="fee-text">Total:</p>                                                    
                                                </td>
                                                <td>
                                                	<p class="fee-amount"><strong>&#8369;&nbsp;<span class="text-danger"><?php echo number_format( $totalCost, 2 ); ?></span></strong></p>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <?php endforeach; ?>
                                    </table>
                                    
                                    <hr class="wide">
                                    <div>
                                        <a href="#" class="btn btn-info">Export to PDF...</a>
                                    </div>

                                    <div class="clearfix"></div>
                                    
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