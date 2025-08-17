<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
    <ul class="breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url('admin/'); ?>">Home</a>
        </li>
        <li>Commission</li>
        <li>
            <a href="<?php echo site_url( 'admin/commissions/recruitment-agents' ); ?>">Recruitment Agents</a>
        </li>
        <li class="active"><a href="<?php echo site_url( 'admin/commissions/recruitment-agent-all/'.$agent['agent_id'] ); ?>"><?php echo $agent['agent_first'].' '.$agent['agent_last']; ?></a></li>
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
        <div class="col-lg-10 col-sm-10 col-xs-12">
           
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="widget flat radius-bordered">
                        <div class="widget-header bg-blue">
                            <span class="widget-caption">
                                <a href="<?php echo site_url( 'admin/commissions/recruitment-agent-all/'.$agent['agent_id'] . $queryString  ); ?>" class="btn btn-xs btn-default">&laquo;Back</a>
                            </span>
                        </div>
                        <div class="widget-body">
							<?php $app->renderAlerts(); ?>
                            <h2>Voucher# <span class="text-danger"><?php echo $voucher; ?></span></h2>
                            <p>&nbsp;</p>
                            <table class="table table-hover table-condensed">
                                <thead>
                                    <tr>
                                        <th>Date deployed</th>
                                        <th>Workers</th>
                                        <th>Job Offer</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ( $workers as $applicantId => $worker ): ?>
                                    <tr>
                                        <td>
                                            <?php echo fdate( 'Y-m-d', $worker['commission_created'] ); ?>
                                        </td>
                                        <td>
                                            <?php echo $worker['applicant_first'].' '.$worker['applicant_last']; ?>
                                        </td>
                                        <td>
                                            <?php echo $worker['job_name']; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <p>&nbsp;</p>
                            <form method="post" class="form" >
                            
                            <div class="row">
                                <div class="col-sm-5">

                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <div class="input-group">
                                                      <input name="payment[amount]" type="number" class="form-control" placeholder="0.00">
                                                      <span class="input-group-btn">
                                                        <button class="btn btn-primary" type="submit">Save</button>
                                                      </span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                </table>

                                </div>
                            </div>  
                            </form>

                            <div class="clearfix"></div>
                            
                        </div>
                        
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>
<!-- /Page Body -->