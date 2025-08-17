<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
    <ul class="breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url('admin/'); ?>">Home</a>
        </li>
        <li>Billing</li>
        <li><a href="<?php echo site_url( 'admin/billing/workers' ); ?>">Due to worker</a></li>
        <li class="active"><?php echo $worker['applicant_first'].' '.$worker['applicant_last']; ?></li>
    </ul>
</div>
<!-- /Page Breadcrumb -->
<!-- Page Header -->
<div class="page-header position-relative">
    <div class="header-title">
        <h1>
            <?php echo $worker['applicant_first'].' '.$worker['applicant_last']; ?>
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
                <div class="col-lg-10 col-sm-10 col-xs-12">
                   
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <div class="widget flat radius-bordered">
                                <div class="widget-header bg-blue">
                                    <span class="widget-caption">
                                        <a href="<?php echo site_url( 'admin/billing/worker-soa/'.$worker['applicant_slug'] ); ?>" class="btn btn-xs btn-default">&laquo;Back</a>
                                    </span>
                                </div>
                                <div class="widget-body">
									<?php $app->renderAlerts(); ?>
                                    <form method="post" class="form"  action="#">
                                    <h2>
                                        OR# <input type="text" name="or" value="<?php echo $ORId; ?>" style="color:#a94442" size="10">
                                    </h2>
                                    <span class="pull-right">Ref#: <strong>SOA-<?php echo str_pad( $bill['bill_id'], 6, '0', STR_PAD_LEFT ); ?></strong></span>
                                    <p>&nbsp;</p>
                                    <table class="table table-hover table-condensed">
                                        <thead>
                                            <tr>
                                                <th>Worker</th>
                                                <th>Job Offer</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <?php echo $worker['applicant_first'].' '.$worker['applicant_last']; ?>
                                                </td>
                                                <td>
                                                    <?php echo $worker['job_name']; ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <p>&nbsp;</p>
                                    
                                    <table class="table table-hover table-condensed">
                                    	<thead>
                                        	<tr>
                                                <th>Description</th>
                                                <th>Amount</th>
                                                <th>Remaining Balance</th>
                                                <th>Deposit</th>
                                                <th>Paid</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ( count( $fees ) == 0 ): ?>
                                            <tr>
                                                <td colspan="5" align="center">-- No more credits to pay --</td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php foreach ( $fees as $feeId => $fee ): ?>
                                            <tr>
                                                <td>
                                                    <p class="fee-text"><?php echo $fee['fee_name']; ?></p>    
                                                </td>
                                                <td>
                                                    <p class="fee-amount">&#8369;&nbsp;<span class="text-default"><?php echo number_format( $fee['detail_applicant_cost'], 2 ); ?></span></p>
                                                </td>
                                                <td>
                                                    <p class="fee-amount">&#8369;&nbsp;<span class="text-danger"><?php echo number_format( $fee['detail_applicant_cost'] - $fee['detail_applicant_deposit'], 2 ); ?></span></p>
                                                </td>
                                                <td>
                                                    <p class="fee-amount">&#8369;&nbsp;<span class="text-primary"><?php echo number_format( $fee['detail_applicant_deposit'], 2 ); ?></span></p>
                                                </td>
                                                <td> 
                                                    <div class="input-group col-sm-6">
                                                      <span class="input-group-addon">&#8369;</span>
                                                      <input name="payment[fee][<?php echo $feeId; ?>]" type="number" class="form-control fee-amount" placeholder="0.00" max="<?php echo $fee['detail_applicant_cost'] - $fee['detail_applicant_deposit']; ?>" min="0"> 
                                                    </div>                                                     
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        	
                                        </tbody>
                                    </table>
                                    <?php if ( count( $fees ) > 0 ): ?>
                                    <hr class="wide">
                                    <div align="right">
                                        <button type="reset" class="btn btn-default">Clear</button>
                                        <button type="submit" class="btn btn-info">Save</button>
                                    </div>
                                    <?php endif; ?>
                                    </form>

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