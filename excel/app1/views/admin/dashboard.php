<script type="text/javascript">
function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
}
</script>
<?php
$mylink="http://iwebframework.com/globalpro/";
?>

<div ng-controller="dashboard">
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
		
		<?php //include'db.php';?>
		<?php //include'flow.php';?>
		

        <div class="horizontal-space"></div>
	<h3>Welcome!</h3>	
	
	</br></br>
	
<?php if ( !in_array( $_SESSION['admin']['user']['user_type'], [20,13,21,22]) ): ?> 
		<iframe src="<?=$mylink?>others/index.php" style="width:100%;margin:auto 0 ;height:1000px"></iframe>
	

<?php endif; ?>
	

<?php if ( in_array( $_SESSION['admin']['user']['user_type'], [20111]) ): ?> 
	
	



<?php endif; ?>
	



 	





    </div>
    <!-- /Page Body -->
</div>