<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
    <ul class="breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url('admin/'); ?>">Home</a>
        </li>
        <li>Commissions</li>
        <li class="active">Employer</li>
    </ul>
</div>
<!-- /Page Breadcrumb -->
<!-- Page Header -->
<div class="page-header position-relative">
    <div class="header-title">
        <h1>
            Agents with commissions
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
                        <table class="table table-bordered ">
                            <thead class="bordered-palegreen">
                                <tr>
                                    <th>#</th>
                                    <th>ID</th>
                                    <th>Agent</th>
                                    <th>Company</th>
                                    <th>Amount Due</th>                                    
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ( count( $commissions ) == 0 ): ?>    
                            <tr>
                                <td colspan="5" align="center">-- No commissions --</td>
                            </tr>
                            <?php endif; ?>
                            <?php $ctr = $paginationCounter['from']; ?>
                            <?php foreach ( $commissions as $commission ): ?>
                            <tr>
                                <td><?php echo $ctr; ?></td>
                                <td>
                                    <?php if ( ! is_null( $commission['commission_agency'] ) ): ?>
                                    <?php echo str_pad( $commission['agency_id'], 6, '0', STR_PAD_LEFT ); ?>
                                    <?php else: ?>
                                    <?php echo str_pad( $commission['agent_id'], 6, '0', STR_PAD_LEFT ); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ( ! is_null( $commission['commission_agency'] ) ): ?>
                                    <?php echo str_pad( $commission['agency_name'], 6, '0', STR_PAD_LEFT ); ?>
                                    <?php else: ?>
                                    <?php echo str_pad( $commission['agent_first'].' '.$commission['agent_last'], 6, '0', STR_PAD_LEFT ); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $commission['employer_name']; ?></td>
								<td>
									&#8369; &nbsp;<span class="text-danger"><?php echo number_format( $commission['amount_due'], 2 ); ?></span>
								</td>
                            </tr>
                            <?php $ctr ++; ?>
                            <?php endforeach; ?>
                            </tbody>
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