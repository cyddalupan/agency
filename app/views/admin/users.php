

<div ng-controller="admin-user">
    <!-- Page Breadcrumb -->
    <div class="page-breadcrumbs">
        <ul class="breadcrumb">
            <li>
                <i class="fa fa-home"></i>
                <a href="<?php echo site_url('admin/'); ?>">Home</a>
            </li>
            <li class="active">Users</li>
        </ul>
    </div>
    <!-- /Page Breadcrumb -->
    <!-- Page Header -->
    <div class="page-header position-relative">
        <div class="header-title">
            <h1>
                Users
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
            <div class="col-xs-12 col-md-12">
                <div class="widget">
                    <div class="widget-header  with-footer">
                        <span class="widget-caption">
                        	<a href="#" role="button" class="btn btn-xs btn-default" data-toggle="modal" data-target="#modalAddUser"><i class="fa fa-plus"></i> Add user</a>
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
                        <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="bordered-palegreen">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                  
                                    <th>User type</th>
                                    <th>Last login</th>
                                    <th>Date added</th>
									<th>Branch ID</th>
									<th>Source ID</th>
									
                                    <th></th> 
                                </tr>
                            </thead>
                            <tbody>
                                

                                
                                                            	
                                <?php foreach ( $users as $user ): ?>
                                <tr class="cyd_hide_me_<?php echo $user['user_id']; ?>">
                                    <td>
                                        <?php echo str_pad( $user['user_id'], 5, '0', STR_PAD_LEFT ); ?>
                                    </td>
                                    <td>
                                        <?php echo $user['user_fullname']; ?>
                                    </td>
                                    <td class="numeric">
                                       <?php echo $user['user_name']; ?>
                                    </td>
                                    
                                    <td class="numeric">
                                        <span class="label label-<?php echo $user['type_color']; ?>"><?php echo $user['type_name']; ?></span>
                                    </td>
                                    <td class="numeric">
                                        <?php if ( (int) strtotime($user['user_lastlogin']) == 0 ): ?>
                                            <span class="text-warning">Never been login</span>
                                        <?php else: ?>
                                            <a href="javascript:;" class="timeago" title="<?php echo date('c', strtotime($user['user_lastlogin'])); ?>"><?php echo date('m.d.Y h:ia', strtotime($user['user_lastlogin'])); ?></a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="numeric">
                                        <?php echo date( 'd M Y', strtotime( $user['user_created'] ) ); ?>
                                    </td>
									

									
									
<?php
if($user['branch_id']==0){$branchme="N/A";}
if($user['branch_id']==1){$branchme=" MAIN OFFICE";}
if($user['branch_id']==2){$branchme="Aklan BRANCH";}
if($user['branch_id']==3){$branchme="Calapan BRANCH";}
if($user['branch_id']==4){$branchme="Cagaya BRANCH";}
if($user['branch_id']==5){$branchme="Davao DE ORO BRANCH";}
if($user['branch_id']==6){$branchme="Leyte BRANCH";}
if($user['branch_id']==7){$branchme="Lucena BRANCH ";}
if($user['branch_id']==8){$branchme="Pangasinan BRANCH";}


?>

								
									
									
									 <td class="numeric">
                                       <?php echo $branchme; ?>
                                    </td>
									  <td class="numeric">
                                       <?php echo $user['userassign']; ?>
                                    </td>
									
									
									
                                    <td class="numeric">
                                        
                                        
                                 <?php if ( in_array( $_SESSION['admin']['user']['user_type'], [4]) ): ?>  
                                        
                                        <a href="#" role="button" class="btn btn-xs btn-default btn-settings cyd_save_user_id" data-toggle="modal" data-target="#modalSettings" data-user="<?php echo $user['user_id']; ?>"><i class="fa fa-gear"></i> Edit</a>
										
							        	<a href="https://recruitment-portal.net/1stnorthern/others/users.php?ume=<?php echo $user['user_id']; ?>" role="button" class="btn btn-xs btn-default"  data-user="<?php echo $user['user_id']; ?>" target="_blank"><i class="fa fa-gear"></i> Assign Employer</a>
                                         <a href="https://recruitment-portal.net/1stnorthern/others/users1.php?ume=<?php echo $user['user_id']; ?>" role="button" class="btn btn-xs btn-default"  data-user="<?php echo $user['user_id']; ?>" target="_blank"><i class="fa fa-gear"></i> Assign Branch</a>
                                   	     <a href="https://recruitment-portal.net/1stnorthern/others/users2.php?ume=<?php echo $user['user_id']; ?>" role="button" class="btn btn-xs btn-default"  data-user="<?php echo $user['user_id']; ?>" target="_blank"><i class="fa fa-gear"></i> Assign Accom</a>
                                   	
                                   	<?php endif; ?> 
								   </td> 
                                </tr>
                                <?php endforeach; ?>
                                
                            </tbody>
                        </table> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Page Body -->
</div>
