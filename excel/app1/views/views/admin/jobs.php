<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
    <ul class="breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url('admin/dashboard'); ?>">Home</a>
        </li>
        <li class="active">Jobs</li>
    </ul>
</div>
<!-- /Page Breadcrumb -->
<!-- Page Header -->
<div class="page-header position-relative">
    <div class="header-title">
        <h1>
            Jobs
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
                        <a href="<?php echo site_url( 'admin/jobs/add' ); ?>" class="btn btn-xs btn-default" role="button" data-toggle="modal" data-target="#modalReports" ><i class="fa fa-plus"></i> Add new job</a>
                    </span>
                    
                    <div class="widget-buttons">
                        <small>Select employer: </small>
                        <select class="form-contrl input-sm" id="filter-employer">
                            <option value="">-- All --</option>
                            <?php foreach ( $employers as $employer ): ?>
                            <option value="<?php echo $employer['employer_id']; ?>" <?php echo isset( $_GET['filter']['eid'] ) && $_GET['filter']['eid'] == $employer['employer_id'] ? 'selected' : ''; ?>><?php echo $employer['employer_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

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
                        <table class="table table-bordered table-hover table-jobs">
                            <thead class="bordered-palegreen">
                                <tr>
									  <th>&nbsp;</th>
                                    <th>#</th>
									  <th>Status</th>
                                    <th>Job offer title</th>
                                    <th>Agency/Employer</th>
                                    <th>Position</th>
                                    <th>Number of workers needed</th>
                                    <th>Remaining slot</th>
                                    <th>Gender</th>
                                    <th>Salary range</th>
                                    <th>Total revenue</th>
                                    <th>Remarks</th>
                                    <th>Posted</th>
                                  
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ( count( $jobs ) == 0 ): ?>
                                    <tr>	
                                        <td colspan="11" align="center"> -- No job offer records --</td>
                                    </tr>
                                <?php endif;?>
                                
                                <?php $ctr = $paginationCounter['from']; ?>
                                <?php $currentGroup = ''; ?>
                                <?php foreach ( $jobs as $job ): ?>
                                <?php if ( $currentGroup !== $job['employer_name'] ): ?>
                                <?php $currentGroup = $job['employer_name']; ?>
                                <tr>
                                    <th class="border-warning" colspan="12" align="center" style="text-transform:uppercase"><?php echo $job['employer_name']; ?></th>
                                </tr>
                                <?php endif; ?>
                                <tr>
								
								
								  <td class="numeric">
                                        <a class="btn btn-default btn-xs" data-toggle="modal" data-target="#modalReports" role="button" href="<?php echo site_url( 'admin/jobs/review/'.$job['job_id'] ); ?>" title="Review">
                                            <i class="fa fa-search"></i> Review
                                        </a>
                                    </td> 
								
									<td>
                                        <?php echo $job['job_sstatus']; ?>
                                    </td>
                                    <td>
                                        <?php echo str_pad( $ctr, 5, '0', STR_PAD_LEFT ); ?>
                                    </td>
                                    <td>
                                        <a class="btn-review" data-toggle="modal" data-target="#modalReports" role="button" href="<?php echo site_url( 'admin/jobs/review/'.$job['job_id'] ); ?>" title="Review">
                                            <strong><?php echo $job['job_name']; ?></strong>
                                        </a> 
                                    </td>
                                    <td style="text-transform:uppercase">
                                        <a href="<?php echo site_url( 'admin/employers/preview/'.$job['job_employer'] ); ?>" class="" role="button" data-toggle="modal" data-target="#modalReports" >
                                        <?php echo $job['employer_name']; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php echo $job['position_name']; ?>
                                    </td>
                                    <td>
                                        <?php echo number_format( $job['job_total'] ); ?>
                                    </td>
                                    <td>
                                        <?php echo number_format( $job['job_total'] - $job['job_occupied'] ); ?>
                                    </td>
                                    <td>
                                        <?php echo $job['job_gender']; ?>
                                    </td>
                                    <td>
                                        P <span class="text-danger"><?php echo number_format( $job['job_salary_from'], 2); ?></span> &minus;
                                        P <span class="text-danger"><?php echo number_format( $job['job_salary_to'], 2); ?></span>
                                    </td>
                                     <td>
                                        P <span class="text-danger"><?php echo number_format( $job['job_revenue'], 2); ?></span>
                                    </td>
                                    <td>
                                        <?php echo $job['job_remarks']; ?>
                                    </td>
                                    <td>
                                        <?php echo date( 'd M Y', strtotime( $job['job_created'] ) ); ?>
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

            </div> <!-- .widget -->
            
        </div>
    </div>
</div>
<!-- /Page Body -->
