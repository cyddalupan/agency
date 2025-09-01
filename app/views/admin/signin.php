<!DOCTYPE html>
<html lang="en">
<!--Head-->
<head>
    <meta charset="utf-8" />
    <title><?php echo $_SESSION["settings"]['client_full']; ?></title>

    <meta name="description" content="login page" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="<?php echo $app->getPath()['images']; ?>favicon.png" type="image/x-icon">

    <!--Basic Styles-->
    <link href="<?php echo $app->getPath()['styles']; ?>bootstrap.min.css" rel="stylesheet" />
    <link id="bootstrap-rtl-link" href="#" rel="stylesheet" />
    <link href="<?php echo $app->getPath()['styles']; ?>font-awesome.min.css" rel="stylesheet" />

    <!--Fonts-->
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,400,600,700,300" rel="stylesheet" type="text/css">

    <!--Beyond styles-->
    <link id="beyond-link" href="<?php echo $app->getPath()['styles']; ?>beyond.min.css" rel="stylesheet" />
    <link href="<?php echo $app->getPath()['styles']; ?>style.css" rel="stylesheet" />
    <link href="<?php echo $app->getPath()['styles']; ?>animate.min.css" rel="stylesheet" />
    <link id="skin-link" href="#" rel="stylesheet" type="text/css" />

    <?php $app->renderStyles(); ?>

    <!--Skin Script: Place this script in head to load scripts for skins and rtl support-->
    <script src="<?php echo $app->getPath()['scripts']; ?>skins.min.js"></script>
</head>
<!--Head Ends-->
<!--Body-->
<body>
    <?php $app->renderAlerts(); ?>
        <?php 
            if(isset($adminnote) && $adminnote != ''){
                echo '<div class="alert alert-info" role="alert">';
                echo $adminnote;
                echo '</div>';
            }
        ?>
    <div class="logreg-page animated fadeInDown">
        <h3>Sign In to <strong><span class="color"><?php echo $_SESSION["settings"]['client']; ?> </span></strong></h3>                        
        <hr>
        <div class="form">
            <!-- Login form (not working)-->
            <form action="<?php echo site_url( 'admin/signin' ); ?>" role="form" method="post" class="form-horizontal">
            <!-- Username -->
            <div class="form-group">
                <label for="login[username]" class="control-label col-md-3">Username</label>
                <div class="col-md-8">
                    <input type="text" placeholder="" class="form-control" name="login[username]">
                </div>
            </div>
            <!-- Password -->
            <div class="form-group">
                <label for="login[password]" class="control-label col-md-3">Password</label>
                <div class="col-md-8">
                    <input type="password" placeholder="" class="form-control" name="login[password]">
                </div>
            </div>                                                                            
            <!-- Buttons -->
            <div class="form-group">
                <!-- Buttons -->
                <div class="col-md-9 col-md-offset-3">
                    <button class="btn btn-default" type="reset">Reset</button>
                    <button class="btn btn-primary" type="submit">Login</button>
                </div>
            </div>  
            </form>

            <hr>
            <div class="lregister">
                Forgot password? <a href="#">Request a new password</a>
            </div>
        </div>                       
    </div>



    <!--Basic Scripts-->
    <script src="<?php echo $app->getPath()['scripts']; ?>jquery-2.0.3.min.js"></script>
    <script src="<?php echo $app->getPath()['scripts']; ?>bootstrap.min.js"></script>
	<script src="<?php echo $app->getPath()['scripts']; ?>script.js"></script>
    
</body>
<!--Body Ends-->
</html>
