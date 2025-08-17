<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
    <ul class="breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url('admin/'); ?>">Home</a>
        </li>
        <li class="">Billing</li>
        <li class="active">Employers</li>
    </ul>
</div>
<!-- /Page Breadcrumb -->
<!-- Page Header -->
<div class="page-header position-relative">
    <div class="header-title">
        <h1>
            Due to Employers
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
                    <div class="table-responsive" style="overflow:scroll; padding:10px;">
                        <table class="table table-bordered table-hover table-applicants">
                            <thead class="bordered-palegreen">
                                <tr>
                                    <th>#</th>
                                    <th>Employer #</th>
                                    <th class="graded">Agency/Employer</th>
                                    <th>Total workers</th>
                                    <th>Amount</th>
                                    <th>Remarks</th>
                                    <th>Last updated by</th>
                                    <th></th> 
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ( count( $employers ) == 0 ): ?>
                                <tr>
                                    <td colspan="12" align="center">-- No employers yet --</td>
                                </tr>
                                <?php endif; ?>
                                <?php $ctr = $paginationCounter['from']; ?>
                                <?php foreach ( $employers as $employer ): ?>
                                <tr>
                                    <td>
                                        <?php echo str_pad( $ctr, 5, '0', STR_PAD_LEFT ); ?>
                                    </td>
                                    <td class="numeric">
                                       <?php echo $employer['employer_no']; ?> 
                                    </td>
                                    <td class="numeric">
                                        <a href="<?php echo site_url( 'admin/billing/employer-soa/'.$employer['employer_id']); ?>" title="Review">
                                            <strong><?php echo $employer['employer_name']; ?></strong>
                                        </a> 
                                    </td>
                                    <td>
                                    	<?php echo number_format( $employer['workers'] ); ?>
                                    </td>
                                    <td>
                                    	<strong>&#8369; <span class="text-danger"><?php echo number_format( $employer['bill'] ); ?></span></strong>
                                    </td>
                                    <td>
                                    	<?php echo str_short( $employer['employer_remarks'], 100 ); ?>
                                    </td>
                                    <td class="numeric">
                                        <a href="#"><small><?php echo $employer['updated_by']['user_fullname']; ?></small></a>
                                    </td>
                                    <td class="numeric">
                                        <a href="<?php echo site_url( 'admin/billing/employer-soa/'.$employer['employer_id']); ?>" class="btn btn-default btn-xs">
                                            <i class="fa fa-search"></i> List statements
                                        </a>  
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
