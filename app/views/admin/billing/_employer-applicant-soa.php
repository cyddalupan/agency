<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
    <ul class="breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url('admin/'); ?>">Home</a>
        </li>
        <li>
        	<a href="<?php echo site_url( 'admin/applicants/all' ); ?>">Applicant</a>
       	</li>
        <li>
        	<a href="#"><?php echo $applicant['applicant_name']; ?></a>
       	</li>
        <li class="active">Billing</li>
    </ul>
</div>
<!-- /Page Breadcrumb -->
<!-- Page Header -->
<div class="page-header position-relative">
    <div class="header-title">
        <h1>
            Billing due to worker
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
                                    <span class="widget-caption">Company: <?php echo $applicant['employer_name']; ?></span>
                                </div>
                                <div class="widget-body">
									<?php $app->renderAlerts(); ?>
                                    <div id="registration-form">
                                        <form class="form" role="form" method="post" enctype="multipart/form-data">
                                            <div class="form-title">
                                            	<span><?php echo $applicant['applicant_name']; ?></span>
                                                <a href="#" class="pull-right btn btn-xs btn-default">
                                                <?php echo $applicant['job_name']; ?>
                                                </a>
                                            	<div class="clearfix"></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-8">
                                                     
                                                    <table class="table table-bordered table-condensed">
                                                        <thead>
                                                            <tr>
                                                                <th width="40%">&nbsp;</th>
                                                                <th>Amount</th> 
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php $totalAmount = 0; ?>
                                                            <?php $currentGroup = ''; ?>
                                                            <?php foreach ( $jobFees as $feeId => $fee ): ?>
                                                            <?php $totalAmount += $billDetail[$feeId]['detail_amount'];  ?>
                                                            <?php if ( $currentGroup != $fee['fee_group'] ): ?>
                                                            <?php $currentGroup = $fee['fee_group'];  ?>
                                                            <thead>
                                                                <tr>
                                                                    <th align="center" colspan="3"><?php echo $fee['fee_group']; ?></th>
                                                                </tr>
                                                            </thead>
                                                            <?php endif; ?>
                                                            <tr>
                                                                <td><?php echo $fee['fee_name']; ?></td>
                                                                <td>
                                                                    <div class="input-group">
                                                                      <span class="input-group-addon">&#8369;</span>
                                                                      <input name="bill[fee][<?php echo $feeId; ?>][amount]" type="text" class="form-control fee-amount" placeholder="0.00" value="<?php echo isset( $billDetail[$feeId] ) ? $billDetail[$feeId]['detail_amount'] : '' ?>"> 
                                                                    </div>
                                                                </td> 
                                                            </tr>
                                                            <?php endforeach; ?>
                                                            <tr>
                                                                <th>Total revenue:</th>
                                                                <td colspan="3">
                                                                    &#8369; &nbsp;<span class="text-danger total-revenue"><?php echo number_format( $totalAmount, 2); ?></span>
                                                                </td>
                                                            </tr>
                                                    </table>
                                                </div>
                                            </div>
                                            
                                            <hr class="wide" />
                                            
                                            <div class="row">
                                                <div class="col-sm-10">
                                                	<div class="form-group">
                                                        <label>Remarks <small><em>(optional)</em></small></label>
                                                        <span class="input-icon icon-right">
                                                            <textarea name="bill[remarks]" class="form-control" rows="1" placeholder="Write some remarks..."><?php echo $bill['bill_remarks']; ?></textarea>
                                                            <i class="fa fa-briefcase darkorange"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                             
                                            
                                            <hr class="wide" />
                                            <button type="reset" class="btn btn-default">Reset Form</button>
                                            <button type="submit" class="btn btn-blue">Save</button>
                                            
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