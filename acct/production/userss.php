<?php include'inc/header.php';
include'inc/expenses.php';
include'tracking.php';
include'styles.php';
?>
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <div class="col-md-3 left_col">
          <div class="left_col scroll-view">
            <div class="navbar nav_title" style="border: 0;">
              <a href="index.html" class="site_title"> <span><?php include'title.php';?></span></a>
            </div>

            <div class="clearfix"></div>

            <!-- menu profile quick info -->
            <div class="profile clearfix">
              <div class="profile_pic">
                <img src="images/img.jpg" alt="..." class="img-circle profile_img">
              </div>
              <div class="profile_info">
                <span>Welcome,</span>
                <h2><?=$_SESSION['user_fullname']?></h2>
              </div>
            </div>
            <!-- /menu profile quick info -->

            <br />

            <!-- sidebar menu -->
            <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
            
              <div class="menu_section">
               <?php include'inc/menu.php';?>	
              </div>

            </div>
            <!-- /sidebar menu -->

            <!-- /menu footer buttons -->
            <div class="sidebar-footer hidden-small">
              <a data-toggle="tooltip" data-placement="top" title="Settings">
                <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="FullScreen">
                <span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="Lock">
                <span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="Logout" href="login.html">
                <span class="glyphicon glyphicon-off" aria-hidden="true"></span>
              </a>
            </div>
            <!-- /menu footer buttons -->
          </div>
        </div>

        <!-- top navigation -->
        <div class="top_nav">
          <div class="nav_menu">
            <nav>
              <div class="nav toggle">
                <a id="menu_toggle"><i class="fa fa-bars"></i></a>
              </div>

              <ul class="nav navbar-nav navbar-right">
                <li class="">
                  <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <img src="images/img.jpg" alt="">
                    <span class=" fa fa-angle-down"></span>
                  </a>
                  <ul class="dropdown-menu dropdown-usermenu pull-right">
                   
                    </li>
                    <li><a href="logout.php"><i class="fa fa-sign-out pull-right"></i> Log Out</a></li>
                  </ul>
                </li>

              
                  </ul>
                </li>
              </ul>
            </nav>
          </div>
        </div>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
 

         
              



<div class="row">



<div class="row">
<div class="col-md-12 col-xs-12">
<div class="x_panel">
<div class="x_content">
</br>
  <form action="" method="post">
<h4>Users
</h4>
<table  cellspacing="0" id="example-checkbox">
<thead>
<tr>
<th STYLE="width:2%">#</th>
<th >Date</th>
<th STYLE="width:10%">Name</th>
<th >Username</th>
<th >Password</th>
<th >Usertype</th>
</tr>
</thead>
<tbody>
<?php

if(isset($_POST['delete_orders'])){
foreach ($_POST['delall'] as $delall) {
$app11 = mysql_query("delete  FROM user where user_id= ".$delall)

or die ("cannot Update data");
}
echo"<p style='color:Red'>Succesfully Deleted</p>";
}



$app = mysql_query("SELECT * FROM user where  user_type IN (17,2)

order by user_id desc");


while($row=mysql_fetch_array($app))
{


if($row["user_type"]==2){$request='Head';$rcolor='red';}
if($row["user_type"]==17){$request='Staff';$rcolor='red';}

echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$row['user_id']."' ></td> ";
echo'<td>'; echo date('Y-M-d', strtotime($row['user_created']));echo'</td>';
echo'<td>'.$row["user_fullname"].'</td>';
echo'<td>'.$row["user_name"].'</td>';
echo'<td>'.$row["acct_pass"].'</td>';
echo'<td>'.$request.'</td>';

echo'</tr>';
}
$tot = number_format($total, 2);
?>



</tbody>
</table>
</br></br>
<div class="row">
<div class="col-5" style="width:100%,float:right">		
<input type="submit" value="Delete" name="delete_orders" style="width:130px;background:Red;padding:5px;margin-left:20px;margin-top:-1px;color:white">

</div>
</div>
</form>	



</br></br></br></br>
<div class="ln_solid"></div>

</div>
</div>
</div>  
</div>	






</div>

 <script src="../vendors/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- Skycons -->
<script src="../vendors/skycons/skycons.js"></script>

<!-- Custom Theme Scripts -->
<script src="../build/js/custom.min.js"></script>
	
  </body>
</html>






<!-- Modal -->
<div class="modal fade" id="myModal" role="dialog">
<div class="modal-dialog">

<!-- Modal content-->
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal">&times;</button>
<h4 class="modal-title">New  Transaction</h4>
</div>
<div class="modal-body">

<form class="form-horizontal form-label-left input_mask" method="post" action="">	

<h1>FOR UPDATE</h1>


</form>		  



</div>

</div>

</div>
</div>





