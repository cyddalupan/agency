<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
    <ul class="breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url('admin/'); ?>">Home</a>
        </li>
        <li>Applicants</li>
        <li class="active">Expired Visa</li>
    </ul>
</div>
<!-- /Page Breadcrumb -->
<!-- Page Header -->
<div class="page-header position-relative">
    <div class="header-title">
        <h1>
            Applicants with Expired Visa
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
                <form method="post">
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
                            <?php if ( count( $applicants ) == 0 ): ?>
                            <tr>
                                <td>No entries</td>
                            </tr>
                            <?php else: ?>
                            <thead class="  bordered-palegreen">
                                <tr>
                                    <th>#</th>
                                    <th>Applicant</th>
                                    <th>Employer</th>
                                    <th>Visa date</th>
                                    <th>Date release</th>
                                    <th>Date applied</th>
                                    <th>Source</th>
                                    <th>Date expired</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $ctr = $paginationCounter['from']; ?>
                                <?php foreach ( $applicants as $applicant ): ?>
                                <tr>
                                    <td>
                                        <?php echo $ctr; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo $applicant['applicant_name']; ?></strong>
                                    </td>
                                    <td><?php echo $applicant['employer_name']; ?></td>
                                    <td class="numeric">
                                        <?php echo $applicant['requirement_visa_date']; ?>
                                    </td>
                                    <td >
                                        <?php echo date( 'd-M-Y', strtotime( $applicant['requirement_visa_release_date'] ) ); ?>
                                    </td>
                                    <td>
                                        <?php echo date( 'd-M-Y', strtotime( $applicant['applicant_date_applied'] ) ); ?>
                                    </td>
                                    <td class="numeric">
                                    	<?php if ( $applicant['applicant_source'] == 0 ): ?>
                                        <span class="text-danger">DIRECT HIRE</span>
                                        <?php else: ?>
                                        
                                        <?php endif; ?>
                                    </td>
                                    <td class="numeric danger">
                                        <?php echo date( 'd M Y', strtotime( $applicant['requirement_visa_expiration'] ) ); ?>
                                    </td>
                                </tr>
                                <?php $ctr++; ?>
                                <?php endforeach; ?>
                                
                            </tbody>
                            <?php endif; ?>
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
                </form>
            </div>
        </div>
    </div>
</div>
<!-- /Page Body -->