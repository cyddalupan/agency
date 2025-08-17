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
<h4>Cash Fund</h4>
<button type="button" class="btn btn-danger btn-sm pull-right" data-toggle="modal" data-target="#myModal">Add New CASH Fund</button>
<table  cellspacing="0" id="example-checkbox">
<thead>
<tr>
    <th STYLE="width:2%">#</th>
<th STYLE="width:10%">DATE</th>
<th STYLE="width:10%">Cash OUT account</th>
<th STYLE="width:10%">Cash IN account</th>
<th STYLE="width:15%">Description</th>
<th STYLE="width:10%">Amount</th>
<th STYLE="width:10%">Other Amount</th>
<th STYLE="width:10%">Total</th>
</tr>
</thead>
<tbody>
<?php


if(isset($_POST['delete_orders'])){
foreach ($_POST['delall'] as $delall) {
$app11 = mysql_query("delete  FROM fund where fund_id= ".$delall)

or die ("cannot Update data");
}
echo"<p style='color:Red'>Succesfully Deleted</p>";
}



$app = mysql_query("SELECT * FROM  fund
where fund_date_status=0
order by fund_id desc  ");


while($row=mysql_fetch_array($app))
{


$amt = number_format($row["fund_amount"]+$row['fund_amount_other'], 2);
$total=$amt +$total;
$bank = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$row['fund_bank_id']." ");
$bank1=mysql_fetch_array($bank);

$source = mysql_query("SELECT * FROM  source_payment where s_id=".$row['fund_source_id']." ");
$source1=mysql_fetch_array($source);

echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$row['fund_id']."' ></td> ";
echo'<td>'; echo date('Y-M-d', strtotime($row['fund_date']));echo'</td>';
echo'<td>'.$bank1["bank_name"].' - Account #: '.$bank1["bank_num"].' </BR> Currency: '.$bank1["bank_currency"].'</td>';
echo'<td>'.$source1["s_name"].' </td>';
echo'<td>'.$row["fund_p"].'</td>';
echo'<td> '.number_format($row["fund_amount"], 2).'</td>';
echo'<td> '.number_format($row['fund_amount_other'], 2).'</td>';
echo'<td style="color:red">'.$amt.' '.$row["fund_currency"].'</td>';
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

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">DATE Transaction</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="date" class="form-control" name="fund_date" value="<?=$date1?>" required>
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">CURRENCY</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<select class="form-control" required  name="fund_currency" required>
<option VALUE=""></option>
<option>USD</option>
<option>PHP</option>

</select>
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Bank Accounts (Cash out)</label>
<div class="col-md-9 col-sm-9 col-xs-12">

<?php
echo'<select class="form-control" name="fund_bank_id"  required>';
echo'<option value="">  --</option>';
$bank = mysql_query("SELECT * FROM bank_accounts ORDER BY bank_name asc");
while($bank1=mysql_fetch_array($bank))
{
echo'<option value="'.$bank1["bank_id"].'">  '.$bank1["bank_name"].' - Account #: '.$bank1["bank_num"].' ('.$bank1["bank_currency"].') </option>';
}
ECHO'</select>';
?>

</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Transfer to Source(Cash IN)</label>
<div class="col-md-9 col-sm-9 col-xs-12">

<?php
echo'<select class="form-control" name="fund_source_id"  required>';
echo'<option value="">  --</option>';
$source = mysql_query("SELECT * FROM source_payment ORDER BY s_name asc");
while($source1=mysql_fetch_array($source))
{
echo'<option value="'.$source1["s_id"].'">  '.$source1["s_name"].' </option>';
}
ECHO'</select>';
?>

</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Amount</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="fund_amount" step=any required>
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Other Amount</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="fund_amount_other" step=any required>
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Particular(s)</label>
<div class="col-md-9 col-sm-9 col-xs-12">

<textarea name="fund_p"></textarea>
</div>
</div>



<div class="form-group">
<div class="col-md-12 col-sm-9 col-xs-12">	
<button type="submit" class="btn btn-danger pull-right"  name="add_fund">Save</button>
</div>
</div>


</form>		  



</div>

</div>

</div>
</div>





