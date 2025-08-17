<!DOCTYPE html>
<html lang="en">
  <head>
      <meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
   


  </head>
<?php
include'inc/functions.php';
$app = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$_GET['agent']." ");
$row=mysql_fetch_array($app);
?>

<title>Agent Summary</title>

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
font-size:11px;
font-family:calibri;
}
.table-fixed td,th{
font-size:11px;
padding:2px;
}gent_i


</style>

<body style="background:white">
<div id="wrapper">

<div>
<h1 style="font-size:30px;color:black;text-align:center;"><?=$row['agent_first']?> <?=$row['agent_last']?> </h1>




<?php
$allapplicant = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$_GET['agent']."'");
$allapplicant1 = mysql_fetch_array($allapplicant);


$onprocess = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$_GET['agent']."' 
AND applicant_status IN (8,12,4,3,2,5)
");
$onprocess1 = mysql_fetch_array($onprocess);

$deployed = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$_GET['agent']."' 
AND applicant_status IN (9)
");
$deployed1 = mysql_fetch_array($deployed);


$repat = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$_GET['agent']."' 
AND applicant_status IN (22)
");
$repat1 = mysql_fetch_array($repat);


$pass = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$_GET['agent']."' 
AND applicant_status IN (15,0,10)
");
$pass1 = mysql_fetch_array($pass);


$not = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$_GET['agent']."' 
AND applicant_status IN (1,14,21,7)
");
$not1 = mysql_fetch_array($not);


?>


<ul class="stats-overview">
<li>
<span class="name">Total Applicants</span>
<span class="value text-success"><?=$allapplicant1['countme']?>  </span>
</li>

<li>
<span class="name">Available / Passporting</span>
<span class="value text-success"><?=$pass1['countme']?>  </span>
</li>

 <li>   
<span class="name"> Backout/Cancelled/Unfit</span>
<span class="value text-success"> <?=$not1['countme']?> </span>
</li>


</ul>



<ul class="stats-overview">
 <li>   
<span class="name"> On Process Applicants</span>
<span class="value text-success"> <?=$onprocess1['countme']?> </span>
</li>

<li class="hidden-phone">
<span class="name"> Deployed Applicants</span>
<span class="value text-success"> <?=$deployed1['countme']?> </span>
</li>

<li>
<span class="name"># Repat Applicants</span>
<span class="value text-success"> <?=$repat1['countme']?> </span>
</li>

</ul>




<ul class="stats-overview" style="display:none">

<li>
<span class="name"> Total Credit / Repatration </span>
<span class="value text-success">  </span>
</li>
<li class="hidden-phone">
<span class="name"> For Release Amount</span>
<span class="value text-success">  </span>
</li>


</ul>
<br />



</div>

<!-- page content -->
<div class="right_col" role="main">
<div class="">


<div class="clearfix"></div>

<div class="row">
<div class="col-md-12">
<div class="x_panel">

<div class="x_content">

<section class="content invoice">
<div class="row invoice-info">




<div class="col-sm-2 invoice-col pull-right">
<a href="print.php?agent=<?=$_GET['agent']?>" style="COLOR:white;font-size:16px">Print</a>

</div>





</div>

</BR>

<!-- Table row -->
<div class="row">
<div class="col-xs-12 table">
<h2 style="color:Red">Applicants</h2>
<table class="table table-fixed">
<thead>
<tr>
<th>#</th>
<th>Status</th>
<th>Date Applied</th>
<th>Applicant</th>
<th>USD COM</th>
<th>PHP COM</th>

</tr>

</thead>
<tbody>
<?PHP
$unfitz = mysql_query("SELECT SUM(receivable) as receive ,agent_id,receivable,app_id
FROM email_address4
WHERE agent_id='".$_GET["agent"]."'   AND currency='PHP' and type_status='charge'");
$unfitrowz=mysql_fetch_array($unfitz);

$CONz = mysql_query("SELECT SUM(receivable) as receiveusd ,agent_id,receivable,app_id 
FROM email_address4
WHERE agent_id='".$_GET["agent"]."'  AND  currency='USD' and type_status='charge' ");
$contractz=mysql_fetch_array($CONz);


$colz = mysql_query("SELECT SUM(amount) as amountusd ,agent_id,amount FROM email_address6
WHERE agent_id='".$_GET["agent"]."'   AND  currency='USD' ");
$colusdz=mysql_fetch_array($colz);

$colSz = mysql_query("SELECT SUM(amount) as amounPHP ,agent_id,amount FROM email_address6
WHERE agent_id='".$_GET["agent"]."'   AND  currency='PHP' ");
$colphpz=mysql_fetch_array($colSz);


$app1 = mysql_query("SELECT SUM(amount) as USD ,agent_id,currency FROM email_address
WHERE agent_id=".$row["agent_id"]." AND currency='USD'");
$usd=mysql_fetch_array($app1);

$app2 = mysql_query("SELECT SUM(amount) as PHP2 ,agent_id,currency FROM email_address
WHERE agent_id='".$_GET["agent"]."' AND currency='PHP'");
$php=mysql_fetch_array($app2);



$app = mysql_query("SELECT * FROM applicant where  applicant_source=".$_GET['agent']."
order by applicant_date_applied desc");

$count=1;

while($row=mysql_fetch_array($app))
{
$status = mysql_query("SELECT * FROM statuses where number=".$row['applicant_status']." ");
$srow=mysql_fetch_array($status);

$col = mysql_query("SELECT SUM(amount) as totals,app_id,amount,currency,agent_id 
FROM email_address where  app_id=".$row['applicant_id']."
AND currency='USD'");
$rowusd=mysql_fetch_array($col);

$col1 = mysql_query("SELECT SUM(amount) as totals,app_id,amount,currency,agent_id 
FROM email_address where  app_id=".$row['applicant_id']."
AND currency='PHP'");
$rowphp=mysql_fetch_array($col1);

echo'<tr>';
echo'<td>'.$count.'</td>';
echo'<td><button type="button" class="btn btn-'.$srow["statusColors"].' btn-xs">'.$srow["status"].'</button></td>';

echo'<td>'; echo date('Y-M-d', strtotime($row['applicant_date_applied']));echo'</td>';
echo'<td><B>'.$row["applicant_first"].' '.$row["applicant_last"].'</B></td>';
echo'<td>'.$rowusd["totals"].'</td>';
echo'<td>'.$rowphp["totals"].'</td>';



$count++;
echo'</tr>';

}

?>	
<tr style="border-top:1px solid gray">
<td style="color:black;text-align:left;font-size:15px;font-weight:bold">TOTAL:</td>
<td style="color:black;text-align:right;font-size:15px;font-weight:bold" colspan="3"></td>
<td style="color:black;text-align:left;font-size:15px;font-weight:bold"><?php echo number_format($usd['USD'],2);?></td>
<td style="color:black;text-align:left;font-size:15px;font-weight:bold"><?php echo number_format($php['PHP2'],2);?></td>



</tbody>
</table>

</BR></BR>







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