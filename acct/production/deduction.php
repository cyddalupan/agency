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

<a href="<?=$actual_link;?>" style="color:Red;text-align:right">Refresh</a>


<div class="row">
<div class="col-md-12 col-xs-12">
<div class="x_panel">
<div class="x_content">
</br>
  <form action="" method="post">
<h4>Deduction</h4>

<p style="color:black">" Note: Enter this DATA if charge to <i style="color:red">Agent</i> for backout,repat,unfit etc"</p>


<button type="button" class="btn btn-danger btn-sm pull-right" data-toggle="modal" data-target="#myModal">Add New </button>
<table  cellspacing="0" id="example-checkbox">
<thead>
<tr>
    <th STYLE="width:2%">#</th>
<th STYLE="width:10%">DATE</th>
<th STYLE="width:10%">Agent</th>
<th STYLE="width:10%">Applicant</th>
<th STYLE="width:25%">Remarks</th>
<th STYLE="width:5%">Amount</th>
<th STYLE="width:5%">Action</th>
</tr>
</thead>
<tbody>
<?php


if(isset($_POST['delete_orders'])){
foreach ($_POST['delall'] as $delall) {
$app11 = mysql_query("delete  FROM deduction where deduction_id= ".$delall)

or die ("cannot Update data");
}
echo"<p style='color:Red'>Succesfully Deleted</p>";
}



$app = mysql_query("SELECT * FROM  deduction

LEFT JOIN  	applicant
ON deduction.app_id = applicant.applicant_id

 order by deduction_id desc  ");


while($row=mysql_fetch_array($app))
{


$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row1=mysql_fetch_array($app1);


echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$row['deduction_id']."' ></td> ";
echo'<td>'; echo date('Y-M-d', strtotime($row['deduction_date']));echo'</td>';
echo'<td STYLE="Color:black;font-weight:bold;font-size:14px">'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td STYLE="Color:black;font-weight:bold;font-size:14px">'.$row["applicant_first"].' '.$row["applicant_last"].'</td>';
echo'<td>'.$row["deduction_remarks"].'</td>';
echo'<td> '.number_format($row["deduction_amount"], 2).'</td>';

echo'<td><button type="button" class="btn btn-info" btn-xs" style="font-size:11px">
<a href="deduction_edit.php?e_id='.$row["deduction_id"].'" target="_blank"  STYLE="color:black">Review</button></a>
</td>';
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


<H2 STYLE="COLOR:RED"> ALL DEDUCTION INPUT BY PESO ONLY</H2>

<form class="form-horizontal form-label-left input_mask" method="post" action="">	

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">DATE Transaction</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="date" class="form-control" name="deduction_date" value="<?=$date1?>" required>
</div>
</div>


<script>
function showapplicant(str) {

var xhttp;    
if (str == "") {
document.getElementById("showapp").innerHTML = "";
return;
}
xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
if (this.readyState == 4 && this.status == 200) {
document.getElementById("showapp").innerHTML = this.responseText;
}
};
xhttp.open("GET", "showapp.php?q="+str, true);
xhttp.send();
}

</script>


<div class="form-group" STYLE="">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Agent</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<?php
echo'<select class="form-control" name="agent_id" required onchange="showapplicant(this.value)" >

<option vaue="">N/A</option>
';

$usertype = mysql_query("SELECT * FROM recruitment_agent  
 ORDER BY agent_first asc");
while($rows=mysql_fetch_array($usertype))
{
echo'<option value="'.$rows["agent_id"].'"> '.$rows["agent_first"].' '.$rows["agent_last"].'</option>';
}
ECHO'</select>';
?>
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Applicant</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<?php
echo'<select class="form-control" name="app_id"  id="showapp" required>
<option  value="">-----</option>';

//$applicant = mysql_query("SELECT * FROM applicant ORDER BY applicant_date_applied asc");
//while($rows1=mysql_fetch_array($applicant))
//{
//echo'<option value="'.$rows1["applicant_id"].'">  '.$rows1["applicant_last"].' '.$rows1["applicant_first"].'-'.$rows1["applicant_id"].'</option>';
//}
//ECHO'</select>';
?>

</div>
</div>


</BR>
Amount

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Amount</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="deduction_amount" step=any required placeholder="Amount">
</div>
</div>





<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Particular(s)</label>
<div class="col-md-9 col-sm-9 col-xs-12">

<textarea name="deduction_remarks"></textarea>
</div>
</div>



<div class="form-group">
<div class="col-md-12 col-sm-9 col-xs-12">	
<button type="submit" class="btn btn-danger pull-right"  name="add_deduction">Save</button>
</div>
</div>


</form>		  



</div>

</div>

</div>
</div>





