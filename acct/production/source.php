<?php include'inc/header.php';?>

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
<h2>John Doe</h2>
</div>
</div>
<!-- /menu profile quick info -->

<br />

<!-- sidebar menu -->
<div id="sidebar-menu" class="main_menu_side hidden-print main_menu">

<div class="menu_section">
<h3>Live On</h3>
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


</ul>                </li>
</ul>
</nav>
</div>
</div>
<!-- /top navigation -->

<!-- page content -->
<div class="right_col" role="main">






<div class="row">




<div class="col-md-12 col-xs-12">


<div class="x_panel">

<h2>Petty Cash / Source Payment</h2>
<p style="color:Red">NOTE: This will reflect to CASH FUND TAB</p>

<div class="x_content">


<div class="x_panel">


<div class="x_content">

<form action="" method="post" style="width:400px;float:left" >

<input type="text" class="form-control" name="s_name" required></div>


<input type="submit" name="add_set1" value="ADD" style="width:80px;flot:right" >

</form>

<div class="clearfix"></div>    <div class="clearfix"></div>
</br></br>
<form action="" method="post">

<table class="table table-bordered">
<thead>
<tr>
<th STYLE="width:1">#</th>
<th>Code#</th>
<th>Date Added</th>
<th >Source Name</th>
<th>Debit</th>
<th>Credit</th>
<th>Balance</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php	
include'inc/functions.php';
if(isset($_POST['delete_orders'])){
foreach ($_POST['delall'] as $delall) {
$app11 = mysql_query("delete  FROM source_payment where s_id= ".$delall)

or die ("cannot Update data");
}
echo"<p style='color:Red'>Succesfully Deleted</p>";
}

$app = mysql_query("SELECT * FROM source_payment
order by s_id desc");


while($row=mysql_fetch_array($app))
{

$credit = mysql_query("SELECT SUM(fund_amount+fund_amount_other) as amount_total ,fund.* FROM fund
WHERE fund_source_id=".$row["s_id"]."");
$credit1=mysql_fetch_array($credit);


$expense = mysql_query("SELECT SUM(amount) as amount_total ,email_address.* FROM email_address
WHERE fund_source_id=".$row["s_id"]."");
$expense1=mysql_fetch_array($expense);

$balance=$credit1["amount_total"]-$expense1["amount_total"];	
	
echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$row['s_id']."' ></td> ";
echo'<td>000'.$row["s_id"].'</td>';
echo'<td>'.$row["dateadded"].'</td>';
echo'<td>'.$row["s_name"].'</td>';
echo'<td> '.number_format($credit1["amount_total"], 2).'</td>';
echo'<td> '.number_format($expense1["amount_total"], 2).'</td>';
echo'<td style="color:Red"> '.number_format($balance, 2).'</td>';
echo'<td>
<a href="#">
<button type="button" class="btn btn-danger" btn-xs" style="font-size:11px;margin:3px">Review</button></a>
</td>';

echo'</tr>';
}
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


</div>
</div>

<div class="ln_solid"></div>

</div>
</div>
</div>  




</div>			











<!-- /footer content -->
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
