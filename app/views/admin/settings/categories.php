<!-- Page Breadcrumb -->
<div class="page-breadcrumbs">
    <ul class="breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url('admin/'); ?>">Home</a>
        </li>
        <li>Settings</li>
        <li class="active">Categories</li>
    </ul>
</div>
<!-- /Page Breadcrumb -->

<!-- Page Header -->
<div class="page-header position-relative">
    <div class="header-title">
        <h1>
            Categories
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
        <div class="col-xs-12 col-md-8">
            <div class="well with-header with-footer">
                <div class="header bordered-green">
                    Categories sorted by name
                    <a href="#" role="button" class="btn btn-xs btn-default pull-right" data-toggle="modal" data-target="#modalAddCategory"><i class="fa fa-plus"></i> Add category</a>
                </div>
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
							
							 <th></th>
						
                            <th width="5%">
                               #
                            </th>
                            <th>
                                Photo
                            </th>
                            <th>
                                Category
                            </th>
                            <th width="30%">
                                Positions
                            </th>
                            <th>
                                Created
                            </th>
                           
                        </tr>
                    </thead>
                    <tbody>
						<?php foreach ($categories as $category): ?>
						<?php $ctr = !isset( $ctr ) ? 1 : $ctr + 1; ?>
                        <tr>
							   <td align="center" width="10%">
                                <a href="#" class="btn btn-default btn-xs black btn-edit-category" data-toggle="modal" data-target="#modalEditCategory" data-category="<?php echo (int) $category['category_id']; ?>"><i class="fa fa-pencil"></i> Edit</a>
                                <br><br>
                                <a href="<?php echo base_url("admin/settings/delete_cateogry/".$category['category_id']); ?>">Delete category</a>
                            </td>
						
                            <td>
                                <?php echo str_pad( $ctr, 3, '0', STR_PAD_LEFT); ?>
                            </td>
                            <td>
                                <img width="70" src="<?php echo $app->getPath()['images']; ?>nophoto.gif" class="img-responsive img-rounded" alt="<?php echo $category['category_name']; ?>" />
                            </td>
                            <td>
                                <?php echo $category['category_name']; ?>
                            </td>
                            <td class="numeric" align="center">
                                <a href="#" role="button" class="btn-category-positions" data-toggle="modal" data-target="#modalListPositions" data-category="<?php echo (int) $category['category_id']; ?>">View all (<?php echo number_format( $category['positions'] ); ?>)</a><br>
                                <a href="#" class="btn-add-position" data-category="<?php echo $category['category_id']; ?>" data-toggle="modal" data-target="#modalAddPosition"><i class="fa fa-plus"></i> Add position</a><br>
                            </td>
                            <td>
                                <small><?php echo $category['category_created']; ?></small>
                            </td>
                         
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="footer">
                    Total of <?php echo number_format( count( $categories ) ); ?> records.
                </div>

            </div>
        </div>
        
    </div>
    
</div>
<!-- /Page Body --> 