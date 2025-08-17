<?php
session_start();
if(!isset($_SESSION['staff'])){
header("location:logout.php");
}
include'tracking.php';
include'styles.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<title>Accounting System</title>

<!-- Bootstrap -->
<link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
<!-- Custom Theme Style -->
<link href="../build/css/custom.min.css" rel="stylesheet">
<!-- Datatable js -->

<!-- jQuery: required (tablesorter works with jQuery 1.2.3+) -->
<script src="docs/js/jquery-1.2.6.min.js"></script>

<!-- Pick a theme, load the plugin & initialize plugin -->
<link href="dist/css/theme.default.min.css" rel="stylesheet">
<script src="dist/js/jquery.tablesorter.min.js"></script>
<script src="dist/js/jquery.tablesorter.widgets.min.js"></script>
<script>
$(function(){
$('table').tablesorter({
widgets        : ['zebra', 'columns'],
usNumberFormat : false,
sortReset      : true,
sortRestart    : true
});
});
</script>

</head>

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


<form id="demo-form2" data-parsley-validate class="form-horizontal form-label-left" method="post">


<div class="form-group">
<div class="col-md-4 col-sm-4 col-xs-4 form-group">
<?php
include'r/db.php';
include'r/functions8.php';

echo'<select class="form-control" name="fra" onchange="showapplicant(this.value)" required>';
echo'<option value=""> -Select FRA-</option>';
$emp1111 = mysql_query("SELECT * FROM employer ORDER BY employer_name asc");
while($emp1112=mysql_fetch_array($emp1111))
{
echo'<option value="'.$emp1112["employer_id"].'">  '.$emp1112["employer_name"].'</option>';
}
ECHO'</select>';
?>


</div>

<div class="col-md-6 col-sm-12 col-xs-12 form-group">
<button type="submit" class="btn btn-info"  name="Search" >Search FRA</button>

</div>
</form>       

<div style="clear:both"></div>





<div class="row">




<div class="col-md-12 col-xs-12">


<div class="x_panel">

<h2>FRA REMITTANCE<SMALL><a href="paid-app.php" style="color:white;font-size:10px;background:green;padding:4px"></a></SMALL></h2>

<p style="color:black">" Note: Enter Remittance Agreement to <i style="color:red">FRA (<?=$_GET['test']?>)</i> "</p>

<form action="" method="post">
<a href="delete_methode_link" onclick="return confirm('Are you sure you want to UPDATE the amount?');">
<input type='submit'class="btn btn-lg btn-danger" name='updatedata1' value='Update Data' style="width:140px;height:40px;float:right;margin-right:180px;border:2px solid white">
</a>
<input type="hidden" value="<?=$_POST['fra']?>" name="fra">

<div class="x_content">


<a href="otherspaid3.php?test=Regular">Regular</a> | <a href="otherspaid-driver.php?test=Driver">Driver</a>  | <a href="otherspaid-direct.php?test=Direct">Direct</a>



<table  class="tablesorter"  id="example-checkbox">
<thead>
<tr>
<th STYLE="width:2%"></th>
<th>DATE</th>
<th>Name</th>

<th>Passport</th>
<th>Country</th>
<th>Principal</th>
<th>Agent</th>
<th>Position</th>
<th>Date Medical</th>	
<th>Result</th>
<th>w/ visa</th>
<th>w/ Contract</th>
<th>Deployed</th>
<th>Repat</th>
<th>Status</th>
<th>VISA ($)</th>
<th>CONTRACT ($)</th>
<th>DEPLOYED ($)</th>

<th></th>

</tr>
</thead>
<tbody>
<?php


$app = mysql_query("SELECT * FROM applicant
WHERE applicant_paid!=1
AND applicant_preferred_position  IN (7)
and applicant_source!=89
ORDER BY applicant_id desc



LIMIT 50 ");

if(isset($_POST['Search']))
{
$app = mysql_query("SELECT * FROM applicant
where
applicant_employer=".$_POST['fra']."
AND applicant_preferred_position  IN (7)
and applicant_source!=89
ORDER BY applicant_id desc");




				$aff = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed) as singils ,applicant.* FROM applicant
			WHERE applicant_employer=".$_POST['fra']."");
			$rowre=mysql_fetch_array($aff);	

			$app1 = mysql_query("SELECT SUM(amount) as USD ,emp_id,currency FROM email_address
			WHERE emp_id=".$_POST['fra']." AND currency='USD'");
			$usd=mysql_fetch_array($app1);


			$unfit = mysql_query("SELECT SUM(receivable) as receive ,emp_id,receivable FROM email_address4
			WHERE emp_id='".$_POST['fra']."'  AND currency='PHP'");
			$unfitrow=mysql_fetch_array($unfit);

			$CON = mysql_query("SELECT SUM(receivable) as receiveusd ,emp_id,receivable FROM email_address4
			WHERE emp_id='".$_POST['fra']."'  AND  currency='USD' ");
			$contract=mysql_fetch_array($CON);

			//$result1 = mysql_query("SELECT count(emp_id) as alls FROM  email_address2 where emp_id='".$row['employer_id']."'");
			//$all = mysql_fetch_array($result1);


			$col = mysql_query("SELECT SUM(amount) as amountusd ,emp_id,amount FROM email_address6
			WHERE emp_id='".$_POST['fra']."'  AND  currency='USD' and status_collect=0");
			$colusd=mysql_fetch_array($col);


			$deduction = mysql_query("SELECT SUM(amount) as amountusd ,emp_id,amount FROM email_address6
			WHERE emp_id='".$_POST['fra']."'  AND  currency='USD' and status_collect=1");
			$deduction1=mysql_fetch_array($deduction);

			$oldcollect = mysql_query("SELECT SUM(amount) as amountusd ,emp_id,amount FROM email_address6
			WHERE emp_id='".$_POST['fra']."'  AND  currency='USD' and status_collect=2");
			$oldcollect1=mysql_fetch_array($oldcollect);


			$expnese88= mysql_query("SELECT SUM(amount/50) as totalamount ,email_address.* FROM email_address
			WHERE emp_id='".$_POST['fra']."'  AND  currency='PHP' AND request_payment=3");
			$expnese881=mysql_fetch_array($expnese88);



			$oldme8 = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed) AS amount,applicant.* FROM applicant
			WHERE  applicant_employer=".$_POST['fra']." AND   applicant_status NOT IN (25,1,21,14,22)   ");
			$oldme81=mysql_fetch_array($oldme8);


			//$colS = mysql_query("SELECT SUM(amount) as amounPHP ,emp_id,amount FROM email_address6
			//WHERE emp_id='".$row["employer_id"]."'  AND  currency='PHP' ");
			//$colphp=mysql_fetch_array($colS);

			$backout = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed) AS amount,applicant.*  FROM applicant
			WHERE applicant_status IN (25,1,21,14) AND applicant_employer=".$_POST['fra']." ");
			$backout1=mysql_fetch_array($backout);


			$repatresult = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed) AS amount,applicant.*,deployed.* FROM applicant
			LEFT JOIN deployed
			ON applicant.applicant_id = deployed.deployed_applicant
			WHERE applicant_status  IN (22) AND applicant_employer=".$_POST['fra']."
			AND deployed_date between date_sub(now(), interval 91 day)  AND date_add(now(), interval 1 day)");
			$repatresult1=mysql_fetch_array($repatresult);

			$singil=$expnese881["totalamount"]+$oldcollect1["amountusd"]+$oldme81["amount"];
			$alldeduct=$colusd["amountusd"]+$deduction1["amountusd"]+$backout1["amount"]+$repatresult1["amount"];
			$balancesheet=$singil-$alldeduct;

	echo'<h3 style="color:Red;font-size:14px"> Balance: ';   echo number_format($balancesheet,2);  echo'</h3>';


}

if(isset($_POST['updatedata1']))
{
$app = mysql_query("SELECT * FROM applicant
where
applicant_employer=".$_POST['fra']."
AND applicant_preferred_position  IN (7)
and applicant_source!=89
ORDER BY applicant_id desc");




				$aff = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed) as singils ,applicant.* FROM applicant
			WHERE applicant_employer=".$_POST['fra']."");
			$rowre=mysql_fetch_array($aff);	

			$app1 = mysql_query("SELECT SUM(amount) as USD ,emp_id,currency FROM email_address
			WHERE emp_id=".$_POST['fra']." AND currency='USD'");
			$usd=mysql_fetch_array($app1);


			$unfit = mysql_query("SELECT SUM(receivable) as receive ,emp_id,receivable FROM email_address4
			WHERE emp_id='".$_POST['fra']."'  AND currency='PHP'");
			$unfitrow=mysql_fetch_array($unfit);

			$CON = mysql_query("SELECT SUM(receivable) as receiveusd ,emp_id,receivable FROM email_address4
			WHERE emp_id='".$_POST['fra']."'  AND  currency='USD' ");
			$contract=mysql_fetch_array($CON);

			//$result1 = mysql_query("SELECT count(emp_id) as alls FROM  email_address2 where emp_id='".$row['employer_id']."'");
			//$all = mysql_fetch_array($result1);


			$col = mysql_query("SELECT SUM(amount) as amountusd ,emp_id,amount FROM email_address6
			WHERE emp_id='".$_POST['fra']."'  AND  currency='USD' and status_collect=0");
			$colusd=mysql_fetch_array($col);


			$deduction = mysql_query("SELECT SUM(amount) as amountusd ,emp_id,amount FROM email_address6
			WHERE emp_id='".$_POST['fra']."'  AND  currency='USD' and status_collect=1");
			$deduction1=mysql_fetch_array($deduction);

			$oldcollect = mysql_query("SELECT SUM(amount) as amountusd ,emp_id,amount FROM email_address6
			WHERE emp_id='".$_POST['fra']."'  AND  currency='USD' and status_collect=2");
			$oldcollect1=mysql_fetch_array($oldcollect);


			$expnese88= mysql_query("SELECT SUM(amount/50) as totalamount ,email_address.* FROM email_address
			WHERE emp_id='".$_POST['fra']."'  AND  currency='PHP' AND request_payment=3");
			$expnese881=mysql_fetch_array($expnese88);



			$oldme8 = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed) AS amount,applicant.* FROM applicant
			WHERE  applicant_employer=".$_POST['fra']." AND   applicant_status NOT IN (25,1,21,14,22)   ");
			$oldme81=mysql_fetch_array($oldme8);


			//$colS = mysql_query("SELECT SUM(amount) as amounPHP ,emp_id,amount FROM email_address6
			//WHERE emp_id='".$row["employer_id"]."'  AND  currency='PHP' ");
			//$colphp=mysql_fetch_array($colS);

			$backout = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed) AS amount,applicant.*  FROM applicant
			WHERE applicant_status IN (25,1,21,14) AND applicant_employer=".$_POST['fra']." ");
			$backout1=mysql_fetch_array($backout);


			$repatresult = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed) AS amount,applicant.*,deployed.* FROM applicant
			LEFT JOIN deployed
			ON applicant.applicant_id = deployed.deployed_applicant
			WHERE applicant_status  IN (22) AND applicant_employer=".$_POST['fra']."
			AND deployed_date between date_sub(now(), interval 91 day)  AND date_add(now(), interval 1 day)");
			$repatresult1=mysql_fetch_array($repatresult);

			$singil=$expnese881["totalamount"]+$oldcollect1["amountusd"]+$oldme81["amount"];
			$alldeduct=$colusd["amountusd"]+$deduction1["amountusd"]+$backout1["amount"]+$repatresult1["amount"];
			$balancesheet=$singil-$alldeduct;

	echo'<h3 style="color:Red;font-size:14px"> Balance: ';   echo number_format($balancesheet,2);  echo'</h3>';



}







while($row=mysql_fetch_array($app))
{
$status = mysql_query("SELECT * FROM statuses where number=".$row['applicant_status']." ");
$srow=mysql_fetch_array($status);

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
$row1=mysql_fetch_array($app1);


$req = mysql_query("SELECT * FROM applicant_requirement where requirement_applicant=".$row['applicant_id']." ");
$row2=mysql_fetch_array($req);


$cert = mysql_query("SELECT * FROM applicant_certificate where certificate_applicant=".$row['applicant_id']." ");
$cert1=mysql_fetch_array($cert);



$deployed = mysql_query("SELECT * FROM deployed where deployed_applicant=".$row['applicant_id']." order by deployed_id desc");
$deployed1=mysql_fetch_array($deployed);



$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['applicant_employer']." ");
$emprow=mysql_fetch_array($emp);

$passport = mysql_query("SELECT * FROM applicant_passport where passport_applicant=".$row['applicant_id']." ");
$passportrow=mysql_fetch_array($passport);

$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);

$LIQ = mysql_query("SELECT sum(amount) as cashfund FROM  email_address6 
where  app_id=".$row['applicant_id']."");
$cashliq=mysql_fetch_array($LIQ);


$position = mysql_query("SELECT * FROM position where position_id=".$row['applicant_preferred_position']." ");
$position1=mysql_fetch_array($position);


$balancecollection=$row['singil']-$cashliq['cashfund'];

if($row['applicant_paid']!=1)
{ $paids='UNPAID'; }  

if($row['applicant_paid']==1)
{ $paids='PAID'; } 


//STATUS BACKGROUND
if($srow["status"]=="Cancelled" || $srow["status"]=="cancelled" || $srow["status"]=="Repat")
{
$color111="red";
$color21="white";
}
if($srow["status"]!="Cancelled" || $srow["status"]!="cancelled" || $srow["status"]!="REPAT"  || $srow["status"]!="Repat")
{
$color111="";
$color21="";   
}

$agree = mysql_query("SELECT * FROM employer where employer_id=".$row['applicant_employer']." ");
$agree1=mysql_fetch_array($agree);

$date_addedded=date_create("".$deployed1['deployed_date']."");
$datecur=date_create("".$row['repat_date']."");
$diff=date_diff($date_addedded,$datecur);

$date_deployed=date_create("".$deployed1['deployed_date']."");
$datecur1=date_create("".$date2."");
$diff1=date_diff($date_deployed,$datecur1);
$dayone=$diff1->format("%r%a");

echo'<tr style="background:'.$color111.';color:'.$color21.'">';
echo "<td><input type='hidden' name='applicant_id[]' value='".$row['applicant_id']."' ></td> ";
echo'<td>'; echo date('Y-M-d', strtotime($row['applicant_date_applied']));echo'</td>';
echo'<td><b>'.$row["applicant_first"].' '.$row["applicant_last"].'</b></td>';
echo'<td>'.$passportrow["passport_number"].'</td>';

echo'<td>'.$countryrow["country_name"].'</td>';
echo'<td>'.$emprow["employer_name"].'</td>';
echo'<td style="color:black;font-size:12px">'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td>'.$position1["position_name"].'</td>';



echo"<td >".$row['certificate_medical_exam_date']."</td>";
echo"<td STYLE='color:black;font-size:9px;COLOR:rED'>".$row['certificate_medical_result']."</td>";


//visa----------------------------------------------------------


IF($row2['requirement_visa_stamp']=='1970-01-01' || $row2['requirement_visa_stamp']=='0000-00-00'   ) {
echo"<td style='font-size:9px'>NO Result</td>";
		//agree
		if ($row['fra_ftw']==0){
		$ftwfinal=0;
		$border1="border:0px solid red";		
		}

		if ($row['fra_ftw']!=0){
	    	$ftwfinal=$row['fra_ftw'];
		$border1="";	
		}	
}
IF($row2['requirement_visa_stamp']!='0000-00-00' && $row2['requirement_visa_stamp']!='1970-01-01' )  {
echo"<td >".$row2['requirement_visa_stamp']." <br> Visa NO. ".$row2['requirement_visa_no']." </td>";
		//agree
		if ($row['fra_ftw']==0  && $row['fra_deployed']==0){
		
		        if($srow["status"]=="Cancelled" || $srow["status"]=="cancelled" || $srow["status"]=="Repat")
                    { 
                    
                        $ftwfinal=0;
                        $border1="border:0px solid red";
                    }
                    
                     ELSE
                    { 
                    
                        $ftwfinal=$agree1['agree_driver3'];
                        $border1="border:1px solid red";
                    }
		
		
		}

		ELSE {
		$ftwfinal=$row['fra_ftw'];
		$border1="";	
		}
		
}


//contract----------------------------------------------------------

IF($row2['requirement_contract']=='1970-01-01' || $row2['requirement_contract']=='0000-00-00'   ) {
echo"<td style='font-size:9px'>NO Result</td>";
			//agreement
			if ($row['fra_visa']==0 ){
			$visafinal=0;
			$border2="border:0px solid red";		
			}
			if ($row['fra_visa']!=0){
			$visafinal=$row['fra_visa'];
			$border2="";	
			}

}
else{ 
echo"<td >".$row2['requirement_contract']." </td>";
		//agreement
		if ($row['fra_visa']==0  && $row['fra_deployed']==0){
	
		
		        if($srow["status"]=="Cancelled" || $srow["status"]=="cancelled" || $srow["status"]=="Repat")
                    { 
                    
                        $visafinal=0;
                        $border2="border:0px solid red";
                    }
                    
                     ELSE
                    { 
                    
                        $visafinal=$agree1['agree_driver1'];
                        $border2="border:1px solid red";
                    }
		
		
		}


		ELSE {
		$visafinal=$row['fra_visa'];
		$border2="";	
		}
		
	

}





//deployed----------------------------------------------------------
IF($row['applicant_status']!=9 ) {
			//agreement
			if 	($row['fra_deployed']==0){
			$deployedfinal=0;
			$border3="border:0px solid red";		
			}

			if ($row['fra_deployed']!=0){
			$deployedfinal=$row['fra_deployed'];
			$border3="";	
			}

echo"<td>--</td>";
}


IF($row['applicant_status']==9 ) {
$reqdeployed="";   
				//agreement
				if 	($row['fra_deployed']==0 && $row['applicant_status']==9 && ($agree1['numberdays'] <=  $dayone )){
				$deployedfinal=$agree1['agree_driver2'];
				$border3="border:1px solid red";		
				}

				if ($row['fra_deployed']!=0){
				$deployedfinal=$row['fra_deployed'];
				$border3="";	
				}


echo"<td >".$deployed1['deployed_date']."</br><i style='color:black'>"; echo $diff1->format("%r%a days");   echo" </i></td>";
}



IF($row['repat_date']=='1970-01-01' || $row['repat_date']=='0000-00-00' || $row['repat_date']=='' || $row['repat_date']=='0000-00-00 00:00:00'  ) {
echo"<td >---</td>";
}
IF($row['repat_date']!='0000-00-00') {
echo"<td >".$row['repat_date']." </br></br>  <i style='color:black'>"; echo $diff->format("%r%a days");   echo" </i></td>";
}











echo "<td>
<select name='applicant_paid[]'>
<option value='".$row["applicant_paid"]."'>".$paids."</option>
<option value=1>PAID</option>
<option value=0>unpaid</option>
</SELECT>

</td> ";

echo "<td><input type='NUMBER' name='fra_ftw[]' value='".$ftwfinal."' placeholder='Visa' style='width:70px;".$border1."'  readonly> </br>".$agree1['agree_ftw']." -agree</td> ";
echo "<td><input type='NUMBER' name='fra_visa[]' value='".$visafinal."' placeholder='Contract'  style='width:70px;".$border2."'  readonly></br>".$agree1['agree_driver1']." -agree</td> ";
echo "<td><input type='NUMBER' name='fra_deployed[]' value='".$deployedfinal."' placeholder='Deployed' style='width:70px;".$border3."' readonly></br>".$agree1['agree_driver2']." -agree</td> ";

echo'<td>
<a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">
<button type="button" class="btn btn-'.$srow["statusColors"].' btn-xs" style="font-size:9px">'.$srow["status"].'</button></a></td>';
echo'</tr>';
}
?>
</tbody>
</table>

</br></br>


</form>	





<div class="ln_solid"></div>

</div>
</div>
</div>  




</div>			





<!-- /footer content -->
</div>
</div>


</body>
</html>


<!-- Modal -->
<div class="modal fade" id="myModal" role="dialog">
<div class="modal-dialog">

<!-- Modal content-->
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal">&times;</button>
<h4 class="modal-title">New Applicant</h4>
</div>
<div class="modal-body">

<form class="form-horizontal form-label-left input_mask" method="post" action="">	

<input type="hidden" class="form-control" name="status" value="Available">

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">DATE APPLIED</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="date" class="form-control" name="date_applied" value="<?=$date1?>" required>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">FIRST NAME</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="name" required>
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">MIDDLE NAME</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="mname" required>
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">LAST NAME</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="lname" required>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">PASSPORT #</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="passport" required>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12"> DATE EXPIRED</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="date" class="form-control" name="ppt_expired" required>
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Contact #</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="contact" required>
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">COUNTRY</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<select class="form-control" required  name="country" required>
<option><?=$row1['country']?></option>
<option>KSA</option>
<option>KUWIAT</option>
<option>QATAR</option>
<option>OMAN</option>
</select>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">AGENT</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<select class="form-control" required  name="agent_id" required>
<option>-----</option>
<?php
$usertype = mysql_query("SELECT * FROM recruitment_agent ORDER BY agent_first asc");

while($rows=mysql_fetch_array($usertype))
{
echo'<option value="'.$rows["agent_id"].'">'.$rows["agent_first"].' '.$rows["agent_last"].'</option>';
}
?>
</select>
</div>
</div>


<div class="form-group">
<div class="col-md-12 col-sm-9 col-xs-12">	
<button type="submit" class="btn btn-danger pull-right"  name="add_applicants">Save</button>
</div>
</div>


</form>		  



</div>

</div>

</div>
</div>
