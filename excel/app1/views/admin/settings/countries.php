<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
    <ul class="breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url('admin/'); ?>">Home</a>
        </li>
        <li>Settings</li>
        <li class="active">Countries</li>
    </ul>
</div>
<!-- /Page Breadcrumb -->

<!-- Page Header -->
<div class="page-header position-relative">
    <div class="header-title">
        <h1>
            Countries
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
	<?php $app->renderAlerts(); ?> 

    <div class="row">
        <div class="col-xs-12 col-md-9">
            <div class="well with-header with-footer">
                <div class="header bordered-green">
                    Countries sorted by name
                    <a href="#" role="button" class="btn btn-xs btn-default pull-right" data-toggle="modal" data-target="#modalAddCountry"><i class="fa fa-plus"></i> Add country</a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
						
							  <th></th>
                            <th width="5%">
                               #
                            </th>
                            <th>
                                Country
                            </th>
                            <th>
                                Abbr.
                            </th>
                            <th>
                                Code
                            </th>
                            <th>
                                Created
                            </th>
                          
                        </tr>
                    </thead>
                    <tbody>
                        <?php $ctr = $paginationCounter['from']; ?>
						<?php foreach ($countries as $country): ?>
                        <tr>
						
							   <td width="10%">
                                <a href="#" class="btn btn-default btn-xs black btn-edit-country" data-toggle="modal" data-target="#modalEditCountry" data-country="<?php echo (int) $country['country_id']; ?>"><i class="fa fa-pencil"></i> Edit</a>
                                <a href="#" class="btn-delete-country" data-country="<?php echo (int) $country['country_id']; ?>"><i class="fa fa-bin"></i> Remove</a>
                            </td>
							
                            <td>
                                <?php echo str_pad( $ctr, 3, '0', STR_PAD_LEFT); ?>
                            </td>
                            <td>
                                <?php echo $country['country_name']; ?>
                            </td>
                            <td>
                                <?php echo $country['country_abbr']; ?>
                            </td>
                            <td>
                                <?php echo $country['country_code']; ?>
                            </td>
                            <td>
                                <small><?php echo date( 'M-d-Y h:ia', strtotime( $country['country_created'] ) ); ?></small>
                            </td>
                         
                        </tr>
                        <?php $ctr++; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <div class="footer">
                    <span class="">
                       Showing <?php echo $paginationCounter['from']; ?> to <?php echo $paginationCounter['to']; ?> of <?php echo $paginationCounter['total-records']; ?> entries
                    </span>
                    <?php echo $pagination; ?>
                </div> 

            </div>
        </div>
        
    </div>
    
</div>
<!-- /Page Body --> 