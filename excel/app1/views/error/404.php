<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<!--Head-->
<head>
    <meta charset="utf-8" />
    <title>Error 404 - Page Not Found</title>

    <meta name="description" content="Dashboard" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <?php echo $app->renderMetas(); ?>
    <link rel="shortcut icon" href="<?php echo $app->getPath()['images']; ?>admin/favicon.png" type="image/x-icon">

    <!--Basic Styles-->
    <link href="<?php echo $app->getPath()['styles']; ?>admin/bootstrap.min.css" rel="stylesheet" />
    <link id="bootstrap-rtl-link" href="#" rel="stylesheet" />
    <link href="<?php echo $app->getPath()['styles']; ?>admin/font-awesome.min.css" rel="stylesheet" />
    <link href="<?php echo $app->getPath()['styles']; ?>admin/weather-icons.min.css" rel="stylesheet" />

    <!--Fonts-->
    <!-- <link href="http://font.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,400,600,700,300" rel="stylesheet" type="text/css"> -->

    <!--Beyond styles-->
    <link id="beyond-link" href="<?php echo $app->getPath()['styles']; ?>admin/beyond.min.css" rel="stylesheet" type="text/css" />

    <link href="<?php echo $app->getPath()['styles']; ?>admin/typicons.min.css" rel="stylesheet" />
    <link href="<?php echo $app->getPath()['styles']; ?>admin/animate.min.css" rel="stylesheet" />
    <link href="<?php echo $app->getPath()['styles']; ?>admin/style.css" rel="stylesheet" />
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
<!--Head Ends-->
<!--Body-->
<body class="body-404">
    <div class="error-header"> </div>
    <div class="container ">
        <section class="error-container text-center">
            <h1>404</h1>
            <div class="error-divider">
                <h2>page not found</h2>
                <p class="description">We Couldn’t Find This Page</p>
            </div>
            <a href="<?php echo site_url( 'admin' ); ?>" class="return-btn"><i class="fa fa-home"></i>Home</a>
        </section>
    </div>
    
    <!--Basic Scripts-->
    <script src="<?php echo $app->getPath()['scripts']; ?>jquery-2.0.3.min.js"></script>
    <script src="<?php echo $app->getPath()['scripts']; ?>bootstrap.min.js"></script>
    
    <script src="<?php echo $app->getPath()['scripts']; ?>admin/script.js"></script> 
    <?php $app->renderScripts(); ?>
</body>
<!--Body Ends-->
</html>
