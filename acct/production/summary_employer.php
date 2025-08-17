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
include'styles.php';
//$app = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$_GET['agent']." ");
//$row=mysql_fetch_array($app);
?>

<title>Principal Summary</title>

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
padding:1px;
font-size:12px;
font-family:calibri;
}
.table-fixed td,th{
font-size:11px;
padding:1px;
}gent_i


</style>

<body style="background:white">
<div id="wrapper">

<div>
<h1 style="font-size:30px;color:black;text-align:center;"><?=$row['agent_first']?> <?=$row['agent_last']?> </h1>

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
			
			
		
		
			
			
			
			
		</div>
			
</BR>
			
<!-- Table row -->
<div class="row">
<div class="col-xs-12 table">

<?php
$fafa = mysql_query("SELECT * FROM employer where employer_id=".$_GET['emp_id']." ");
$fafaemployer=mysql_fetch_array($fafa);
?>


<table id="customers">
<tr>
<td><b>Code # :</b> 000<?=$fafaemployer["employer_id"]?> </td>
<td><b>FRA Name :  </b><?=$fafaemployer["employer_name"]?></td>
<td><b>Agreement :  </b><?=$fafaemployer["employer_remarks"]?></td>

</tr>
</table>

<h2 style="color:Red">Applicants</h2>
<table class="table table-fixed">
<thead>
<tr>
<th>#</th>
<th>Status</th>
<th>Date Applied</th>
<th>Applicant</th>
<th>Direct Employer</th>
<th>Status Date</th>
<th>DEBIT</th>
<th>CREDIT</th>
<th>Balance</th>
			</tr>
			
			</thead>
			<tbody>
				<?PHP
$unfitz = mysql_query("SELECT SUM(receivable) as receive ,emp_id,receivable,app_id
FROM email_address4
WHERE emp_id='".$_GET["emp_id"]."'   AND currency='PHP' and type_status='Charge to Employer'");
$unfitrowz=mysql_fetch_array($unfitz);

$CONz = mysql_query("SELECT SUM(receivable) as receiveusd ,emp_id,receivable,app_id 
FROM email_address4
WHERE emp_id='".$_GET["emp_id"]."'  AND  currency='USD' and type_status='Charge to Employer' ");
$contractz=mysql_fetch_array($CONz);
		

$colz = mysql_query("SELECT SUM(amount) as amountusd ,emp_id,amount FROM email_address6
WHERE emp_id='".$_GET["emp_id"]."'   AND  currency='USD' ");
$colusdz=mysql_fetch_array($colz);

$colSz = mysql_query("SELECT SUM(amount) as amounPHP ,emp_id,amount FROM email_address6
WHERE emp_id='".$_GET["emp_id"]."'   AND  currency='PHP' ");
$colphpz=mysql_fetch_array($colSz);


//$app1 = mysql_query("SELECT SUM(amount) as USD ,emp_id,currency FROM email_address
//WHERE emp_id=".$row["emp_id"]." AND currency='USD'");
//$usd=mysql_fetch_array($app1);

$app2 = mysql_query("SELECT SUM(amount) as PHP2 ,emp_id,currency FROM email_address
WHERE emp_id='".$_GET["emp_id"]."' AND currency='PHP'");
$php=mysql_fetch_array($app2);
				
//AND applicant_status IN(4,12,9,8)				

$app = mysql_query("SELECT * FROM applicant 
where  applicant_employer=".$_GET['emp_id']."

AND applicant_paid!=1
order by applicant_date_applied desc");

$count=1;
				
				while($row=mysql_fetch_array($app))
				{
$status = mysql_query("SELECT * FROM statuses where number=".$row['applicant_status']." ");
$srow=mysql_fetch_array($status);


$col = mysql_query("SELECT SUM(amount) as amountusd ,agent_id,amount,app_id
FROM email_address6
WHERE app_id='".$row["applicant_id"]."'  AND  currency='USD' ");
$colusd=mysql_fetch_array($col);





$colS = mysql_query("SELECT SUM(amount) as amounPHP ,agent_id,amount,app_id
FROM email_address6
WHERE app_id='".$row["applicant_id"]."'  AND  currency='PHP' ");
$colphp=mysql_fetch_array($colS);


$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['applicant_employer']." ");
$emprow=mysql_fetch_array($emp);

$line = mysql_query("SELECT * FROM applicants_logs_view where log_applicant=".$row['applicant_id']." ORDER BY log_id DESC LIMIT 1 ");
$lineup=mysql_fetch_array($line);

$totalpayable=$row["singil"]+$totalpayable;
$totalpaid=$colusd["amountusd"]+$totalpaid;
$totalbalance=$totalpayable-$totalpaid;

echo'<tr>';
echo'<td>'.$count.'</td>';
echo'<td><button type="button" class="btn btn-'.$srow["statusColors"].' btn-xs" style="font-size:10px">'.$srow["status"].'</button></td>';

echo'<td>'; echo date('Y-M-d', strtotime($row['applicant_date_applied']));echo'</td>';
echo'<td><B>'.$row["applicant_first"].' '.$row["applicant_last"].'</B></td>';

echo'<td>'.$row["sub_employer"].'</td>';
echo"<td>";
echo "" .$lineup['log_date'];
echo"</td>";

echo'<td style="color:blue">$'.number_format($row["singil"],2).' </td>';
echo'<td style="color:BLACK">$'.number_format($colusd["amountusd"],2).'</td>';
echo'<td style="color:red">'.number_format($row["singil"]-$colusd["amountusd"],2).'</td>';

$count++;
echo'</tr>';
				
}


				
				?>	
<tr>
<td colspan="10" STYLE="color:BLUE;font-size:18px;TEXT-ALIGN:RIGHT">TOTAL PAYABLE : <?php echo'$'.number_format($totalpayable,2).'';?></td>
</tr>
<tr>
<td colspan="10" STYLE="color:BLACK;font-size:18px;TEXT-ALIGN:RIGHT">TOTAL PAID : <?php echo'$'.number_format($totalpaid,2).'';?></td>
</tr>
<tr>
<td colspan="10" STYLE="color:BLACK;font-size:18px;TEXT-ALIGN:RIGHT">________________________________</td>
</tr>
<tr>
<td colspan="10" STYLE="color:RED;font-size:18px;TEXT-ALIGN:RIGHT"> BALANCE : <?php echo'$'.number_format($totalbalance,2).'';?></td>
</tr>
				
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