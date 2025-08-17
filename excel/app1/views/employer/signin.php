<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<!-- Title here -->
	<title><?php echo $_SESSION["settings"]['client']; ?></title>
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
		
		echo 'var '.implode(',', $urls) . ';';
	?>
	</script>
</head>
<body>
<!-- Content strats -->

<div class="content">
<div class="container">
  <div class="row">
     <div class="col-md-12">


        
        <!-- Login starts -->
        <div class="">
           <div class="row">
              <div class="col-md-12">
              	<?php $app->renderAlerts(); ?>
                 <div class="logreg-page">
                    <h3>Sign In to <span class="color">Employer</span></h3>                        
                    <hr />
                    <div class="form">
                      <!-- Login form (not working)-->
                      <form class="form-horizontal" method="post" role="form" action="">
                          <!-- Username -->
                          <div class="form-group">
                            <label class="control-label col-md-3" for="login[username]">Username</label>
                            <div class="col-md-8">
                              <input name="login[username]" type="text" class="form-control" placeholder="Username">
                            </div>
                          </div>
                          <!-- Password -->
                          <div class="form-group">
                            <label class="control-label col-md-3" for="login[password]">Password</label>
                            <div class="col-md-8">
                              <input name="login[password]" type="password" class="form-control" placeholder="Password">
                            </div>
                          </div>
                          <div class="form-group">
                             <div class="col-md-9 col-md-offset-3">
                                <label class="checkbox-inline">
                                <input type="checkbox"> Remember me
                                </label>
                             </div>
                          </div>                                                                               
                          <!-- Buttons -->
                          <div class="form-group">
                             <!-- Buttons -->
							 <div class="col-md-9 col-md-offset-3">
                            
                            <button type="reset" class="btn btn-default">Reset</button>
                            <button type="submit" class="btn btn-primary">Login</button>
							</div>
						</div>  
                      </form>
					  
                      <hr />
                          <div class="lregister">
                             Forgot password? <a href="#">Request a new password</a>
                          </div>
                    </div>                       
                 </div>
              </div>
           </div>
        </div>
        
        <!-- Login ends --> 
     </div>
  </div>
</div>
</div>   

<!-- Content ends --> 


	<!-- Footer -->
	<?php $app->renderFooter(); ?>	
	<!--/ Footer -->

	<!-- Scroll to top -->
	<span class="totop"><a href="#"><i class="fa fa-angle-up"></i></a></span> 
			
	<!-- Javascript files -->
	<!-- jQuery -->
	<script src="<?php echo $app->getPath()['scripts']; ?>jquery.js"></script>
	<!-- Bootstrap JS -->
	<script src="<?php echo $app->getPath()['scripts']; ?>bootstrap.min.js"></script>
	<!-- Isotope, Pretty Photo JS -->
	<script src="<?php echo $app->getPath()['scripts']; ?>jquery.isotope.js"></script>
	<script src="<?php echo $app->getPath()['scripts']; ?>jquery.prettyPhoto.js"></script>
	<!-- Support Page Filter JS -->
	<script src="<?php echo $app->getPath()['scripts']; ?>filter.js"></script>
	<!-- Flex slider JS -->
	<script src="<?php echo $app->getPath()['scripts']; ?>jquery.flexslider-min.js"></script>
	<!-- Respond JS for IE8 -->
	<script src="<?php echo $app->getPath()['scripts']; ?>respond.min.js"></script>
	<!-- HTML5 Support for IE -->
	<script src="<?php echo $app->getPath()['scripts']; ?>html5shiv.js"></script>
	<!-- Custom JS -->
	<script src="<?php echo $app->getPath()['scripts']; ?>custom.js"></script>
</body>
</html>