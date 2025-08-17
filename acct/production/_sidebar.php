<?php 
/*echo'<p style="margin-top:-10px;display:none">';
$con = mysql_connect("localhost","stepupma_cyd","d2R1*-yR46");
mysql_select_db("stepupma_dev", $con);
echo'<p>';



$pass =  mysql_query("SELECT count(applicant_id) as alls,applicant.* FROM applicant
where 
applicant_status=15");
$pass1 = mysql_fetch_array($pass);*/
$mylink="https://recruitment-ph.com/system/";
?>


<div class="page-sidebar <?php echo $app->getSideBarClass(); ?>" id="sidebar">
    
    
   <div style="clear:both;height:100px"></div> 
    
    
<!-- Page Sidebar Header-->
<div class="sidebar-header-wrapper">

<?php if ( !in_array( $_SESSION['admin']['user']['user_type'], [20]) ): ?> 
<form method="get" action="<?php echo site_url(); ?>admin/applicants/quick_search">
<input type="text" name="keyword" class="searchinput"  placeholder="Search Applicants, Employers, Users" required/>
</form>
<?php endif; ?>

<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [13111]) ): ?> 
<form method="post" action="<?php echo site_url(); ?>admin/dashboard">
<input type="text" name="keyword" class="searchinput"  placeholder="Search for handler" required />
<button type="submit" class="btn btn-info"  name="Search" >Search </button>

</form>
<?php endif; ?>






<i class="searchicon fa fa-search"></i>
<div class="searchhelper">Search Applicants, Employers, Users</div>
<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [1, 3,4, 6, 7,11]) ): ?>  
<?php endif; ?>
</div>
<!-- /Page Sidebar Header -->

<!-- Sidebar Menu -->
<ul class="nav sidebar-menu">
<!--Dashboard-->
<li class="<?php echo controllerActive('dashboard'); ?>">
<a href="<?php href('admin/dashboard'); ?>">
<i class="menu-icon fa fa-home"></i>
<span class="menu-text" > Dashboard </span>
</a>
</li>






<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [17]) ): ?> 


<li>
<a href="<?php href( 'admin/applicants/add' ); ?>">
<span class="menu-text btn btn-default btn-xs"><i class="fa fa-plus"></i> Add Applicant</span>
</a>
</li>

<li>
<a href="<?=$mylink?>others/branch.php?branch=<?=$_SESSION['admin']['user']['branch_id'];?>" target="blank" title="For Review">
	<i class="menu-icon fa fa-file-image-o"></i>
<span class="menu-text">My Applicant<small style="font-size:10px;color:Red">Search Applicant</small></span>
</a>
</li>

<?php endif; ?>





<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [4,2,6]) ): ?> 
<li>
<a  href="<?=$mylink?>others/dashboard.php?user=<?=$_SESSION['admin']['user']['user_id']?>" target="_blank" title="New Media">
<i class="menu-icon fa fa-list"></i>
Applicant Reports <span class="menu-text btn btn-danger btn-xs"> Recent</span>
</a>
</li>






		
<?php endif; ?>



<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [19]) ): ?> 
<li>
<a  href="<?=$mylink?>others/generalprocessing.php?user=<?=$_SESSION['admin']['user']['user_id']?>" target="_blank" title="New Media">
<i class="menu-icon fa fa-list"></i>

Applicant Reports <span class="menu-text btn btn-danger btn-xs"> New</span>

</a>
</li>		
<?php endif; ?>


<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [13]) ): ?> 



<li class="<?php echo controllerActive('dashboard'); ?>">
<a href="<?=$mylink?>others/view_coordinator.php?user=<?=$_SESSION['admin']['user']['user_id']?>"  target="_blank" >
<i class="menu-icon fa fa-folder"></i>
  <span class="menu-text btn btn-danger btn-xs"> My Applicant</span>
</a>
</li>

<li class="<?php echo controllerActive('dashboard'); ?>">
<a href="https://e-recruitmentph.com/pjv/cases/login.php?user=<?=$_SESSION['admin']['user']['user_id']?>"  target="_blank" >
<i class="menu-icon fa fa-list"></i>
  <span class="menu-text btn btn-success btn-xs"> Complaint</span>
</a>
</li>

<?php endif; ?>





<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [20]) ): ?> 

<li class="<?php echo controllerActive('dashboard'); ?>">
<a href="<?php href( 'admin/applicants/add' ); ?>">
<span class="menu-text btn btn-default btn-xs"><i class="fa fa-plus"></i> Add Applicant</span>
</a>
</li>
<li class="<?php echo controllerActive('dashboard'); ?>">
<a href="<?=$mylink?>others/view_branch.php?imy=<?=$_SESSION['admin']['user']['branch_id']?>"  target="_blank">
<i class="menu-icon fa fa-file"></i>
<span class="menu-text btn btn-info btn-xs"> Branch Applicants</span>
</a>
</li>
<?php endif; ?>



<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [18]) ): ?> 
<li class="<?php echo controllerActive('dashboard'); ?>">
<a href="<?php href( 'admin/applicants/add' ); ?>">
<span class="menu-text btn btn-default btn-xs"><i class="fa fa-plus"></i> Add Applicant</span>
</a>
</li>
<li class="<?php echo controllerActive('dashboard'); ?>">
<a href="<?=$mylink?>others/medical_view.php"  target="_blank">
<i class="menu-icon fa fa-file"></i>
<span class="menu-text btn btn-info btn-xs"> Medical view</span>
</a>
</li>

<li class="<?php echo controllerActive('dashboard'); ?>">
<a href="<?=$mylink?>others/tesda_view.php"  target="_blank">
<i class="menu-icon fa fa-folder"></i>
<span class="menu-text btn btn-info btn-xs"> Tesda view</span>
</a>
</li>

<?php endif; ?>



<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [3, 6, 7,11]) ): ?> 








<li style="display:none">
<a href="" ng-click="redirect('applicants','For Review2');skill = 'all';" title="For Review">
	<i class="menu-icon fa fa-graduation-cap"></i>
<span class="menu-text"> 
Encode Branch
<span ng-show="{{notifencodebranch}}" class="badge bg-primary">{{notifencodebranch}}</span>
</span>
</a>
</li>



<?php endif; ?>

<?php if($_SESSION["settings"]['has_case'] == 'yes'){ ?>
<li class="<?php echo controllerActive('dashboard'); ?>">
<a href="<?php echo apiLoginLink(); ?>">
<i class="menu-icon fa fa-exclamation-circle"><small class="badge pull-right bg-danger hit-count">~</small></i>
<span class="menu-text"> Hit Applicants</span>
</a>
</li>
<?php }//if show casemanagement ?>

<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [1, 3,4, 6, 7,9,10,11,12]) ): ?>
<!-- Applicants -->
<li class="<?php echo controllerActive('applicants', false); ?>">
<a href="#" class="menu-dropdown">
<i class="menu-icon fa fa-bookmark"></i>
<span class="menu-text"> Applicants </span>
<i class="menu-expand"></i>
</a>

<ul class="submenu">

<li class="<?php echo subControllerActive('applicants/add'); ?>">
<a href="<?php href( 'admin/applicants/add' ); ?>">
<span class="menu-text btn btn-default btn-xs"><i class="fa fa-plus"></i> Add Applicant</span>
</a>
</li>

<li style="display:none">
<a href="" ng-click="redirect('applicants');skill = 'all'">
	<?php if(in_array( $_SESSION['admin']['user']['user_type'], [11,12] )): ?>
	<span class="menu-text">Applicants</span>
	<?php else: ?>
	<span class="menu-text">All Applicants</span>
	<?php endif; ?>
</a>
</li>
</ul>
</li>
<?php endif; ?>






















<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [1, 3,4, 6, 7,11,12]) ): ?>
<!-- Applicants Households -->
<li class="<?php echo controllerActive('applicants', true); ?>" style="display:none">
<a href="#" class="menu-dropdown">
<i class="menu-icon fa fa-bookmark"></i>
<span class="menu-text"> Applicants Households</span>
<i class="menu-expand"></i>
</a>

<ul class="submenu">
<?php if ( ! in_array( $_SESSION['admin']['user']['user_type'], [6, 7]) ): ?>
<li class="<?php echo subControllerActive('applicants/add'); ?>">
<a href="<?php href( 'admin/applicants/add' ); ?>">
<span class="menu-text btn btn-default btn-xs"><i class="fa fa-plus"></i> Add Applicant</span>
</a>
</li>
<?php endif; ?>

<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [1,6, 7, 3, 4,9,10,11,12]) ): ?>
<li class="<?php echo subControllerActive('applicants/all'); ?>">
<a href="" ng-click="redirect('applicants');skill = 'Household'">
<span class="menu-text">All</span>
</a>
</li>
<?php if(!in_array( $_SESSION['admin']['user']['user_type'], [11,12] )): ?>
<li class="<?php echo subControllerActive('applicants/all?status=Available'); ?>">
<a href="" ng-click="redirect('applicants','Available');skill = 'Household'">
<span class="menu-text">Available</span> 
</a>
</li>
<li class="<?php echo subControllerActive('applicants/all?status=Reserved'); ?>">
<a href="" ng-click="redirect('applicants','Reserved');skill = 'Household'">
<span class="menu-text">Reserved</span> 
</a>
</li>
<li class="<?php echo subControllerActive('applicants/selected'); ?>">
<a href="" ng-click="redirect('applicants','Selected');skill = 'Household'">
<span class="menu-text"> Selected</span>
</a>
</li>
<li class="<?php echo subControllerActive('applicants/for-deployment'); ?>">
<a href="" ng-click="redirect('applicants','For Deployment');skill = 'Household'">
<span class="menu-text"> For Deployment</span>
</a>
</li>
<li class="<?php echo subControllerActive('applicants/deployed'); ?>">
<a href="" ng-click="redirect('applicants','Deployed');skill = 'Household'">
<span class="menu-text"> Deployed</span>
</a>
</li>
<?php endif; ?>
<?php endif; ?>
</ul>
</li>
<?php endif; ?>


<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [1, 3,4, 6, 7,11]) ): ?>
<!-- Applicants Skilled -->
<li class="<?php echo controllerActive('applicants', true); ?>"  style="display:none">
<a href="#" class="menu-dropdown">
<i class="menu-icon fa fa-bookmark"></i>
<span class="menu-text"> Applicants Skilled</span>
<i class="menu-expand"></i>
</a>

<ul class="submenu">
<?php if ( ! in_array( $_SESSION['admin']['user']['user_type'], [6, 7]) ): ?>
<li class="<?php echo subControllerActive('applicants/add'); ?>">
<a href="<?php href( 'admin/applicants/add' ); ?>">
<span class="menu-text btn btn-default btn-xs"><i class="fa fa-plus"></i> Add Applicant</span>
</a>
</li>
<?php endif; ?>

<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [1,6, 7, 3, 4,9,10,11]) ): ?>
<li class="<?php echo subControllerActive('applicants/all'); ?>">
<a href="" ng-click="redirect('applicants');skill = 'Skilled'">
<span class="menu-text">All</span>
</a>
</li>
<?php if(!in_array( $_SESSION['admin']['user']['user_type'], [11,12] )): ?>
<li class="<?php echo subControllerActive('applicants/all?status=Available'); ?>">
<a href="" ng-click="redirect('applicants','Available');skill = 'Skilled'">
<span class="menu-text">Available</span> 
</a>
</li>
<li class="<?php echo subControllerActive('applicants/all?status=Reserved'); ?>">
<a href="" ng-click="redirect('applicants','Reserved');skill = 'Skilled'">
<span class="menu-text">Reserved</span> 
</a>
</li>
<li class="<?php echo subControllerActive('applicants/selected'); ?>">
<a href="" ng-click="redirect('applicants','Selected');skill = 'Skilled'">
<span class="menu-text"> Selected</span>
</a>
</li>
<li class="<?php echo subControllerActive('applicants/for-deployment'); ?>">
<a href="" ng-click="redirect('applicants','For Deployment');skill = 'Skilled'">
<span class="menu-text"> For Deployment</span>
</a>
</li>
<li class="<?php echo subControllerActive('applicants/deployed'); ?>">
<a href="" ng-click="redirect('applicants','Deployed');skill = 'Skilled'">
<span class="menu-text"> Deployed</span>
</a>
</li>
<?php endif; ?>
<?php endif; ?>
</ul>
</li>
<?php endif; ?>



<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [1, 3,4, 6, 7,11]) ): ?>
<!-- Applicants Professional -->
<li class="<?php echo controllerActive('applicants', true); ?>" style="display:none">
<a href="#" class="menu-dropdown">
<i class="menu-icon fa fa-bookmark"></i>
<span class="menu-text"> Applicants Professional</span>
<i class="menu-expand"></i>
</a>

<ul class="submenu">
<?php if ( ! in_array( $_SESSION['admin']['user']['user_type'], [6, 7]) ): ?>
<li class="<?php echo subControllerActive('applicants/add'); ?>">
<a href="<?php href( 'admin/applicants/add' ); ?>">
<span class="menu-text btn btn-default btn-xs"><i class="fa fa-plus"></i> Add Applicant</span>
</a>
</li>
<?php endif; ?>

<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [1,6, 7, 3, 4,9,10,11,12]) ): ?>
<li class="<?php echo subControllerActive('applicants/all'); ?>">
<a href="" ng-click="redirect('applicants');skill = 'Pro'">
<span class="menu-text">All</span>
</a>
</li>
<?php if(!in_array( $_SESSION['admin']['user']['user_type'], [11,12] )): ?>
<li class="<?php echo subControllerActive('applicants/all?status=Available'); ?>">
<a href="" ng-click="redirect('applicants','Available');skill = 'Pro'">
<span class="menu-text">Available</span> 
</a>
</li>
<li class="<?php echo subControllerActive('applicants/all?status=Reserved'); ?>">
<a href="" ng-click="redirect('applicants','Reserved');skill = 'Pro'">
<span class="menu-text">Reserved</span> 
</a>
</li>
<li class="<?php echo subControllerActive('applicants/selected'); ?>">
<a href="" ng-click="redirect('applicants','Selected');skill = 'Pro'">
<span class="menu-text"> Selected</span>
</a>
</li>
<li class="<?php echo subControllerActive('applicants/for-deployment'); ?>">
<a href="" ng-click="redirect('applicants','For Deployment');skill = 'Pro'">
<span class="menu-text"> For Deployment</span>
</a>
</li>
<li class="<?php echo subControllerActive('applicants/deployed'); ?>">
<a href="" ng-click="redirect('applicants','Deployed');skill = 'Pro'">
<span class="menu-text"> Deployed</span>
</a>
</li>
<?php endif; ?>
<?php endif; ?>
</ul>
</li>
<?php endif; ?>

<!-- Employers -->
<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [3,4]) ): ?>
<li class="<?php echo controllerActive('employers', true); ?>">
<a href="#" class="menu-dropdown">
<i class="menu-icon fa fa-briefcase"></i>
<span class="menu-text"> FRA </span>
<i class="menu-expand"></i>
</a>

<ul class="submenu">
<li class="<?php echo subControllerActive('employers/add'); ?>">
<a href="<?php href( 'admin/employers/add' ); ?>" role="button" data-toggle="modal" data-target="#modalReports">
<span class="menu-text btn btn-default btn-xs"><i class="fa fa-plus"></i> Add FRA</span>
</a>
</li>
<li class="<?php echo subControllerActive('employers/all'); ?>">
<a href="<?php href( 'admin/employers/all' ); ?>">
<span class="menu-text">All</span>
</a>
</li>
</ul>
</li>


<!-- Agencies / Agents -->

<li class="<?php echo controllerActive('agencies', true); ?>">
<a href="#" class="menu-dropdown">
<i class="menu-icon fa fa-language"></i>
<span class="menu-text"> Agents / Source </span>
<i class="menu-expand"></i>
</a>
<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [3,4,6,2,8]) ): ?>
<ul class="submenu">
<li class="<?php echo subControllerActive('agencies/marketing-agencies'); ?>" style="display:none">
<a href="<?php href( 'admin/agencies/marketing-agencies' ); ?>">
<span class="menu-text">Marketing Agencies</span>
</a>
</li>
<li class="<?php echo subControllerActive('agencies/marketing-agents'); ?>" style="display:none">
<a href="<?php href( 'admin/agencies/marketing-agents' ); ?>">
<span class="menu-text">Marketing Agents</span>
</a>
</li>
<li class="<?php echo subControllerActive('agencies/recruitment-agents'); ?>">
<a href="<?php href( 'admin/agencies/recruitment-agents' ); ?>">
<span class="menu-text">Recruitment Agents</span>
</a>
</li>
</ul>
</li>
<?php endif; ?>
<?php endif; ?>

<!-- Commission -->
<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [2, 8, 4]) ): ?>
<li class="<?php echo controllerActive('commissions', true); ?>" style="display:none">
<a href="#" class="menu-dropdown">
<i class="menu-icon fa fa-cutlery"></i>
<span class="menu-text"> Commissions</span>
<i class="menu-expand"></i>
</a>

<ul class="submenu">
<li class="<?php echo subControllerActive('commissions/transactions'); ?>">
<a href="<?php href( 'admin/commissions/transactions' ); ?>">
<span class="menu-text">All transactions</span>
</a>
</li>
<li class="<?php echo subControllerActive('commissions/marketing-agencies'); ?>">
<a href="<?php href( 'admin/commissions/marketing-agencies' ); ?>">
<span class="menu-text">Marketing Agency</span>
</a>
</li>
<li class="<?php echo subControllerActive('commissions/marketing-agents'); ?>">
<a href="<?php href( 'admin/commissions/marketing-agents' ); ?>">
<span class="menu-text">Marketing Agents</span>
</a>
</li>
<li class="<?php echo subControllerActive('commissions/recruitment-agents'); ?>">
<a href="<?php href( 'admin/commissions/recruitment-agents' ); ?>">
<span class="menu-text">Recruitment Agents</span>
</a>
</li>
</ul>
</li> 

<!-- Billing -->
<?php if ( ! in_array( $_SESSION['admin']['user']['user_type'], [3]) ): ?>
<li class="<?php echo controllerActive('billing', true); ?>" style="display:none">
<a href="#" class="menu-dropdown">
<i class="menu-icon fa fa-dollar"></i>
<span class="menu-text"> Billing</span>
<i class="menu-expand"></i>
</a>

<ul class="submenu">
<li class="<?php echo subControllerActive('billing/transactions'); ?>">
<a href="<?php href( 'admin/billing/transactions' ); ?>">
<span class="menu-text">All transactions</span>
</a>
</li>
<li class="<?php echo subControllerActive('billing/employers'); ?>">
<a href="<?php href( 'admin/billing/employers' ); ?>">
<span class="menu-text">Due to Employer</span>
</a>
</li>
<li class="<?php echo subControllerActive('billing/workers'); ?>">
<a href="<?php href( 'admin/billing/workers' ); ?>">
<span class="menu-text">Due to Worker</span>
</a>
</li>
</ul>
</li>
<?php endif; ?>      
<?php endif; ?>      

<!-- Job Offers -->

<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [3,4]) ): ?>
<li class="<?php echo controllerActive('jobs', true); ?>">
<a href="#" class="menu-dropdown">
<i class="menu-icon fa fa-file-o"></i>
<span class="menu-text"> Job Orders </span>
<i class="menu-expand"></i>
</a>

<ul class="submenu">
<li class="<?php echo subControllerActive('jobs/add'); ?>">
<a class="btn-add-job" href="<?php href( 'admin/jobs/add' ); ?>" role="button" data-toggle="modal" data-target="#modalReports">
<span class="menu-text btn btn-default btn-xs"><i class="fa fa-plus"></i> Add Job Offer</span>
</a>
</li>
<li class="<?php echo subControllerActive('jobs/all'); ?>">
<a href="<?php href( 'admin/jobs/all' ); ?>">
<span class="menu-text">All</span>
</a>
</li>
</ul> 
</li>
<?php endif; ?>

<?php if($_SESSION["settings"]['withTraining'] == 'yes'): ?>
<li class="<?php echo controllerActive('applicant-reports', true); ?>" style="display:none">
<a href="<?php echo site_url( 'admin/reports/training-reports-modal' ); ?>" role="button" class="btn-reports" data-toggle="modal" data-target="#modalReports">
<i class="menu-icon fa fa-graduation-cap"></i>
<span class="menu-text"> Training Reports </span>
</a>
</li>
<?php endif; ?>

<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [1111111110]) ): ?>
<!-- Applicant Reports -->
<li class="<?php echo controllerActive('applicant-reports', true); ?>">
<a href="#" class="menu-dropdown">
<i class="menu-icon fa fa-file-text-o"></i>
<span class="menu-text"> Applicant Reports </span>

<i class="menu-expand"></i>
</a>

<ul class="submenu">
<li class="<?php echo subControllerActive('reports/applicants'); ?>">
<a href="<?php echo site_url( 'admin/reports/applicants-search' ); ?>" role="button" class="btn-reports" data-toggle="modal" data-target="#modalReports">
<span class="menu-text"> Selected & Deployed </span>
</a>
</li>


<li class="<?php echo subControllerActive('reports/applicants-status'); ?>">
<a href="<?php echo site_url( 'admin/reports/applicants-status-search' ); ?>" role="button" class="btn-reports" data-toggle="modal" data-target="#modalReports">
<span class="menu-text"> Applicant Status Report</span>
</a>
</li>



<li class="<?php echo subControllerActive('reports/applicants-status'); ?>">
<a href="<?php echo site_url( 'admin/reports/summary_reports_modal' ); ?>" role="button" class="btn-reports" data-toggle="modal" data-target="#modalReports">
<span class="menu-text"> Summary Reports</span>
</a>
</li>
<li class="<?php echo subControllerActive('reports/applicants-status'); ?>">
<a href="<?php echo site_url( 'admin/reports/summary_applied_online_modal' ); ?>" role="button" class="btn-reports" data-toggle="modal" data-target="#modalReports">
<span class="menu-text"> Summary Applied </span>
</a>
</li>
<li class="<?php echo subControllerActive('reports/employers'); ?>"  style="display:none">
<a href="<?php echo site_url( 'admin/reports/employers-search' ); ?>" role="button" class="btn-reports" data-toggle="modal" data-target="#modalReports">
<span class="menu-text"> Employers </span>
</a>
</li>
<li style="display:none">
<a href="#" class="menu-dropdown">
<span class="menu-text">
Marketing Agencies
</span>
<i class="menu-expand"></i>
</a>

<ul class="submenu">
<li>
<a href="<?php echo site_url( 'admin/reports/marketing-agencies-employers-search' ); ?>" role="button" class="btn-reports" data-toggle="modal" data-target="#modalReports">
	<span class="menu-text">Employers</span>
</a>
</li>
<li>
<a href="<?php echo site_url( 'admin/reports/marketing-agencies-deployed-search' ); ?>" role="button" class="btn-reports" data-toggle="modal" data-target="#modalReports">
	<span class="menu-text">Deployed Applicants</span>
</a>
</li>
<li>
<a href="<?php echo site_url( 'admin/reports/marketing-agencies-selected-search' ); ?>" role="button" class="btn-reports" data-toggle="modal" data-target="#modalReports">
	<span class="menu-text">Selected Applicants</span>
</a>
</li>
</ul>
</li>
<li>
<a href="#" class="menu-dropdown" style="display:none">
<span class="menu-text">
Marketing Agents
</span>
<i class="menu-expand"></i>
</a>

<ul class="submenu">
<li>
<a href="<?php echo site_url( 'admin/reports/marketing-agents-employers-search' ); ?>" role="button" class="btn-reports" data-toggle="modal" data-target="#modalReports">
	<span class="menu-text">Employers</span>
</a>
</li>
<li>
<a href="<?php echo site_url( 'admin/reports/marketing-agents-deployed-search' ); ?>" role="button" class="btn-reports" data-toggle="modal" data-target="#modalReports">
	<span class="menu-text">Deployed Applicants</span>
</a>
</li>
<li>
<a href="<?php echo site_url( 'admin/reports/marketing-agents-selected-search' ); ?>" role="button" class="btn-reports" data-toggle="modal" data-target="#modalReports">
	<span class="menu-text">Selected Applicants</span>
</a>
</li>
</ul>
</li>
<li>
<a href="#" class="menu-dropdown" style="">
<span class="menu-text">
Recruitment Agents
</span>
<i class="menu-expand"></i>
</a>

<ul class="submenu">
<li>
<a href="<?php echo site_url( 'admin/reports/recruitment-agents-deployed-search' ); ?>" role="button" class="btn-reports" data-toggle="modal" data-target="#modalReports">
	<span class="menu-text">Deployed Applicants</span>
</a>
</li>
<li>
<a href="<?php echo site_url( 'admin/reports/recruitment-agents-selected-search' ); ?>" role="button" class="btn-reports" data-toggle="modal" data-target="#modalReports">
	<span class="menu-text">Selected Applicants</span>
</a>
</li>
</ul>
</li>
</ul>
</li>
<?php endif; ?> 

<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [2, 4, 8,]) ): ?>
<!-- Accounting Reports -->
<li class="<?php echo controllerActive('accounting', true); ?>" style="display:none">
<a href="#" class="menu-dropdown">
<i class="menu-icon fa fa-file-text-o"></i>
<span class="menu-text"> Accounting Reports </span>

<i class="menu-expand"></i>
</a>

<ul class="submenu">
<li class="<?php echo subControllerActive('accounting/workers-search'); ?>">
<a href="<?php echo site_url( 'admin/accounting/workers-search' ); ?>" role="button" class="btn-reports" data-toggle="modal" data-target="#modalReports">
<span class="menu-text"> Workers</span>
</a>
</li>
<li class="<?php echo subControllerActive('accounting/employers-search'); ?>">
<a href="<?php echo site_url( 'admin/accounting/employers-search' ); ?>" role="button" class="btn-reports" data-toggle="modal" data-target="#modalReports">
<span class="menu-text"> Employers </span>
</a>
</li>
<li class="<?php echo subControllerActive('accounting/marketing-agencies-search'); ?>">
<a href="<?php echo site_url( 'admin/accounting/marketing-agencies-search' ); ?>" role="button" class="btn-reports" data-toggle="modal" data-target="#modalReports">
<span class="menu-text"> Marketing Agencies </span>
</a>
</li>
<li class="<?php echo subControllerActive('accounting/marketing-agents-search'); ?>">
<a href="<?php echo site_url( 'admin/accounting/marketing-agents-search' ); ?>" role="button" class="btn-reports" data-toggle="modal" data-target="#modalReports">
<span class="menu-text"> Marketing Agents </span>
</a>
</li>
<li class="<?php echo subControllerActive('accounting/recruitment-agents-search'); ?>">
<a href="<?php echo site_url( 'admin/accounting/recruitment-agents-search' ); ?>" role="button" class="btn-reports" data-toggle="modal" data-target="#modalReports">
<span class="menu-text"> Recruitment Agents </span>
</a>
</li>
<!-- <li class="<?php echo subControllerActive('accounting/not-approved-vouchers'); ?>">
<a href="<?php href( 'admin/accounting/not-approved-vouchers' ); ?>">
<span class="menu-text">NOT APPROVED VOUCHER</span>
</a>
</li> -->
</ul>
</li>
<?php endif; ?> 






<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [3,4,6,2,8,9]) ): ?>
<!-- Settings -->
<li class="<?php echo controllerActive('settings', true); ?>">
<a href="#" class="menu-dropdown">
<i class="menu-icon fa fa-gears"></i>
<span class="menu-text"> Settings </span>

<i class="menu-expand"></i>
</a>

<ul class="submenu">

<li class="<?php echo subControllerActive('settings/categories'); ?>">
<a href="<?php href('admin/settings/categories'); ?>">
<span class="menu-text"> Categories / Positions</span>
</a>
</li>
<li class="<?php echo subControllerActive('settings/countries'); ?>">
<a href="<?php href('admin/settings/countries'); ?>">
<span class="menu-text"> Countries </span>
</a>
</li>
<?php endif; ?>  
<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [3,4]) ): ?>

<!-- User Logs -->
<li class="<?php echo subControllerActive('settings/countries'); ?>">
<a href="<?php echo site_url( 'admin/userlogs/search' ); ?>" role="button" class="btn-reports" data-toggle="modal" data-target="#modalReports">
<span class="menu-text"> User Logs</span>
</a>
</li>


<!-- Users -->
<li class="<?php echo subControllerActive('settings/countries'); ?>">
<a href="<?php href( 'admin/users/all' ); ?>">
<span class="menu-text"> Users</span>
</a>
</li>
<?php endif; ?> 

<?php if ( !in_array( $_SESSION['admin']['user']['user_type'], [13,20]) ): ?> 
<li class="<?php echo subControllerActive('settings/countries'); ?>">
<a href="" ng-click="redirect('stats_notification')" title="New Status">
<span class="menu-text"> Change Status <span ng-show="{{notifstatus}}" class="badge bg-warning">{{notifstatus}}</span>
</a>
</li>

<li class="<?php echo subControllerActive('settings/countries'); ?>">
<a href="" ng-click="redirect('media_notification')" title="New Media">
<span class="menu-text"> Uploaded File <span ng-show="{{notifmedia}}" class="badge bg-danger">{{notifmedia}}</span></span>
</a>
</li>
<?php endif; ?> 
</ul>
</li>




</ul>
<!-- /Sidebar Menu -->

</div>
