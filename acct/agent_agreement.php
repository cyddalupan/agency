<?php
session_start();
include'inc/functions.php';
include'tracking.php';
include'styles.php';
	

if(isset($_POST['delete_orders'])){
foreach ($_POST['delall'] as $delall) {
mysql_query("delete  FROM agent_agreement
WHERE agree_id = ".$delall)
or die ("cannot Update data");
}
echo"<p style='color:Red'>Succesfully Deleted.</p>";
}



$app = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$_GET['agent']." ");
$row=mysql_fetch_array($app);




?>

<title>Accounting System</title>

<!-- Bootstrap -->
<link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
<!-- NProgress -->
<link href="../vendors/nprogress/nprogress.css" rel="stylesheet">
<!-- iCheck -->
<link href="../vendors/iCheck/skins/flat/green.css" rel="stylesheet">

<!-- bootstrap-progressbar -->
<link href="../vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet">
<!-- JQVMap -->
<link href="../vendors/jqvmap/dist/jqvmap.min.css" rel="stylesheet"/>
<!-- bootstrap-daterangepicker -->
<link href="../vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">

<!-- Custom Theme Style -->
<link href="../build/css/custom.min.css" rel="stylesheet">
<style>
#wrapper{
border:0px solid black;
width:90%;
min-height:630px;
height:!important;
margin: 0 auto;	
padding:10px;
font-size:12px;
font-family:calibri;
}
.table-fixed td,th{
font-size:13px;
padding:2px;
}


</style>

<body style="background:white">
<div id="wrapper">

<div>
<h1 style="font-size:20px;red:black;text-align:left;color:black">Agent  Name: <?=$row['agent_first']?> <?=$row['agent_last']?></h1>

</div>
<a href="<?=$actual_link;?>" style="color:Red;text-align:right">Refresh</a>

<!-- page content -->
<div class="right_col" role="main">
<div class="">


<div class="clearfix"></div>

<div class="row">
<div class="col-md-12">
<div class="x_panel">

<div class="x_content">

<section class="content invoice">



</div>

</BR>

<!-- Table row -->
<div class="row">
<div class="col-xs-12 ">




<div class="row" >
<div class="col-md-12 col-xs-12" >
<div class="x_panel" style="background:#F5F5F5">
<div class="x_content">




<h3>Agreement per Country</h3>

<form class="form-horizontal form-label-left input_mask" method="post" action="">	





<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Country </label>
<div class="col-md-4 col-sm-6 col-xs-12">
<select class="form-control" required  name="agree_country" required>
<option value=""> --</option>
<?php
$usertype1 = mysql_query("SELECT * FROM country ORDER BY country_name asc");
while($rows1=mysql_fetch_array($usertype1))
{
echo'<option value="'.$rows1["country_id"].'">  '.$rows1["country_name"].'</option>';
}
?>
</select>
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">agree_ppt</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="agree_ppt" step="any"  required value="">
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">agree_ftw</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="agree_ftw" step="any"  required value="">
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">agree_contract</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="agree_contract" step="any"  required value="">
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">agree_deployed</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="agree_deployed" step="any"  required value="">
</div>
</div>






<div class="form-group">
<div class="col-md-4 col-sm-6 col-xs-12">
<input type="hidden" class="form-control" name="agent_id" value="<?=$_GET['agent']?>">
<button type="submit" class="btn btn-danger pull-right"  name="add_agree">Save</button>
</div>
</div>
</form>	

  <form action="" method="post">
<table  cellspacing="1" id="customers">
<thead>
<tr>
<th>#</th>
<th>Country</th>
<th>Passport</th>
<th>agree_ftw</th>
<th>agree_contract</th>
<th>agree_deployed</th>

</tr>
</thead>
<tbody>
<?php	
$agreerow = mysql_query("SELECT agent_agreement.*,country.* FROM agent_agreement 
LEFT JOIN country
ON agent_agreement.agree_country = country.country_id
where agent_id=".$_GET['agent']."");
while($agreerow1=mysql_fetch_array($agreerow))
{
echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$agreerow1['agree_id']."' ></td> ";
echo'<td>'.$agreerow1['country_name'].'</td>';
echo'<td>'.$agreerow1['agree_ppt'].'</td>';
echo'<td>'.$agreerow1['agree_ftw'].'</td>';
echo'<td>'.$agreerow1['agree_contract'].'</td>';
echo'<td>'.$agreerow1['agree_deployed'].'</td>';
echo'</tr>';
}

?>

</tbody>
</table>

<div class="row">
<div class="col-6" style="width:100%,float:right">		

<a href="delete_methode_link" onclick="return confirm('Are you sure you want to Remove?');">
<input type="submit" value="Delete" name="delete_orders" style="width:130px;background:blue;padding:5px;margin-left:20px;margin-top:-1px;color:white">
</a>
</div>
</div>

</form>	











</div>
</div>
</div>  
<div style="clear:both"></div>			
</div>			









</div>
</div>
<!-- /.col -->
</div>
<!-- /.row -->


</section>
</div>
</div>
</div>
</div>
</div>
</div>
<!-- /page content -->


</div>

</body>