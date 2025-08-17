<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
    <ul class="breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url('admin/'); ?>">Home</a>
        </li>
        <li>Applicants</li>
        <li class="active">For Deployment</li>
    </ul>
</div>
<!-- /Page Breadcrumb -->
<!-- Page Header -->
<div class="page-header position-relative">
    <div class="header-title">
        <h1>
            For Deployment Applicants
        </h1>
    </div>
    <!--Header Buttons-->
    <div class="header-buttons">
        <a class="sidebar-toggler" href="#">
            <i class="fa fa-arrows-h"></i>
        </a>
        <a class="refresh" id="refresh-toggler" href="<?php echo site_url( 'admin/applicants/deployed' ); ?>">
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
                        <a href="#" class="btn-show-photo btn btn-xs btn-info">Show photos</a>
                    </span>
                    <?php if ( count( $applicants ) > 0 ): ?>
                    <div class="widget-buttons">
                        <a href="#" class="btn-advanced-search">
                            <i class="fa fa-search"></i> Advanced search
                        </a>  
                    </div> 
                    <?php endif; ?>
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
                    <div class="row advanced-search <?php echo isset( $get['search'] ) ? '' : 'hide'; ?>">
                        <div class="col-sm-12">
                            <div class="form-title">
                                <strong>Advanced Search</strong>
                            </div>
                            <form method="get">
                            <div class="row">
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label>Search keyword</label>
                                        <input type="text" name="search[q]" class="form-control input-sm" placeholder="Keywords" value="<?php echo isset( $get['search']['q'] ) ? $get['search']['q'] : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label>Country:</label> 
                                        <select name="search[country]" class="form-control input-sm">
                                            <option value="0">All</option>
                                            <?php foreach ($countries as $country): ?>
                                            <option value="<?php echo $country['country_id']; ?>" <?php echo isset( $get['search']['country'] ) && $get['search']['country'] == $country['country_id'] ? 'selected' : '';  ?>><?php echo $country['country_name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label>Position</label> 
                                        <select name="search[position]" class="form-control input-sm">
                                            <option value="0">-- Select position --</option>
                                            <?php foreach ($categories as $category): ?>
                                            <?php if ( count( $category['positions'] ) == 0 ) continue; ?>
                                            <optgroup label="<?php echo $category['category_name']; ?>">
                                            <?php foreach ($category['positions'] as $position): ?>
                                            <option value="<?php echo $position['position_id']; ?>" <?php echo isset( $get['search']['position'] ) && $get['search']['position'] == $position['position_id'] ? 'selected' : '';  ?>><?php echo $position['position_name']; ?></option>
                                            <?php endforeach; ?>
                                            </optgroup>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-1">
                                    <div class="form-group">
                                        <label>Gender</label>
                                        <select name="search[gender]" class="form-control input-sm">
                                            <option value="">Any</option>
                                            <option value="Male" <?php echo isset( $get['search']['gender'] ) && $get['search']['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo isset( $get['search']['gender'] ) && $get['search']['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <label>Age bracket</label>
                                    <div class="input-group ">
                                        <input name="search[age][from]" type="text" class="form-control input-sm" value="<?php echo isset( $get['search']['age']['from'] ) ? $get['search']['age']['from'] : ''; ?>" placeholder="0">
                                        <span class="input-group-addon">&minus;</span>
                                        <input name="search[age][to]" type="text" placeholder="0" class="form-control input-sm" value="<?php echo isset( $get['search']['age']['to'] ) ? $get['search']['age']['to'] : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <label>Salary range</label>
                                    <div class="input-group ">
                                        <input name="search[salary][from]" type="text" class="form-control input-sm" value="<?php echo isset( $get['search']['salary']['from'] ) ? $get['search']['salary']['from'] : ''; ?>" placeholder="0.00">
                                        <span class="input-group-addon">&minus;</span>
                                        <input name="search[salary][to]" type="text" placeholder="0.00" class="form-control input-sm" value="<?php echo isset( $get['search']['salary']['to'] ) ? $get['search']['salary']['to'] : ''; ?>">
                                    </div>
      