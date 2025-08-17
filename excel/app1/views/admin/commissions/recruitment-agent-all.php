<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
    <ul class="breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url('admin/'); ?>">Home</a>
        </li>
        <li>Commissions</li>
        <li class="active"><?php echo $agent['agent_first'].' '.$agent['agent_last']; ?></li>
    </ul>
</div>
<!-- /Page Breadcrumb -->
<!-- Page Header -->
<div class="page-header position-relative">
    <div class="header-title">
        <h1>
            <?php echo $agent['agent_first'].' '.$agent['agent_last']; ?>
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
                                    	<thead>
                                        	<tr>
                                                <th>Ref#</th>
                                                <th>Date deployed</th>
                                            	<th>Applicants</th>
                                                <th>Job Offer</th>
                                                <th>Commission</th>
                                                <th>&nbsp;</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $hasUnPaid = false; ?>
                                            <?php if ( count( $details ) == 0 ): ?>
                                            <tr>
                                                <td colspan="4" align="center">-- No entries --</td>
                                            </tr>
                                            <?php else: ?>
                                        	<?php $totalCommission = 0; ?>
                                        	<?php foreach ( $details as $applicantId => $worker ): ?>
                                        	<?php $totalCommission += $worker['commission_amount']; ?>
                                        	<tr>
                                                <td>SOA-<?php echo str_pad( $worker['bill_id'], 6, '0', STR_PAD_LEFT ); ?></td>
                                                <td><?php echo fdate( 'Y-m-d', $worker['commission_created'] ); ?></td>
                                        		<td><?php echo $worker['applicant_first'].' '.$worker['applicant_last']; ?></td>
                                        		<td><?php echo $worker['job_name']; ?></td>
                                                <td>
                                        			<p class="fee-amount">&#8369;&nbsp;<span class="text-danger"><?php echo number_format( $worker['commission_amount'], 2 ); ?></span></p>
                                        		</td>
                                        		<td>
                                        			<?php if ( ! $worker['commission_status'] ): ?>
                                                    <?php $hasUnPaid = true; ?>
                                        			<a href="<?php echo site_url( 'admin/commissions/recruitment-agent-payment/'.$worker['commission_id'] ); ?>" class="btn btn-xs btn-default"><strong>PAID</strong></a>
                                        			<?php else: ?>
                                        			<span class="text-success"><i class="fa fa-check"></i> Paid</span>
                                        			<?php endif; ?>
                                        		</td>
                                        	</tr>
                                        	<?php endforeach; ?>
                                            
                                            <tr>
                                            	<td align="center" colspan="3">&nbsp;</td>
                                                <td>
                                                	<p class="fee-text" align="right">Total:</p>                                                    
                                                </td>
                                                <td>
                                                	<p class="fee-amount"><strong>&#8369;&nbsp;<span class="text-danger"><?php echo number_format( $totalCommission, 2 ); ?></span></strong></p>
                                                </td>
                                                <td>&nbsp;</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody> 
                                    </table>
                                    </div>

                                    <hr class="wide">
                                    <div>
                                        <?php if ( $hasUnPaid ): ?>
                                        <span class="pull-right">
                                            <a href="<?php echo site_url( 'admin/commissions/recruitment-agent-payment-all/'.$agent['agent_id'] ); ?><?php echo isset( $_GET['filter']['from'], $_GET['filter']['to'] ) ? '?filter[from]='.$_GET['filter']['from'].'&amp;filter[to]='.$_GET['filter']['to'] : ''; ?>" class="btn btn-md btn-primary">
                                            Paid all
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