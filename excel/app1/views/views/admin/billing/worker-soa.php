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
                <div class="col-lg-12 col-sm-12 col-xs-12">
                   
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <div class="widget flat radius-bordered">
                                <div class="widget-header bg-blue">
                                    <span class="widget-caption"><?php echo $worker['applicant_first'].' '.$worker['applicant_last']; ?></span>
                                </div>
                                <div class="widget-body">
									<?php $app->renderAlerts(); ?>

                                    <p>
                                        <span>Ref#: <strong>SOA-<?php echo str_pad( $bill['bill_id'], 6, '0', STR_PAD_LEFT ); ?></strong></span>
                                        <span class="pull-right">Date: <strong><?php echo fdate( 'm/d/Y', $bill['bill_created'], '0000-00-00' ); ?></strong></span>
                                    </p>

                                    <p>&nbsp;</p>
                                    
                                    <table class="table table-hover table-condensed">
                                        <?php $totalAmount = $totalCredit = $totalDebit = 0; ?>
                                    	<thead>
                                        	<tr>
                                                <th>Description</th>
                                                <th>Amount</th>
                                                <th class="warning">Remaining Balance</th>
                                                <th>Paid</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ( $fees as $feeId => $fee ): ?>
                                            <?php $totalAmount += $fee['detail_applicant_cost']; ?>
                                            <?php $credit = $fee['detail_applicant_cost'] - $fee['detail_applicant_deposit'] > 0 ? $fee['detail_applicant_cost'] - $fee['detail_applicant_deposit'] : 0; ?>
                                            <?php $totalCredit += $credit; ?>
                                            <?php $totalDebit  += $fee['detail_applicant_deposit']; ?>
                                            <?php $isPaid       = $fee['detail_applicant_deposit'] >= $fee['detail_applicant_cost']; ?>
                                            <tr class="">
                                                <td>
                                                    <p class="fee-text">
                                                        <?php echo $fee['fee_name']; ?>
                                                        <?php if ( $isPaid ): ?>
                                                            &nbsp;<span class="text-success"><i class="fa fa-check"></i></span>
                                                        <?php endif; ?>
                                                    </p>
                                                </td>
                                                <td width="20%">
                                                    <?php if ( $fee['fee_id'] == 1 ): ?>
                                                    <p class="fee-amount">
                                                        &#8369;&nbsp;<span class="text-default"><?php echo number_format( $fee['detail_applicant_cost'], 2 ); ?></span>
                                                        <?php if ( $bill['bill_applicant_deposit'] < $bill['bill_applicant_cost'] ): ?>
                                                        (<a href="#" role="button" id="btn-change-placement-fee">Edit</a>)
                                                        <?php endif; ?>
                                                    </p>
                                                    <form id="frmEditPlacementFee" class="form-inline hide" method="post" action="<?php echo site_url( 'admin/billing/worker-soa/'.$worker['applicant_slug'] ); ?>">
                                                        <input name="placement-fee[amount]" type="text" placeholder="0.00" class="form-control input-xs" value="<?php echo $fee['detail_applicant_cost']; ?>" required>
                                                        <button type="submit" class="btn btn-xs btn-info">Save</button>
                                                    </form>
                                                    <?php else: ?>
                                                    <p class="fee-amount">
                                                        &#8369;&nbsp;<span class="text-default"><?php echo number_format( $fee['detail_applicant_cost'], 2 ); ?></span>
                                                    </p>
                                                    <?php endif; ?>
                                                </td>
                                                <td width="20%">
                                                    <p class="fee-amount">&#8369;&nbsp;<span class="<?php echo $isPaid ? 'text-danger' : 'text-default'; ?>"><?php echo number_format( $credit, 2 ); ?></span></p>
                                                </td>
                                                <td width="20%">
                                                    <p class="fee-amount">&#8369;&nbsp;<span class="text-primary"><?php echo number_format( $fee['detail_applicant_deposit'], 2 ); ?></span></p>
                                                </td>
                                            </tr> 
                                            <?php endforeach; ?>
                                            <tr>
                                                <td align="right">
                                                    Total:
                                                </td>
                                                <td>
                                                    <p class="fee-amount">
                                                        <strong>&#8369;&nbsp;<span class="text-danger"><?php echo number_format( $totalAmount, 2 ); ?></span></strong>
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="fee-amount">
                                                        <strong>&#8369;&nbsp;<span class="text-danger"><?php echo number_format( $totalCredit, 2 ); ?></span></strong>
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="fee-primary">
                                                        <strong>&#8369;&nbsp;<span class="text-primary"><?php echo number_format( $totalDebit, 2 ); ?></span></strong>
                                                    </p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    
                                    <hr class="wide">
                                    <div>
                                        <span class="pull-right">
                                            <?php if ( $bill['bill_applicant_deposit'] >= $bill['bill_applicant_cost'] ): ?>
                                            <span class="text-success"><i class="fa fa-check"></i>&nbsp;Paid</span>
                                            <?php else: ?>
                                            <a href="<?php echo site_url( 'admin/billing/payment-worker/'.str_pad( $bill['bill_id'], 9, '0', STR_PAD_LEFT ) ); ?>" class="btn btn-xs btn-default">
                                                <i class="fa fa-dollar"></i> PAID
                                            </a>
                                            <?php endif; ?>
                                        </span>
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