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
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <div class="widget ">
                                <div class="widget-header with-footer">
                                    <span class="widget-caption">
                                        <?php if ( ! isset( $_GET['filter']['from'], $_GET['filter']['to'] ) ): ?>
                                        <a class="btn-filter-date" href="#">Filter by date</a>
                                        <?php endif; ?>
                                        <form class="form-inline filter-date <?php echo isset( $_GET['filter']['from'], $_GET['filter']['to'] ) ? '' : 'hide'; ?>">
                                        <div class="input-group ">
                                            <input name="filter[from]" type="text" class="form-control input-sm date-picker" data-date-format="yyyy-mm-dd" placeholder="yyyy-mm-dd" value="<?php echo isset( $_GET['filter']['from'] ) ? $_GET['filter']['from'] : date( 'Y-m-01', time() ); ?>">
                                            <span class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                            <span class="input-group-addon">to</span>
                                            <input name="filter[to]" type="text" class="form-control input-sm date-picker" data-date-format="yyyy-mm-dd" placeholder="yyyy-mm-dd" value="<?php echo isset( $_GET['filter']['to'] ) ? $_GET['filter']['to'] : date( 'Y-m-t', time() ); ?>">
                                            <span class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                        </div>
                                        <button class="btn btn-sm btn-default">Go</button>
                                        </form>
                                    </span>
                                </div>
                                <div class="widget-body">
									<?php $app->renderAlerts(); ?>
                                    <div class="table-responsive">
                                    <table class="table table-hover table-condensed">
                                        <?php if ( count( $workers ) == 0 ): ?>
                                        <tbody>
                                            <tr>
                                            <td align="center">-- No entries --</td>
                                            </tr>
                                        </tbody>
                                        <?php endif;?>
                                        <?php $totalAmount = $totalCredit = $totalDebit = 0; ?>
                                    	<?php foreach ( $workers as $worker ): ?>
                                        <?php   $amount = $credit = $debit = 0; ?>
                                        <?php   foreach ( $worker['breakdown'] as $fee ): ?>
                                        <?php       $amount += $fee['detail_employer_cost']; ?>
                                        <?php       $credit += ( $fee['detail_employer_cost'] - $fee['detail_employer_deposit'] ); ?>
                                        <?php       $debit  += $fee['detail_employer_deposit']; ?>
                                        <?php   endforeach; ?>
                                        <?php   $totalAmount += $amount; ?>
                                        <?php   $totalCredit += $credit; ?>
                                        <?php   $totalDebit  += $debit; ?>
                                    	<thead>
                                        	<tr>
                                                <th>Date</th>
                                                <th>REF #</th>
                                            	<th>Applicant</th>
                                                <th>Job Offer</th>
                                                <th>Description</th>
                                                <th>Amount</th>
                                                <th class="warning">Remaining Balance</th>
                                                <th>Paid</th>
                                                <th>&nbsp;</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        	<tr>
                                                <td><?php echo fdate( 'm/d/Y', $worker['bill_created'], '0000-00-00' ); ?></td>
                                                <td>SOA-<?php echo str_pad( $worker['bill_id'], 6, '0', STR_PAD_LEFT ); ?></td>
                                            	<td>
                                                    <strong><?php echo $worker['applicant_first'].' '.$worker['applicant_last']; ?></strong>
                                                </td>
                                                <td><?php echo $worker['job_name']; ?></td>
                                                <td width="15%">
                                                    <?php foreach ( $worker['breakdown'] as $fee ): ?>
                                                    
                                                    <?php $isPaid       = $fee['detail_employer_deposit'] >= $fee['detail_employer_cost']; ?>
                                                    <p class="fee-text">
                                                        <?php echo $fee['fee_name']; ?>
                                                        <?php if ( $isPaid ): ?>
                                                            &nbsp;<span class="text-success"><i class="fa fa-check"></i></span>
                                                        <?php endif; ?>
                                                    </p>
                                                    <?php endforeach; ?>
                                                </td>
                                                <td width="15%">
                                                    <?php foreach ( $worker['breakdown'] as $fee ): ?>
                                                    <p class="fee-amount">&#8369;&nbsp;<span class="text-default"><?php echo number_format( $fee['detail_employer_cost'], 2 ); ?></span></p>
                                                    <?php endforeach; ?>
                                                </td>
                                                <td width="15%" class="">
                                                    <?php foreach ( $worker['breakdown'] as $fee ): ?>                                                    
                                                    <p class="fee-amount">&#8369;&nbsp;<span class="<?php echo $fee['detail_employer_cost'] - $fee['detail_employer_deposit'] ? 'text-danger' : 'text-default'; ?>"><?php echo number_format( $fee['detail_employer_cost'] - $fee['detail_employer_deposit'], 2 ); ?></span></p>
                                                    <?php endforeach; ?>
                                                </td>
                                                <td width="10%">
                                                    <?php foreach ( $worker['breakdown'] as $fee ): ?>                                                    
                                                    <p class="fee-amount">&#8369;&nbsp;<span class="text-primary"><?php echo number_format( $fee['detail_employer_deposit'], 2 ); ?></span></p>
                                                    <?php endforeach; ?>
                                                </td>
                                                <td width="5%">
                                                    <?php if ( ! $credit ): ?>
                                                    <span class="text-success"><i class="fa fa-check"></i>&nbsp;Paid</span>
                                                    <?php else: ?>
                                                    <a href="<?php echo site_url( 'admin/billing/payment-employer/'.str_pad( $worker['bill_id'], 9, '0', STR_PAD_LEFT ) ); ?>" class="btn btn-xs btn-default"><i class="fa fa-dollar"></i> PAID</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            
                                            <tr>
                                            	<td colspan="4" align="center">&nbsp;</td>
                                                <td>
                                                	<p class="fee-text">Total:</p>                                                    
                                                </td>
                                                <td>
                                                	<p class="fee-amount"><strong>&#8369;&nbsp;<span class="text-default"><?php echo number_format( $amount, 2 ); ?></span></strong></p>
                                                </td>
                                                <td class="">
                                                    <p class="fee-amount"><strong>&#8369;&nbsp;<span class="text-danger"><?php echo number_format( $credit, 2 ); ?></span></strong></p>
                                                </td>
                                                <td>
                                                    <p class="fee-amount"><strong>&#8369;&nbsp;<span class="text-primary"><?php echo number_format( $debit, 2 ); ?></span></strong></p>
                                                </td>
                                                <td>&nbsp;</td>
                                            </tr>
                                        </tbody>
                                        <?php endforeach; ?>
                                    </table>
                                    </div>
                                    <hr class="wide">
                                    <a target="_blank" href="<?php echo site_url();?>admin/billing/print_billing/<?php echo $employer['employer_id']; ?>/<?php echo $employer['employer_name']; ?> " class="btn btn-default pull-right">Print</a>
                                    <div class="clearfix"></div>
                                    <hr class="wide">
                                    <div>
                                        <?php if ( $totalCredit > 0 ): ?>
                                        <span class="pull-right">
                                            <a href="?paid-all<?php echo isset( $_GET['filter']['from'], $_GET['filter']['to'] ) ? '&amp;filter[from]='.$_GET['filter']['from'].'&amp;filter[to]='.$_GET['filter']['to'] : ''; ?>" onclick="return confirm('Are you sure?')" class="btn btn-md btn-primary">
                                            Paid all (&#8369;&nbsp;<?php echo number_format( $totalCredit, 2 ); ?>)
                                            </a>
                                        </span>
                                        <?php endif; ?>
                                        
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
