<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
    <ul class="breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url('admin/'); ?>">Home</a>
        </li>
        <li class="">Commission</li>
        <li class="active">Marketing Agencies</li>
    </ul>
</div>
<!-- /Page Breadcrumb -->
<!-- Page Header -->
<div class="page-header position-relative">
    <div class="header-title">
        <h1>
            &nbsp;
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

                    <div class="widget-buttons">

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
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-applicants">
                            <thead class="bordered-palegreen">
                                <tr>
                                    <th>Agency</th>
                                    <th>Contact person</th>
                                    <th>Total commission</th>
                                    <th>Remaining balance</th>
                                    <th>Total paid</th>
                                    <th>&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ( count( $agencies ) == 0 ): ?>
                                <tr>
                                    <td colspan="12" align="center">-- No commissions  --</td>
                                </tr>
                                <?php endif; ?>
                                <?php foreach ( $agencies as $agency ): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo site_url( 'admin/commissions/marketing-agency-all/'.$agency['agency_id'] . $queryString ); ?>" >
                                        <strong><?php echo $agency['agency_name'] ?></strong>
                                        </a>
                                    </td>
                                    <td><?php echo $agency['agency_contact_person']; ?></td>
                                    <td>
										&#8369;&nbsp;<span class="text-default"><?php echo number_format( $agency['total_commission'], 2 ); ?></span>
                                    </td>
                                    <td>
                                        &#8369;&nbsp;<span class="text-danger"><?php echo number_format( $agency['remaining'], 2 ); ?></span>
                                    </td>
                                    <td>
                                        &#8369;&nbsp;<span class="text-info"><?php echo number_format( $agency['total_paid'], 2 ); ?></span>
                                    </td>
                                    <td>
                                    	<a href="<?php echo site_url( 'admin/commissions/marketing-agency-all/'.$agency['agency_id'] ); ?>" class="btn btn-xs btn-default">Open</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                            </tbody>
                        </table> 
                    </div><!-- .table-responsive -->
                    <hr>
                    <div class="footer" align="right">
                        &nbsp;
                        <div class="clearfix"></div>
                    </div> 
                </div>

            </div>
            
            
        </div>
    </div>
</div>
<!-- /Page Body -->
