<!DOCTYPE html>
<html lang="en" ng-app="myApp">
<head>
    <meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">
	<meta charset="utf-8">
    <title><?php echo $app->getTitle(); ?></title>
	<!-- Description, Keywords and Author -->
	<meta name="description" content="Your description">
	<meta name="keywords" content="Your,Keywords">
	<meta name="author" content="ResponsiveWebInc">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php echo $app->renderMetas(); ?>

	<!-- Styles -->
	<!-- Bootstrap CSS -->
	<link href="<?php echo $app->getPath()['styles']; ?>bootstrap.min.css" rel="stylesheet">
	<!-- Flex Slider CSS -->
	<link href="<?php echo $app->getPath()['styles']; ?>flexslider.css" rel="stylesheet">
	<!-- Pretty Photo -->
	<link href="<?php echo $app->getPath()['styles']; ?>prettyPhoto.css" rel="stylesheet">
	<!-- Font awesome CSS -->
	<link href="<?php echo $app->getPath()['styles']; ?>font-awesome.min.css" rel="stylesheet">
	<!-- Custom CSS -->
	<link href="<?php echo $app->getPath()['styles']; ?>style.css" rel="stylesheet">
	<!--cydcss-->
	<link href="<?php echo base_url(); ?>page/public/css/all.css" rel="stylesheet">
	<?php $app->renderStyles(); ?>

	<!-- Favicon -->
	<link rel="shortcut icon" href="<?php echo $app->getPath()['images']; ?>favicon/favicon.png">
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
</head>
<body>
	<?php $app->renderHeader(); ?>
    <?php $app->renderMenus(); ?>
	<!-- Content starts -->
	<div class="content page-<?php echo str_replace('/', '-', $app->getTemplate() ); ?>">
		<div class="container">
			<?php $app->renderTemplate(); ?>
		</div><!-- .container -->
	</div>
	<!-- Content ends -->
	<!-- Footer -->
	<?php $app->renderFooter(); ?>
	<!--/ Footer -->
	<!-- Scroll to top -->
	<span class="totop"><a href="#"><i class="fa fa-angle-up"></i></a></span>

	<!-- Javascript files -->
	<!-- jQuery -->

	<!--cyd jquery-->
	<script>base_url = '<?php echo base_url(); ?>';</script>
	
    <script src="<?php echo $app->getPath()['scripts']; ?>angular.min.js"></script>
    <script src="<?php echo $app->getPath()['scripts']; ?>ng-file-upload-all.min.js"></script>
    <script src="<?php echo $app->getPath()['scripts']; ?>angular-cookies.min.js"></script>
    <script src="<?php echo $app->getPath()['scripts']; ?>jquery-2.0.3.min.js"></script>
	<!-- Bootstrap JS -->
	<script src="<?php echo $app->getPath()['scripts']; ?>bootstrap.min.js"></script>
	<!-- Respond JS for IE8 -->
	<script src="<?php echo $app->getPath()['scripts']; ?>respond.min.js"></script>
	<!-- HTML5 Support for IE -->
	<script src="<?php echo $app->getPath()['scripts']; ?>html5shiv.js"></script>
	<!-- Custom JS -->
	<script src="<?php echo $app->getPath()['scripts']; ?>custom.js"></script>
	<?php $app->renderScripts(); ?>
	<script src="<?php echo base_url(); ?>page/public/js/all.js"></script>
	<style type="text/css">
        <?php echo $_SESSION["settings"]['style']; ?>
    </style>
</body>
</html>