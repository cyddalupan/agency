<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
    <ul class="breadcrumb">
        <li  class="active">
            <i class="fa fa-home"></i> Home
        </li>
    </ul>
</div>
<!-- /Page Breadcrumb -->

<!-- Page Header -->
<div class="page-header position-relative">
    <div class="header-title">
        <h1>Dashboard</h1>
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
    <?php $app->renderAlerts(); ?> 

    <div class="horizontal-space"></div>
    <div class="row">
        <div class="col-xs-12 col-md-12 col-lg-12">
            <div class="widget">
                <div class="widget-header ">
                    <span class="widget-caption">Statistic</span>
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
                    <div id="applicant-statistic" class="chart chart-lg table-responsive"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12"> 
            <div class="widget">
                <div class="widget-header bordered-bottom bordered-themesecondary">
                    <i class="widget-icon fa fa-tags themesecondary"></i>
                    <span class="widget-caption themesecondary">Updates Board</span>
                </div><!--Widget Header-->
                <div class="widget-body">
                    <div class="widget-main no-padding">
                        <div class="tickets-container">
                            <?php if ( count( $applicantLogs ) == 0 ): ?>
                            <p><span class="text-info"><i class="fa fa-info"></i></span> No updates</p>
                            <?php endif; ?>
                            <ul class="tickets-list">
                                <?php foreach ( $applicantLogs as $log ): ?>
                                <li class="ticket-item">
                                    <div class="row">
                                        <div class="ticket-user col-lg-7 col-sm-12">
                                            <?php if ( is_file( DIR_UPLOADS.'applicant'.DIRECTORY_SEPARATOR.$log['applicant_photo'] ) ): ?>
                                                <img src="<?php echo base_url(); ?>files/applicant/<?php echo $log['applicant_photo']; ?>" alt="<?php echo $log['applicant_name']; ?>" class="user-avatar" />
                                            <?php else: ?>
                                                <img src="<?php echo $app->getPath()['images']; ?>avatars/no-picture.jpg" class="user-avatar" alt="<?php echo $log['applicant_name']; ?>">
                                            <?php endif; ?>
                                            <span class="user-name"><?php echo $log['applicant_name']; ?></span>
                                            <?php if ( $log['log_status'] == $status['Available'] ): ?>
                                            <span class="user-at">is available.</span>
                                            <?php elseif ( $log['log_status'] == $status['Cancelled'] ): ?>
                                            <span class="user-at">was cancelled.</span>
                                            <?php else: ?>
                                            <span class="user-at"><?php echo $statusText[$log['log_status']]; ?> to</span>
                                            
                                            <span class="user-company">
                                                <a href="<?php echo site_url( 'admin/employers/preview/'.$log['employer_id'] ); ?>" class="" role="button" data-toggle="modal" data-target="#modalReports" >
                                                <?php echo $log['employer_name']; ?>
                                                </a>
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ticket-time  col-lg-3 col-sm-6 col-xs-12">
                                            <div class="divider hidden-md hidden-sm hidden-xs"></div>
                                            <i class="fa fa-clock-o"></i>
                                            <a href="javascript:;" class="timeago" title="<?php echo date( 'c', strtotime( $log['log_date'] . ' ' . date('H:i:s', strtotime($log['log_created'] ) ) ) ); ?>">
                                                <span class="time"><?php echo date( 'm.d.Y', strtotime( $log['log_date'] ) ); ?></span>
                                            </a>
                                        </div>
                                        <div class="ticket-type  col-lg-2 col-sm-6 col-xs-12">
                                            <span class="divider hidden-xs"></span>
                                            <a href="<?php echo site_url( 'admin/applicants/review/' . str_pad( $log['applicant_id'], 9, '0', STR_PAD_LEFT ).'/'.strSlug( $log['applicant_name'] ) ); ?>?ref_form=status#history" title="View profile">
                                                <span class="type"><?php echo $statusText[$log['log_status']]; ?></span>
                                            </a>
                                        </div>
                                        <?php if ( $log['log_status'] == $status['Deployed'] ): ?>
                                        <div class="ticket-state bg-palegreen">
                                            <i class="fa fa-check"></i>
                                        </div>
                                        <?php elseif ( $log['log_status'] == $status['Cancelled'] ): ?>
                                        <div class="ticket-state bg-darkorange">
                                            <i class="fa fa-times"></i>
                                        </div>
                                        <?php elseif ( $log['log_status'] == $status['For Deployment'] ): ?>
                                        <div class="ticket-state bg-yellow">
                                            <i class="fa fa-info"></i>
                                        </div>
                                        <?php else: ?>
                                        <div class="ticket-state bg-info">
                                            <i class="fa fa-check-square"></i>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- .row --> 

</div>
<!-- /Page Body -->