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
<!-- jQuery -->
<script src="../vendors/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>


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




<div style="clear:both"></div>




<form id="demo-form2" data-parsley-validate class="form-horizontal form-label-left" method="post">

<div class="form-group">
<div class="col-md-4 col-sm-4 col-xs-4 form-group">
<?php
include'r/db.php';
include'r/functions.php';


echo'<select class="form-control" name="agent_id" required>
<option></option>';

$usertype = mysql_query("SELECT * FROM recruitment_agent where agent_type='Agent' ORDER BY agent_first asc");
echo'<optgroup label="Agent">';
while($rows=mysql_fetch_array($usertype))

{
echo'<option value="'.$rows["agent_id"].'"> '.$rows["agent_first"].' '.$rows["agent_last"].'</option>';
}
echo'</optgroup>';


$sup = mysql_query("SELECT * FROM  recruitment_agent where agent_type='Branch' ORDER BY agent_first asc");
echo'<optgroup label="Branch">';
while($sup1=mysql_fetch_array($sup))
{
echo'<option value="'.$sup1["agent_id"].'"> '.$sup1["agent_first"].' '.$sup1["agent_last"].'</option>';
}
echo'</optgroup>';
ECHO'</select>';
?>


</div>

<div class="col-md-6 col-sm-12 col-xs-12 form-group">
<button type="submit" class="btn btn-info"  name="Search1" >Search AGENT</button>

</div>
</form> 




<div class="row">




<div class="col-md-12 col-xs-12">


<div class="x_panel">

<h2>AGENT COMMISSION <SMALL>Staff Entry</SMALL></h2>

<p style="color:black">" Note: Enter commssion Agreement to <i style="color:red">Agent</i> "</p>

<form action="" method="post">

<input type="hidden" value="<?=$_POST['agent_id']?>" name="agent_id">
<a href="delete_methode_link" onclick="return confirm('Are you sure you want to UPDATE the amount?');">
<input type='submit'class="btn btn-lg btn-danger" name='updatedata1' value='Update Data' style="width:140px;height:40px;float:right;margin-right:180px;border:2px solid white">
</a>
<div class="x_content">








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
<th>w/ Contract</th>
<th>Deployed</th>
<th>Status</th>
<th>PPT(₱)</th>
<th>FTW/LINE UP (₱)</th>
<th>Sign Contract (₱)</th>
<th>DEPLOYED (₱)</th>
<th>Total</th>
<th>Deduction ($)</th>
<th>Paid ($)</th>
<th style="color:Red">Balance ($)</th>
<th>Status</th>


</tr>
</thead>
<tbody>
<?php


$app = mysql_query("SELECT * FROM applicant
WHERE applicant_paid!=1
ORDER BY applicant_id desc  limit 1");



if(isset($_POST['updatedata1']))
{
$app = mysql_query("SELECT * FROM applicant
where
applicant_source=".$_POST['agent_id']."");



}


if(isset($_POST['Search1']))
{
$app = mysql_query("SELECT * FROM applicant
where
applicant_source=".$_POST['agent_id']."");



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



$LIQ = mysql_query("SELECT sum(amount) as cashfund FROM  email_address6 
where  app_id=".$row['applicant_id']."");
$cashliq=mysql_fetch_array($LIQ);


$position = mysql_query("SELECT * FROM position where position_id=".$row['applicant_preferred_position']." ");
$position1=mysql_fetch_array($position);


$balancecollection=$row['singil']-$cashliq['cashfund'];



$paidamount = mysql_query("SELECT SUM(l_amount) AS totalpaidme, e_liq.*, email_address.* FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
where  liq_app=".$row['applicant_id']."
AND l_status=0 
AND request_payment=3
");
$paidamount1=mysql_fetch_array($paidamount);


if($row['applicant_paid1']!=1)
{ $paids='UNPAID'; }  

if($row['applicant_paid1']==1)
{ $paids='PAID'; } 


if($row["applicant_ex"]=="FIRSTIMER")
{
    $color111="";
    $color21="#CD5C5C";
}
else{
  $color111="";
    $color21="";   
}


$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);

$agree = mysql_query("SELECT * FROM agent_agreement where agent_id=".$row['applicant_source']." 
AND agree_country=".$countryrow['country_id']."");
$agree1=mysql_fetch_array($agree);



$paidamount = mysql_query("SELECT SUM(amount/50) AS totalpaidme, email_address.* FROM  email_address 
where  app_id=".$row['applicant_id']."
AND request_payment=3
AND account  IN ('Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
AND currency='PHP'
");
$paidamount1=mysql_fetch_array($paidamount);


$paidamountusd = mysql_query("SELECT SUM(amount) AS totalpaidme, email_address.* FROM  email_address 
where  app_id=".$row['applicant_id']."
AND request_payment=3
AND account  IN ('Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
AND currency='USD'
");
$paidamountusd1=mysql_fetch_array($paidamountusd);




$totaldef = mysql_query("SELECT SUM(deduction_amount/50) AS totaldeduct, deduction.* FROM deduction
where app_id=".$row['applicant_id']." ");
$totaldef1=mysql_fetch_array($totaldef);


$totalcom=$row['agent_ppt'] + $row['agent_ftw'] + $row['agent_contract'] + $row['agent_deployed'];

$mybalance=$totalcom-($totaldef1['totaldeduct']+$paidamount1['totalpaidme']+$paidamountusd1['totalpaidme']);


echo'<tr style="background:'.$color111.';color:'.$color21.'">';
echo "<td><input type='hidden' name='applicant_id[]' value='".$row['applicant_id']."' ></td> ";
echo'<td>'; echo date('Y-M-d', strtotime($row['applicant_date_applied']));echo'</td>';
echo'<td><b>'.$row["applicant_first"].' '.$row["applicant_last"].'</b></td>';
echo'<td>'.$passportrow["passport_number"].'</td>';

echo'<td>'.$countryrow["country_name"].'</td>';
echo'<td>'.$emprow["employer_name"].'</td>';
echo'<td style="color:black;font-size:12px">'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td>'.$position1["position_name"].'</td>';






IF($cert1['certificate_medical_exam_date']=='1970-01-01' || $cert1['certificate_medical_exam_date']=='0000-00-00' || $cert1['certificate_medical_exam_date']=='' ) {

echo"<td>--</td>";
}

else{
echo"<td >".$cert1['certificate_medical_exam_date']."</td>";
}



//$passportrow["passport_number"]



//passportrow----------------------------------------------------------

IF($passportrow["passport_number"]=='') {

			//agreement
			if 	($row['agent_ppt']==0){
			$pptfinal=0;
			$border7="border:0px solid red";		
			}

			ELSE{
			$pptfinal=$row['agent_ppt'];
			$border7="border:0px solid black";				
			}

}

else{ 
			//agreement
			if 	($row['agent_ppt']==0   && $row['agent_deployed']==0){
		
					    
                    if($srow["status"]=="Available")
                    { 
                    
                       
						 $pptfinal=$agree1['agree_ppt'];
                        $border7="border:1px solid red";
						
                    }
                    
                     ELSE
                    { 
                     $pptfinal=0;
                        $border7="border:0px solid red";
                       
                    }
			
			
			}

			ELSE{
			$pptfinal=$row['agent_ppt'];
			$border7="border:1px solid black";			
			}
			
			

}





//FTW----------------------------------------------------------

if($cert1['certificate_medical_result']=='FIT TO WORK'){
			//agreement
			if ($row['agent_ftw']==0 && $cert1['certificate_medical_result']=='FIT TO WORK' && $row['agent_deployed']==0){
		    
                    if($srow["status"]=="Cancelled" || $srow["status"]=="cancelled" || $srow["status"]=="Repat" || $srow["status"]=="A to A")
                    { 
                    
                        $ftwfinal111=0;
                        $border1="border:0px solid red";
                    }
                    
                     ELSE
                    { 
                    
                        $ftwfinal111=$agree1['agree_ftw'];
                        $border1="border:1px solid red";
                    }
			}
			else{
			$ftwfinal111=$row['agent_ftw'];
			  $border1="border:1px solid black";		
			}
			
echo"<td>".$cert1['certificate_medical_result']."</td>";
}




if($cert1['certificate_medical_result']!='FIT TO WORK'){
			//agreement
			if ($row['agent_ftw']==0 && $cert1['certificate_medical_result']=='FIT TO WORK'){
			$ftwfinal111=0;
			$border1="border:0px solid red";		
			}
			else{
			$ftwfinal111=$row['agent_ftw'];	
			$border1="border:1px solid black";				
			}


echo"<td STYLE='color:black;font-size:9px'>--</td>";
}





//contract----------------------------------------------------------

IF($row2['requirement_contract_sign']=='1970-01-01' || $row2['requirement_contract_sign']=='0000-00-00' || $row2['requirement_contract_sign']=='' ) {

			//agreement
			if 	($row['agent_contract']==0){
			$signfinal=0;
			$border2="border:0px solid red";		
			}

			ELSE{
			$signfinal=$row['agent_contract'];
			$border2="border:1px solid black";				
			}

$reqmem="disabled";
echo"<td>--</td>";
}

else{ 
			//agreement
			if 	($row['agent_contract']==0   && $row['agent_deployed']==0){
		
					    
                    if($srow["status"]=="Cancelled" || $srow["status"]=="cancelled" || $srow["status"]=="Repat" || $srow["status"]=="A to A")
                    { 
                    
                        $signfinal=0;
                        $border2="border:0px solid red";
                    }
                    
                     ELSE
                    { 
                    
                        $signfinal=$agree1['agree_contract'];
                        $border2="border:1px solid red";
                    }
			
			
			}

			ELSE{
			$signfinal=$row['agent_contract'];
			$border2="border:1px solid black";			
			}
			
			
echo"<td >".$row2['requirement_contract_sign']."</td>";
}





//deployed----------------------------------------------------------
IF($row['applicant_status']!=9 ) {
			//agreement
				if 	($row['agent_deployed']==0){
				$deployedfinal=0;
				$border3="border:0px solid red";		
				}

				if ($row['agree_deployed']!=0){
				$deployedfinal=$row['agent_deployed'];
				$border3="";	
				}
$reqdeployed="disabled";
echo"<td>--</td>";
}


IF($row['applicant_status']==9 ) {
				//agreement
				if 	($row['agent_deployed']==0){
				$deployedfinal=$agree1['agree_deployed'];
				$border3="border:1px solid red";		
				}

				if ($row['agent_deployed']!=0){
				$deployedfinal=$row['agent_deployed'];
				$border3="border:1px solid black";	
				}

echo"<td >".$deployed1['deployed_date']."</td>";
}




echo "<td>
<select name='applicant_paid1[]'>
<option value='".$row["applicant_paid1"]."'>".$paids."</option>
<option value=1>PAID</option>
<option value=0>unpaid</option>
</SELECT>

</td> ";



if($row['request1']=='Paid') {
$request1='disabled';	
}
else{
$request1='';		
}


if($row['request2']=='Paid') {
$request2='disabled';	
}
else{
$request2='';		
}


if($row['request3']=='Paid') {
$request3='disabled';	
}
else{
$request3='';		
}


echo "<td><input type='NUMBER' name='agent_ppt[]' value='".$pptfinal."' placeholder='SERVICE FEE' style='width:70px;color:black;".$border7."' READONLY></br> ".$agree1['agree_ppt']." </br></td> ";


echo "<td><input type='NUMBER' name='agent_ftw[]' value='".$ftwfinal111."' placeholder='SERVICE FEE' style='width:70px;color:black;".$border1."' READONLY></br> ".$agree1['agree_ftw']." </br>
<small style='color:black'>Request Status</small>
<select name='request1[]' STYLE='border:2px solid blue;margin-top:5px;color:black' ".$request1.">
<option>".$row['request1']."</option>
<option>Request</option>
<option>Paid</option>
<option></option>
</SELECT>
</td> ";
echo "<td><input type='NUMBER' name='agent_contract[]' value='".$signfinal."' placeholder='SERVICE FEE'  style='width:70px;color:black;".$border2."' READONLY ></br>".$agree1['agree_contract']."</br>
<small style='color:black'>Request Status</small>
<select name='request2[]' STYLE='border:2px solid orange;margin-top:5px;color:black' ".$request2.">
<option>".$row['request2']."</option>
<option>Request</option>
<option>Paid</option>
<option></option>
</SELECT>
</td> ";



echo "<td><input type='NUMBER' name='agent_deployed[]' value='".$deployedfinal."' placeholder='SERVICE FEE' style='width:70px;color:black;".$border3."' READONLY></br> ".$agree1['agree_deployed']."</br>
<small style='color:black'>Request Status</small>
<select name='request3[]' STYLE='border:2px solid green;margin-top:5px;color:black' ".$request3.">
<option>".$row['request3']."</option>
<option>Request</option>
<option>Paid</option>
<option></option>
</SELECT>
</td> ";

echo "<td style='color:black'> ".$totalcom."</td> ";
echo "<td> ".$totaldef1['totaldeduct']."</td> ";
echo "<td style='color:black'> ".$paidamount1['totalpaidme']."</td> ";
echo "<td style='color:red'> ".$mybalance."</td> ";

echo'<td>
<a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">
<button type="button" class="btn btn-'.$srow["statusColors"].' btn-xs" style="font-size:9px">'.$srow["status"].'</button></a></td>';
echo'</tr>';

$mytotalcoms=$totalcom+$mytotalcoms;

}
?>
</tbody>
</table>

</br></br>


</form>	

<h2>Agent Commssion</h2>


<?php
$getme = mysql_query("SELECT * FROM applicant
WHERE applicant_source=".$_POST['agent_id']." 
AND (request1='Request' or request2='Request' or request3='Request')");

while($getme1=mysql_fetch_array($getme))
{
$requestamount=$getme1['agent_ftw']+$getme1['agent_contract']+$getme1['agent_deployed']+$getme1['agent_ppt'];
$requesttotal=$requestamount+$requesttotal;	

  $nameArray[] = ' 	'.$getme1['applicant_first'].' '.$getme1['applicant_last'].'  '.$requestamount.'';
}
 $resultmo=implode("\n",$nameArray);

?>


<style>
#customers {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #04AA6D;
  color: white;
}
</style>



<script>
function exportF(elem) {
  var table = document.getElementById("customers");
  var html = table.outerHTML;
  var url = 'data:application/vnd.ms-excel,' + escape(html); // Set your html table into url 
  elem.setAttribute("href", url);
  elem.setAttribute("download", "export.xls"); // Choose the file name
  return false;
}
</script>

<a id="downloadLink" onclick="exportF(this)" style="color:Red;FLOAT:right;font-size:14px;padding:5px">Export my work</a>





<table  cellspacing="0" id="customers">
<thead>
<tr>

<th STYLE="width:1%">Code #</th>



<th >Request</th>
<th>DATE</th>
<th>Agent</th>
<th >Applicant</th>
<th>Type</th>
<th >Type of Expenses</th>
<th>Payment To</th>
<th STYLE="">Requesting</th>
<th >Description</th>

<th >CURRENCY</th>
<th>AMOUNT</th>


</tr>
</thead>
<tbody>
<?php	

$agent = mysql_query("SELECT email_address.*
,applicant.applicant_first
,applicant.applicant_middle
,applicant.applicant_last
,applicant.applicant_id FROM email_address
LEFT JOIN  	applicant
ON email_address.app_id = applicant.applicant_id
where    account  IN ('Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
AND app_id=".$_POST['agent_id']."
order by date_ad  asc");

if(isset($_POST['add_exp']))
{
$agent = mysql_query("SELECT email_address.*
,applicant.applicant_first
,applicant.applicant_middle
,applicant.applicant_last
,applicant.applicant_id FROM email_address
LEFT JOIN  	applicant
ON email_address.app_id = applicant.applicant_id
where  account  IN ('Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
AND agent_id=".$_POST['agent_id']."
order by date_ad  asc");
}


if(isset($_POST['add_exp']))
{
$agent = mysql_query("SELECT email_address.*
,applicant.applicant_first
,applicant.applicant_middle
,applicant.applicant_last
,applicant.applicant_id FROM email_address
LEFT JOIN  	applicant
ON email_address.app_id = applicant.applicant_id
where    account  IN ('Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
AND agent_id=".$_POST['agent_id']."
order by date_ad  asc");
}




if(isset($_POST['Search1']))
{
$agent = mysql_query("SELECT email_address.*
,applicant.applicant_first
,applicant.applicant_middle
,applicant.applicant_last
,applicant.applicant_id FROM email_address
LEFT JOIN  	applicant
ON email_address.app_id = applicant.applicant_id
where  account  IN ('Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
AND agent_id=".$_POST['agent_id']."
order by date_ad  asc");
}





if(isset($_POST['updatedata1']))
{
$agent = mysql_query("SELECT email_address.*
,applicant.applicant_first
,applicant.applicant_middle
,applicant.applicant_last
,applicant.applicant_id FROM email_address
LEFT JOIN  	applicant
ON email_address.app_id = applicant.applicant_id
where  account  IN ('Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
AND agent_id=".$_POST['agent_id']."
order by date_ad  asc");
}






 


while($agentrow=mysql_fetch_array($agent))
{


$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$agentrow['agent_id']." ");
$row1=mysql_fetch_array($app1);

$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$agentrow['app_id']." ");
$rowp=mysql_fetch_array($applicant);

$USER = mysql_query("SELECT * FROM  user where user_id=".$agentrow['staff_id']." ");
$rowu=mysql_fetch_array($USER);


$LIQ = mysql_query("SELECT sum(l_amount) as cashfund FROM  e_liq 
where l_status=0 AND l_e_d=".$agentrow['e_id']."
and l_type='Agent'
");
$cashliq=mysql_fetch_array($LIQ);

$bank = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$agentrow['fund_bank_id']." ");
$bank1=mysql_fetch_array($bank);

$source = mysql_query("SELECT * FROM  source_payment where s_id=".$agentrow['fund_source_id']." ");
$source1=mysql_fetch_array($source);

if($agentrow["request_payment"]==0){$request='For Approval';$rcolor='black';}
if($agentrow["request_payment"]==1){$request='Approved';$rcolor='blue';}
if($agentrow["request_payment"]==2){$request='For Release';$rcolor='orange';}
if($agentrow["request_payment"]==3){$request='Released';$rcolor='green';}
if($agentrow["request_payment"]==4){$request='Decline';$rcolor='red';}
if($agentrow["request_payment"]==5){$request='Return to maker';$rcolor='red';}

echo'<tr>';

echo'<td>000'.$agentrow["e_id"].'</td>';




echo'<td style="font-size:12px;color:white;">
<i style="background:'.$rcolor.';padding:2px;margin:2px">'.$request.'</i>
</td>';
echo'<td>'; echo date('Y-M-d', strtotime($agentrow['date_ad']));echo'</td>';
echo'<td STYLE="Color:black;font-weight:bold;font-size:14px">'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td STYLE="Color:black;font-weight:bold;font-size:14px">'.$agentrow["applicant_first"].' '.$agentrow["applicant_last"].'</td>';

echo'<td>'.$agentrow["method"].'</td>';
echo'<td>'.$agentrow["account"].'</td>';
echo'<td>'.$agentrow["payment_to"].'</td>';
echo'<td>'.$rowu["user_fullname"].'</td>';
echo'<td>'.$agentrow["description"].'</td>';



echo'<td>'.$agentrow["currency"].'</td>';
echo'<td>'.$agentrow['amount'].'</td>';
echo'</tr>';

if($agentrow["currency"]=="USD" && $agentrow["request_payment"] =4 ){ $tetalpaid=$agentrow['amount'];}
if($agentrow["currency"]=="PHP" && $agentrow["request_payment"] =4) { $tetalpaid=$agentrow['amount']/50;}
else{$tetalpaid=0; }
$mytotalpaids=$tetalpaid+$mytotalpaids;
}

$mytodeduct = mysql_query("SELECT SUM(deduction_amount/50) AS totaldeduct, deduction.* FROM deduction
where agent_id=".$_POST['agent_id']." ");
$mytodeduct1=mysql_fetch_array($mytodeduct);
$mydeduct=$mytodeduct1['totaldeduct'];


$debitko=$mytotalpaids+$mydeduct;
$decredit=$mytotalcoms-$debitko;
?>

<tr>
<td style="FONT-WEIGHT:bold;text-align:right;font-size:22px;background:orange" colspan="14"> AGREECOM $:
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo number_format($mytotalcoms,2);?></td>
</tr>

<tr>
<td style="FONT-WEIGHT:bold;text-align:right;font-size:22px;background:lightblue" colspan="14"> RELEASED $:
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo number_format($mytotalpaids,2);?></td>
</tr>


<tr>
<td style="FONT-WEIGHT:bold;text-align:right;font-size:22px;background:lightblue" colspan="14"> DEDUCTION $:
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo number_format($mydeduct,2);?></td>
</tr>



<tr>
<td style="FONT-WEIGHT:bold;text-align:right;font-size:22px;background:black;color:white" colspan="14"> BALANCE $:
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo number_format($decredit,2);?></td>
</tr>

</tbody>
</table>







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







</div>

</div>

</div>
</div>