<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title> </title>

    <!-- Bootstrap -->
    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="../vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- Animate.css -->
    <link href="../vendors/animate.css/animate.min.css" rel="stylesheet">

    <!-- Custom Theme Style -->
    <link href="../build/css/custom.min.css" rel="stylesheet">
  </head>

  <body class="login">
    <div>
      <a class="hiddenanchor" id="signup"></a>
      <a class="hiddenanchor" id="signin"></a>

      <div class="login_wrapper">
        <div class="animate form login_form">
          <section class="login_content">
		  <?php include'login.php';?>
		  
             <form id="form1" name="form1" method="post" action="">
    	   
              <h1><?php include'title.php';?></h1>
              <div>
                <input type="text" class="form-control" placeholder="Username"  name="username" required=""  />
              </div>
              <div>
                <input type="password" class="form-control" placeholder="Password" name="password" />
              </div>
              <div>
				<input type='submit' name='Submits' value='Submit' 
				style="float:right;color:white;background:#03066b;border-radius:10px;padding:5px;font-weight:bold;cursor:pointer" />
              </div>

              <div class="clearfix"></div>

            </form>
          </section>
        </div>

     
      </div>
    </div>
  </body>
</html>
