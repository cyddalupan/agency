<?php
if($_SESSION['admin']['user']['user_id'] == 9999){
    session_destroy();
    redirect( site_url( '/' ) );
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<!-- Head -->
<head>
    <meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">
    <meta charset="utf-8" />
    <title><?php echo $app->getTitle(); ?></title>

    <!-- Description, Keywords and Author -->
    <meta name="description" content="Dashboard" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <?php echo $app->renderMetas(); ?>
   
    <!--Basic Styles-->
    <link href="<?php echo $app->getPath()['styles']; ?>bootstrap.min.css" rel="stylesheet" />
    <link id="bootstrap-rtl-link" href="#" rel="stylesheet" />
    <link href="<?php echo $app->getPath()['styles']; ?>font-awesome.min.css" rel="stylesheet" />
    <link href="<?php echo $app->getPath()['styles']; ?>weather-icons.min.css" rel="stylesheet" />

    <!--Fonts-->
    <!-- <link href="http://font.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,400,600,700,300" rel="stylesheet" type="text/css"> -->

    <!--Beyond styles-->
    <link id="beyond-link" href="<?php echo $app->getPath()['styles']; ?>beyond.min.css" rel="stylesheet" type="text/css" />

    <link href="<?php echo $app->getPath()['styles']; ?>typicons.min.css" rel="stylesheet" />
    <link href="<?php echo $app->getPath()['styles']; ?>animate.min.css" rel="stylesheet" />
    <link href="<?php echo $app->getPath()['styles']; ?>style.css" rel="stylesheet" />
    <link id="skin-link" href="#" rel="stylesheet" type="text/css" />    
    
    <?php $app->renderStyles(); ?>

    <script type="text/javascript">
	<?php 
		$paths    = $app->getPath();
		$urls     = [];
		$urls[]   = sprintf("%s='%s'", 'baseUrl', str_replace("'", "\'", base_url()));
		$urls[]   = sprintf("%s='%s'", 'siteUrl', str_replace("'", "\'", site_url()));
		
		foreach ($paths as $key => $path) {
			$urls[] = sprintf("%sUrl='%s'", $key, str_replace("'", "\'", $path));
		}
		
		echo 'var '.implode(', ', $urls) . ';';
	?>
	</script>
    
    <!--Skin Script: Place this script in head to load scripts for skins and rtl support-->
    <script src="<?php echo $app->getPath()['scripts']; ?>skins.min.js"></script>
</head>
<!-- /Head -->
<!-- Body -->
<body>
    <!-- Loading Container -->
    <div class="loading-container">
        <div class="loading-progress">
            <div class="rotator">
                <div class="rotator">
                    <div class="rotator colored">
                        <div class="rotator">
                            <div class="rotator colored">
                                <div class="rotator colored"></div>
                                <div class="rotator"></div>
                            </div>
                            <div class="rotator colored"></div>
                        </div>
                        <div class="rotator"></div>
                    </div>
                    <div class="rotator"></div>
                </div>
                <div class="rotator"></div>
            </div>
            <div class="rotator"></div>
        </div>
    </div>
    <!--  /Loading Container -->
    <!-- Navbar -->

 
	<div class="row hearing_alert" style="border:0px solid black;background:#FF0066 ;color:white;height:40px;z-index:99;text-align:center;position:relative;font-size:20px;padding:5px; display:none;">
		<span class="fullname"></span> 
        &nbsp;&nbsp;&nbsp;
        <small style="color:#D8D8D8" class="hearing_date"></small> &nbsp;&nbsp;&nbsp;
        <a href="<?php echo apiLoginLink(); ?>" class="btn-applicant-review btn btn-default btn-xs">See More!</a>  
	</div>
    <?php $app->renderHeader(); ?>
    <!-- /Navbar -->
    <!-- Main Container -->
	
    
    <div class="main-container container-fluid">
	
	
		<!-- Page Container -->
        <div class="page-container">
            <!-- Page Sidebar -->
            <?php $app->renderSideBar(); ?>
            <!-- /Page Sidebar -->
            
            <!-- Page Content -->
            <div class="page-content">
                <?php $app->renderTemplate(); ?>
                <!--One Page Loading-->
                <div id="applicants" class="page-content-section"><?php require('public/applicants.html'); ?></div>
                <div id="stats_notification" class="page-content-section" style="display:none;"><?php include('public/stats_notification.html'); ?></div>
                <div id="media_notification" class="page-content-section" style="display:none;"><?php include('public/media_notification.html'); ?></div>
                <div id="change_password" class="page-content-section" style="display:none;"><?php include('public/change_password.html'); ?></div>

                <script>
                    function showSection(sectionId) {
                        const sections = document.querySelectorAll('.page-content-section');
                        sections.forEach(section => {
                            if (section.id === sectionId) {
                                section.style.display = 'block';
                            } else {
                                section.style.display = 'none';
                            }
                        });
                    }

                    function handleHashChange() {
                        const hash = window.location.hash.substring(1);
                        if (hash) {
                            showSection(hash);
                        } else {
                            // Default to showing the applicants section
                            showSection('applicants');
                        }
                    }

                    window.addEventListener('hashchange', handleHashChange);

                    // Initial page load
                    handleHashChange();
                </script>
                <!--Site Footer-->
                <?php $app->renderFooter(); ?>
            </div>
            <!-- /Page Content -->
        </div>
        <!-- /Page Container -->
        <!-- Main Container -->
    </div>
    
    <!-- Generic Modal container -->
    <div id="modalReports" role="dialog" tabindex="-1" class="modal fade modal-darkorange" aria-hidden="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
                    <h4 class="modal-title">Loading</h4>
                </div>
                
                <div class="modal-body">
                    <div class="bootbox-body">
                        Please wait...
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal" type="button">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <!-- endOf: Generic Modal container -->

    <?php $app->renderModals(); ?>


    <!--cyd jquery-->
    <script>base_url = '<?php echo base_url(); ?>';</script>
    
    
    <script src="<?php echo $app->getPath()['scripts']; ?>jquery-2.0.3.min.js"></script>
    <script src="<?php echo $app->getPath()['scripts']; ?>bootstrap.min.js"></script>
    <script src="<?php echo $app->getPath()['scripts']; ?>beyond.min.js"></script>
    
    <script src="<?php echo $app->getPath()['plugins']; ?>timeago/jquery.livequery.js"></script>
	<script src="<?php echo $app->getPath()['plugins']; ?>timeago/jquery.timeago.js"></script>
    <script src="<?php echo $app->getPath()['scripts']; ?>script.js"></script> 
    <script src="<?php echo $app->getPath()['scripts']; ?>pages/applicants.js"></script>


    <?php $app->renderScripts(); ?>
    
    <style type="text/css">
        <?php echo $_SESSION["settings"]['style']; ?>
    </style>
<script>
window.addEventListener('load', function() {
    setTimeout(function(){
        document.getElementById('dashboardstats').src = base_url + 'admin/dashboard/stats';
        document.querySelector('.dashboardstats-loading').style.display = 'none';
    }, 1000);

    setTimeout(function(){
        document.getElementById('updatesboard').src = base_url + 'admin/dashboard/updatesboard';
        document.querySelector('.updatesboard-loading').style.display = 'none';
    }, 2000);
});
</script>
</body>
<!--  /Body -->
</html>