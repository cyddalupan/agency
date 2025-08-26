<div>
    <!-- Page Breadcrumb -->
    <div class="page-breadcrumbs">
        <ul class="breadcrumb">
            <li>
                <i class="fa fa-home"></i>
                <a href="<?php echo site_url('admin/'); ?>">Home</a>
            </li>
            <li class="active">Applicants</li>
        </ul>
    </div>
    <!-- /Page Breadcrumb -->
    <div class="page-header position-relative">
        <div class="header-title">
            <h1>
                All Applicants
            </h1>
        </div>
        <!--Header Buttons-->
        <div class="header-buttons">
            <a class="sidebar-toggler" href="#">
                <i class="fa fa-arrows-h"></i>
            </a>
            <a class="refresh" id="refresh-toggler" href="<?php echo site_url( 'admin/applicants/all' ); ?>">
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
                            <?php if ( isset( $_GET['status'] ) && $_GET['status'] == 'Reserved' ): ?>
                                <a href="<?php echo site_url( 'admin/applicants/expired-reservations' ); ?>" class="btn btn-xs btn-danger">See all expired reservations...</a>
                            <?php else: ?>
                            <?php if( ! in_array( $_SESSION['admin']['user']['user_type'], [7]) ):  ?>
                                <a href="<?php echo site_url( 'admin/applicants/add' ); ?>" class="btn btn-xs btn-default"><i class="fa fa-plus"></i> Add Applicant</a>
                            <?php endif;?>
                            <?php endif; ?>
                            &nbsp;
                            <a href="#" class="btn-show-photo btn btn-xs btn-info">Show photos</a>
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

                        <div class="table-responsive" style="overflow:scroll; padding-bottom:20px;">
                            <table id="cyd_applicant_list" class="table table-bordered table-hover table-applicants">
                                <thead class="bordered-palegreen">
                                    <tr>
                                        <th>#</th>
                                        <th></th> 
                                        <th>#</th>
                                        <th>Date created</th>
                                        <th>Applicant #</th>
                                        <th>Name</th>
                                        <th>Gender</th>
                                        <th>Status</th>
                                        <th>FRA</th>
										<th>Sponsor</th>
                                     
                                        <th>Passport</th>
                                        <th>Preferred position</th>
                                        <th>Sub position</th>
                                        <th>Preferred country</th>
                                        <th>Medical result</th>
                                        <th>NBI</th>
                                        <th>Trade Test</th>
                                        <th>COE</th>
                                    
                                        <th>Insurance #</th>
                                        <th>Visa</th>
                                        <th>Ticket</th>
                                        <th>OEC #</th>
                                        <th>OWWA certificate</th>
                                        <th>Contract</th>
                                        <th>Remarks</th>
                                        <th>Date applied</th>
                                        <th>Civil status</th>
                                        <th>Source</th>
                                        <th>Last updated by</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach($applicants as $key => $applicant): ?>
                                    <tr>
                                        <td>
                                            <div class="checkbox">
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" class="inverted applicants-selection" name="cyd_applicant_select" value="<?php echo $applicant['applicant_id']; ?>" >
                                                        <span class="text"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url('admin/applicants/review/' . $applicant['applicant_id']); ?>" class="btn btn-default btn-xs">
                                                <i class="fa fa-search"></i> Review
                                            </a>
                                        </td>
                                        <td><?php echo $paginationCounter['from'] + $key; ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($applicant['applicant_created'])); ?></td>
                                        <td><?php echo str_pad($applicant['applicant_id'], 9, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo $applicant['applicant_first'] . ' ' . $applicant['applicant_middle'] . ' ' . $applicant['applicant_last']; ?></td>
                                        <td><?php echo $applicant['applicant_gender']; ?></td>
                                        <td>
                                            <span class="label graded <?php echo $statusColors[$applicant['applicant_status']]; ?>">
                                                <?php echo $statusText[$applicant['applicant_status']]; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $applicant['employer_name']; ?></td>
                                        <td><?php echo $applicant['sub_employer']; ?></td>
                                        <td><?php echo $applicant['passport_number']; ?></td>
                                        <td><?php echo $applicant['position_name']; ?></td>
                                        <td></td>
                                        <td><?php echo $applicant['country_name']; ?></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td><?php echo $applicant['applicant_remarks']; ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($applicant['applicant_date_applied'])); ?></td>
                                        <td><?php echo $applicant['applicant_civil_status']; ?></td>
                                        <td><?php echo $applicant['agent_first'] . ' ' . $applicant['agent_last']; ?></td>
                                        <td><?php echo $applicant['user_name']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table> 
                        <div class="clearfix"></div>
                        </div>

                        <div class="footer" align="right">
                            <?php echo $paginationHTML; ?>
                        </div><!-- .table-responsive -->
                    </div>
                </div>            
            </div>
        </div>
    </div>
    <!-- /Page Body -->
</div>