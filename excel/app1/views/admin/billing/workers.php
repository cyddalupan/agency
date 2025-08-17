<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
    <ul class="breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url('admin/'); ?>">Home</a>
        </li>
        <li class="">Billing</li>
        <li class="active">Applicants</li>
    </ul>
</div>
<!-- /Page Breadcrumb -->
<!-- Page Header -->
<div class="page-header position-relative">
    <div class="header-title">
        <h1>
            Due to Workers
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
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-applicants">
                            <thead class="bordered-palegreen">
                                <tr>
                                    <th>Date</th>
                                    <th>REF #</th>
                                    <th>Worker</th>
                                    <th>Company</th>
                                    <th>Job Offer</th>
                                    <th>Amount</th>
                                    <th>Remaining balance</th>
                                    <th>Paid</th>
                                    <th>&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ( count( $workers ) == 0 ): ?>
                                <tr>
                                    <td colspan="12" align="center">-- No records  --</td>
                                </tr>
                                <?php endif; ?>
                                <?php foreach ( $workers as $worker ): ?>
                                <?php $credit = $worker['bill_applicant_cost'] - $worker['bill_applicant_deposit'] > 0 ? $worker['bill_applicant_cost'] - $worker['bill_applicant_deposit'] : 0; ?>
                                <tr>
                                    <td><?php echo fdate( 'm/d/Y', $worker['bill_created'], '0000-00-00' ); ?></td>
                                    <td>SOA-<?php echo str_pad( $worker['bill_id'], 6, '0', STR_PAD_LEFT ); ?></td>
                                	<td>
                                        <p <?php echo ! $credit ? 'style="text-decoration:strikethrough"' : ''; ?>>
                                        <a href="<?php echo site_url( 'admin/billing/worker-soa/'.$worker['applicant_slug'] ); ?>" class="">
                                        <strong><?php echo $worker['applicant_name']; ?></strong>
                                        </a>
                                        </p>
                                    </td>
                                    <td><?php echo $worker['employer_name'] ?></td>
                                    <td><?php echo $worker['job_name'] ?></td>
                                    <td>
										&#8369;&nbsp;<span class="text-default"><?php echo number_format( $worker['bill_applicant_cost'], 2 ); ?></span>
                                    </td>
                                    <td>
										&#8369;&nbsp;<span class="text-danger"><?php echo number_format( $credit, 2 ); ?></span>
                                    </td>
                                    <td>
										&#8369;&nbsp;<span class="text-info"><?php echo number_format( $worker['bill_applicant_deposit'], 2 ) ?></span>
                                    </td>
                                    <td>
                                    	<a href="<?php echo site_url( 'admin/billing/worker-soa/'.$worker['applicant_slug'] ); ?>" class="btn btn-xs btn-default">Open</a>
                                        <?php if ( ! $credit ): ?>
                                        <p><span class="text-success"><i class="fa fa-check"></i>&nbsp;Paid</span></p>
                                        <?php endif; ?>
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
