<?php include'inc/header.php';
include'inc/add.php';
include'tracking.php';
include'styles.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
      <meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
 
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




    


<div class="clearfix"></div>
<form id="demo-form2" data-parsley-validate class="form-horizontal form-label-left" method="post">
<div class="col-md-2 col-sm-4 col-xs-4 form-group">
<select name="country" class="form-control" required>
<option value="99999">-- All country --</option>
<option value="7">SAUDI</option>
<option value="11">QATAR</option>
<option value="5">KUWAIT</option>
<option value="12">DUBAI</option>
</select>
</div>


<div class="col-md-2 col-sm-4 col-xs-4 form-group">
<?php
echo'<select class="form-control" name="agent_id" >
<option VALUE="99999">-Agents-</option>';


$usertype = mysql_query("SELECT * FROM recruitment_agent where agent_type='Agent'  AND agent_first!='0' ORDER BY agent_first asc");
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


<div class="col-md-2 col-sm-4 col-xs-4 form-group">
<?php
echo'<select class="form-control" name="applicant_status" required>
<option VALUE="99999" style="font-weight:bold;color:Red">-All In Process-</option>';
$statuss = mysql_query("SELECT * FROM statuses ORDER BY status asc");
while($statuss1=mysql_fetch_array($statuss))

{
echo'<option value="'.$statuss1["number"].'"> '.$statuss1["status"].'</option>';
}

ECHO'</select>';
?>
</div>



<div class="col-md-2 col-sm-4 col-xs-4 form-group">
<?php
echo'<select class="form-control" name="applicant_employer" required>
<option VALUE="99999">-Select FRA-</option>';
$employerrr = mysql_query("SELECT * FROM employer ORDER BY employer_name asc");
while($employerrr1=mysql_fetch_array($employerrr))

{
echo'<option value="'.$employerrr1["employer_id"].'"> '.$employerrr1["employer_name"].'</option>';
}

ECHO'</select>';
?>
</div>



<div class="col-md-4 col-sm-12 col-xs-12 form-group">
<button type="submit" class="btn btn-success"  name="Searchdeployed" >Filter </button>

</div>
</form>       









<div class="row">
<div class="col-md-12 col-xs-12">
<div class="x_panel">

<div class="x_content">
<script>
function exportF(elem) {
  var table = document.getElementById("example-checkbox");
  var html = table.outerHTML;
  var url = 'data:application/vnd.ms-excel,' + escape(html); // Set your html table into url 
  elem.setAttribute("href", url);
  elem.setAttribute("download", "export.xls"); // Choose the file name
  return false;
}
</script>

<?php $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>


</br>
<a id="downloadLink" onclick="exportF(this)" style="color:Red;FLOAT:right;font-size:14px;padding:5px">Save my work</a>


    <form id="batchreport" action="soa6.php" method="post" target="_blank">
<a href="applicants.php" style="font-size:18px;padding:8px;border:1px solid black;marngin:2px;font-weight:bold;background:#ADD8E6">Applicants</a> 
<a href="applicant-reports.php"  style="font-size:18px;padding:8px;border:1px solid black;marngin:2px;font-weight:bold;background:white">Detailed Reports</a>
<a href="<?=$actual_link;?>"  style="font-size:18px;padding:8px;border:1px solid black;marngin:2px;font-weight:bold;background:#FFD700;float:right">Refresh</a>

</br></br>
	<input type="submit" value="PRINT">


<style>
#example-checkbox table{
width:100%;	
}
#example-checkbox td {
font-size:14px;
text-align:center;	
}
#example-checkbox a{
font-size:14px;
text-align:center;	
}
#example-checkbox th{
font-size:14px;
text-align:center;	
}


table {
  text-align: left;
  position: relative;
  border-collapse: collapse; 
}
th, td {
  padding: 0.25rem;
}
tr.red th {
  background: red;
  color: white;
}
tr.green th {
  background: green;
  color: white;
}
tr.purple th {
  background: purple;
  color: white;
}
th {
  background: lightgray;
  position: sticky;
  top: 0; /* Don't forget this, required for the stickiness */
  box-shadow: 0 4px 4px -1px rgba(0, 0, 0, 0.6);
}


</style>



<table  cellspacing="0"
id="example-checkbox" STYLE="BACKGROUND:WHITE">
<thead>
<tr>
<th></th>
<th></th>
<th>DATE</th>
<th>Name</th>

<th>Country</th>
<th>Agent</th>
<th>Medical</th>

<th>FRA</th>
<th>Status</th>
<th style="background:#6495ED">Full Med</th>
<th style="background:#6495ED">MMR</th>
<th style="background:#6495ED">EXTENSION</th>
<th style="background:#6495ED">RE MED</th>
<th style="background:#4B0082;COLOR:white">SPECIAL BIO</th>
<th style="background:#4B0082;COLOR:white">NORMAL BIO</th>
<th style="background:#4B0082;COLOR:white">RE BIO</th>

<th style="background:#00FFFF">INSURANCE</th>
<th style="background:#696969;COLOR:white">SWAB</th>
<th style="background:#696969;COLOR:white">RE-SWAB</th>
<th style="background:#ADFF2F;COLOR:black">TESDA</th>
<th style="background:#ADFF2F;COLOR:black">Tesda Assestment</th>
<th style="background:#ADFF2F;COLOR:black">TESDA REPAYMENT</th>
<th style="background:#FFD700;COLOR:black">OEC</th>
<th style="background:#FFD700;COLOR:black">PDOS</th>
<th style="background:#FFD700;COLOR:black">PT</th>
<th style="background:#FFD700;COLOR:black">RE PT</th>
<th style="background:#FFD700;COLOR:black">NOTARY</th>
<th style="background:#B22222;COLOR:white">TICKET INT</th>
<th style="background:#B22222;COLOR:white">TICKET REBOOKING</th>
<th style="background:#B22222;COLOR:white">TICKET OTHERS</th>
<th style="background:#FF7F50;COLOR:BLACK">OTHER EXPENSE</th>

<th>Commission & Charge</th>
<th>Total Expense</th>
<th style="background:#FFD700;COLOR:BLACK">Remittance_Agree</th>
<th style="background:#DAA520;COLOR:BLACK">Remittance_Rcvd</th>
<th style="background:#F0FFF0;COLOR:BLACK">BALANCE</th>
<th style="background:#F0FFF0;COLOR:BLACK">Income</th>
</tr>
</thead>
<tbody>
<?php
$count=1;	
	$app = mysql_query("SELECT applicant.applicant_id
	,applicant.applicant_first
	,applicant.applicant_last
	,applicant.applicant_employer
	,applicant.applicant_source
	,employer.employer_id
	,employer.employer_name
	,applicant.applicant_status
	,statuses.*
	,recruitment_agent.*
	,applicant.applicant_date_applied
	,applicant.applicant_preferred_country
	,applicant.applicant_remarks
	,applicant.applicant_employer
	FROM applicant
	LEFT JOIN  statuses
	ON applicant.applicant_status = statuses.number



	LEFT JOIN  	employer
	ON applicant.applicant_employer =employer.employer_id

	LEFT JOIN  	recruitment_agent
	ON applicant.applicant_source =recruitment_agent.agent_id
	where applicant.applicant_status!=9
	limit 10
	");

if(isset($_POST['Searchdeployed']))
{

if ($_POST['agent_id']!="99999"){
 $mygent="AND applicant.applicant_source= ".$_POST['agent_id']."";
}
else{  $mygent="";  }







if ($_POST['country']!="99999"){
$mycountry="AND applicant.applicant_preferred_country=".$_POST['country']."";
}
else{ $mycountry="";}


if ($_POST['applicant_status']!="99999"){
$mystatus="AND applicant.applicant_status=".$_POST['applicant_status']."";
}
else{ $mystatus="";}

//else{ $mystatus="AND applicant.applicant_status  NOT IN (9,21,22,1,14,25)";}


if ($_POST['applicant_employer']!="99999"){
$myemployer="AND applicant.applicant_employer=".$_POST['applicant_employer']."";
}
else{ $myemployer="";}

			$app = mysql_query("SELECT applicant.applicant_id
			,applicant.applicant_first
			,applicant.applicant_last
			,applicant.applicant_employer
			,applicant.applicant_source
			,employer.employer_id
			,employer.employer_name
			,applicant.applicant_status
			,applicant.applicant_remarks
			,applicant.applicant_employer
			,statuses.*
			,recruitment_agent.*
			,applicant.applicant_date_applied
			,applicant.applicant_preferred_country

			FROM applicant
			LEFT JOIN  statuses
			ON applicant.applicant_status = statuses.number



			LEFT JOIN  	employer
			ON applicant.applicant_employer =employer.employer_id

			LEFT JOIN  	recruitment_agent
			ON applicant.applicant_source =recruitment_agent.agent_id
			where 
			applicant.applicant_id!=0
			$mygent
			$mycountry
			$mystatus
			$myemployer

			");

}









while($row=mysql_fetch_array($app))
{


$status = mysql_query("SELECT * FROM statuses where number=".$row['applicant_status']." ");
$srow=mysql_fetch_array($status);

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
$row1=mysql_fetch_array($app1);



$cert = mysql_query("SELECT certificate_applicant,certificate_medical_result FROM applicant_certificate where certificate_applicant=".$row['applicant_id']." ");
$cert1=mysql_fetch_array($cert);





$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);



$app2 = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id

LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id

where  liq_app=".$row['applicant_id']."  AND   l_type NOT IN ('Agent')
and l_status=0");
$php=mysql_fetch_array($app2);






// medical full med
$medfull = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id

LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id

where  liq_app=".$row['applicant_id']."  AND   type_applicant  IN ('Medical Expenses','Medical - P2',
'Medical - P2 SAUDI','PROSPERITY MEDICAL CLINIC','MEDICAL AND VACCINE PAYMENT','Medical - P1')
and l_status=0 AND   l_type NOT IN ('Agent')");
$medfull1=mysql_fetch_array($medfull);

// vaccine
$vaccine = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id

LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id

where  liq_app=".$row['applicant_id']."  AND   type_applicant  IN ('Vaccine')
and l_status=0 AND   l_type NOT IN ('Agent') ");
$vaccine1=mysql_fetch_array($vaccine);


// extension
$exention = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id

LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id

where  liq_app=".$row['applicant_id']."  AND   type_applicant  IN ('MEDICAL EXTENSION')
and l_status=0 AND   l_type NOT IN ('Agent')");
$exention1=mysql_fetch_array($exention);


// extension
$remed = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where  liq_app=".$row['applicant_id']."  AND   type_applicant  IN ('RE-MEDICAL')
and l_status=0 AND   l_type NOT IN ('Agent')");
$remed1=mysql_fetch_array($remed);


// insurance
$insurance = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where  liq_app=".$row['applicant_id']."  AND   type_applicant  IN ('INSURANCE 2','INSURANCE')
and l_status=0 AND   l_type NOT IN ('Agent')");
$insurance1=mysql_fetch_array($insurance);

// SWAB
$swab = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where  liq_app=".$row['applicant_id']."  AND   type_applicant  IN ('SWABTEST','SWABTEST_AHMAD SHABAN')
and l_status=0 AND   l_type NOT IN ('Agent') ");
$swab1=mysql_fetch_array($swab);

//re SWAB
$reswab = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where  liq_app=".$row['applicant_id']."  AND   type_applicant  IN ('SWAB TEST REPAYMENT')
and l_status=0 AND   l_type NOT IN ('Agent')");
$reswab1=mysql_fetch_array($reswab);


//tesda
$tesda = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where  liq_app=".$row['applicant_id']."  AND   type_applicant  IN ('TESDA PAYMENT','Tesda full')
and l_status=0 AND   l_type NOT IN ('Agent')");
$tesda1=mysql_fetch_array($tesda);

//tesda assset
$tesda2 = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where  liq_app=".$row['applicant_id']."  AND   type_applicant  IN ('Tesda Assestment')
and l_status=0 AND   l_type NOT IN ('Agent')");
$tesda22=mysql_fetch_array($tesda2);

//tesda repayment
$tesdare = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where  liq_app=".$row['applicant_id']."  AND   type_applicant  IN ('TESDA RE ASSESSMENT','TESDA REPAYMENT')
and l_status=0 AND   l_type NOT IN ('Agent')");
$tesdare1=mysql_fetch_array($tesdare);

//oec
$oec = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where  liq_app=".$row['applicant_id']."  AND   type_applicant  IN ('OEC Com')
and l_status=0 AND   l_type NOT IN ('Agent')");
$oec1=mysql_fetch_array($oec);


//pdos
$pdos = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where  liq_app=".$row['applicant_id']."  AND   type_applicant  IN ('PDOS')
and l_status=0 AND   l_type NOT IN ('Agent')");
$pdos1=mysql_fetch_array($pdos);


//pt
$pt = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where  liq_app=".$row['applicant_id']."  AND   type_applicant  IN ('PREGNANCY TEST')
and l_status=0 AND   l_type NOT IN ('Agent')");
$pt1=mysql_fetch_array($pt);



//rept
$pt2 = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where  liq_app=".$row['applicant_id']."  AND   type_applicant  IN ('PREGNANCY TEST REPAT')
and l_status=0 AND   l_type NOT IN ('Agent')");
$pt22=mysql_fetch_array($pt2);



//bio
$bio = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where  liq_app=".$row['applicant_id']."  AND   type_applicant  IN ('Biometric')
and l_status=0 AND   l_type NOT IN ('Agent')");
$bio1=mysql_fetch_array($bio);

//sbio
$sbio = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where  liq_app=".$row['applicant_id']."  AND   type_applicant  IN ('SPECIAL BIOMETRIC')
and l_status=0 AND   l_type NOT IN ('Agent')");
$sbio1=mysql_fetch_array($sbio);

//rebio
$rebio = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where  liq_app=".$row['applicant_id']."  AND   type_applicant  IN ('BIOMETRIC 2')
and l_status=0 AND   l_type NOT IN ('Agent')");
$rebio1=mysql_fetch_array($rebio);

//TICKET
$ticket = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where  liq_app=".$row['applicant_id']."  AND   type_applicant  IN ('International tickets')
and l_status=0 AND   l_type NOT IN ('Agent')");
$ticket1=mysql_fetch_array($ticket);


//RETICKET
$reticket = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where  liq_app=".$row['applicant_id']."  AND   type_applicant  IN ('TICKET REBOOKING')
and l_status=0 AND   l_type NOT IN ('Agent')");
$reticket1=mysql_fetch_array($reticket);


//oTICKET
$oticket = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where  liq_app=".$row['applicant_id']."  AND   type_applicant  IN ('TICKET EXPENSES','TICKET-DOMESTIC')
and l_status=0 AND   l_type NOT IN ('Agent')");
$oticket1=mysql_fetch_array($oticket);


//oTICKET
$otherex = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where  liq_app=".$row['applicant_id']." 
and l_status=0
AND   l_type NOT IN ('Agent')
 AND   type_applicant  NOT IN ('Medical Expenses','Medical - P2','MEDICAL EXTENSION','Medical - P2 SAUDI'
,'RE-MEDICAL','PROSPERITY MEDICAL CLINIC','INSURANCE 2','INSURANCE','SWABTEST_AHMAD SHABAN','SWABTEST'
,'SWAB TEST REPAYMENT','Tesda full','TESDA PAYMENT'
,'TESDA RE ASSESSMENT','Tesda Assestment','TESDA REPAYMENT','OEC Com'
,'PDOS','PREGNANCY PREGNANCY TEST REPAT','NOTARY','Biometric'
,'BIOMETRIC 2','SPECIAL BIOMETRIC','International tickets','TICKET REBOOKING','TICKET'
,'TICKET EXPENSES','TICKET-DOMESTIC')

 ");
$otherex111=mysql_fetch_array($otherex);



$collectapp = mysql_query("SELECT sum(fra_ftw+fra_visa+fra_deployed+fra_sent+fra_before) as totalamount,applicant_id
,fra_ftw,fra_visa,fra_deployed,fra_sent,fra_before FROM  applicant 
where  applicant_id=".$row['applicant_id']." ");
$collectapp1=mysql_fetch_array($collectapp);


$received = mysql_query("SELECT sum(fra_amount) as totalamount FROM  liq_fra 
where  app_id=".$row['applicant_id']." ");
$received1=mysql_fetch_array($received);


//agent

$agentexpense = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where  liq_app=".$row['applicant_id']."  
AND l_type  IN ('Agent')
AND request_payment=3  
AND mineme=0 
and l_status=0");
$agentexpense1=mysql_fetch_array($agentexpense);





$meagent=$agentexpense1["totalamount"];
$meaapp=$medfull1["totalamount"]+$vaccine1["totalamount"]+
$exention1["totalamount"]+$remed1["totalamount"]+$insurance1["totalamount"]+$swab1["totalamount"]
+$reswab1["totalamount"]+$tesda1["totalamount"]+$tesda22["totalamount"]+$tesdare1["totalamount"]
+$oec1["totalamount"]+$pdos1["totalamount"]+$bio1["totalamount"]+$sbio1["totalamount"]+$rebio1["totalamount"]
+$ticket1["totalamount"]+$reticket1["totalamount"]+$oticket1["totalamount"]+$pt1["totalamount"]+$pt22["totalamount"];


$totlexpenseper1=$agentexpense1["totalamount"]+$meaapp;
$totlexpenseper2=($php["totalamount"]-$meaapp)*1;
$totalmeke=abs($totlexpenseper2);
$totlexpenseper=$totlexpenseper1+$totalmeke;
								
								
							
								
								
echo'<tr>';

echo'<td><input type="checkbox" name="p_id_report[]" value="'.$row['applicant_id'].'"  checked></td>';
echo'<td>'.$count.'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['applicant_date_applied']));echo'</td>';
echo'<td style="color:black;font-size:14px"><b>'.$row["applicant_first"].' '.$row["applicant_last"].'</b></td>';

echo'<td>'.$countryrow["country_name"].'</td>';

echo'<td style="font-size:12px">'.$row["agent_first"].' '.$row["agent_last"].'</td>';
echo"<td>".$cert1['certificate_medical_result']."</td>";
echo'<td>'.$row["employer_name"].'</td>';
echo'<td>
<a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">
<button type="button" class="btn btn-'.$row["statusColors"].' btn-xs" style="font-size:12px">'.$row["status"].'</button></a></td>';



echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($medfull1["totalamount"],2).'</a></td>';
echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($vaccine1["totalamount"],2).'</a></td>';
echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($exention1["totalamount"],2).'</a></td>';
echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($remed11["totalamount"],2).'</a></td>';
echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($sbio1["totalamount"],2).'</a></td>';

echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($bio1["totalamount"],2).'</a></td>';
echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($rebio1["totalamount"],2).'</a></td>';



echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($insurance1["totalamount"],2).'</a></td>';
echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($swab1["totalamount"],2).'</a></td>';
echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($reswab1["totalamount"],2).'</a></td>';
echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($tesda1["totalamount"],2).'</a></td>';
echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($tesda22["totalamount"],2).'</a></td>';
echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($tesdare1["totalamount"],2).'</a></td>';

echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($oec1["totalamount"],2).'</a></td>';
echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($pdos1["totalamount"],2).'</a></td>';
echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($pt1["totalamount"],2).'</a></td>';
echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($pt22["totalamount"],2).'</a></td>';
echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($tesdare1["totalamount"],2).'</a></td>';

echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($ticket1["totalamount"],2).'</a></td>';
echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($reticket11["totalamount"],2).'</a></td>';
echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($oticket1["totalamount"],2).'</a></td>';

echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($totalmeke,2).'</a></td>';



echo'<td style="color:red"><a href="soa1.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($agentexpense1["totalamount"],2).'</a></td>';
echo'<td style="color:red">'.number_format($totlexpenseper,2).'</td>';

$fraexpense=$totlexpenseper/50;		
$frabalance=$collectapp1["totalamount"]	-	$received1["totalamount"];	
$fraincome=$received1["totalamount"]-$fraexpense;

echo'<td style="color:BLACK;font-weight:BOLD;FONT-SIZE:14PX;background:#FFD700">'.number_format($collectapp1["totalamount"],2).' $</td>';
echo'<td style="color:BLACK;font-weight:BOLD;FONT-SIZE:14PX;background:#DAA520">'.number_format($received1["totalamount"],2).' $</td>';
echo'<td style="color:BLACK;font-weight:BOLD;FONT-SIZE:14PX;background:#F0FFF0">'.number_format($frabalance,2).' $</td>';
echo'<td style="color:BLACK;font-weight:BOLD;FONT-SIZE:14PX;background:#F0FFF0">'.number_format($fraincome,2).' $</td>';


echo'</tr>';
$count++;
$myapplicant=$php["totalamount"]+$myapplicant;
$myagent=$agentexpense1["totalamount"]+$myagent;
$myall=$totlexpenseper+$myall;
$mycollectapp=$collectapp1["totalamount"]+$mycollectapp;
$myrece=$received1["totalamount"]+$myrece;
$mybalance=$frabalance+$mybalance;
$myincome=$fraincome+$myincome;
}
?>
</tbody>

<tr>
<td	colspan="31" style="text-align:right;font-size:22px;color:black">TOTAL: </td>
<td	colspan="1" style="text-align:right;font-size:22px;color:black"><?php echo''.number_format($myagent,2).'';?></td>
<td	colspan="1" style="text-align:right;font-size:22px;color:black"><?php echo''.number_format($myall,2).'';?></td>
<td	colspan="1" style="text-align:right;font-size:22px;color:black;background:#FFD700"><?php echo''.number_format($mycollectapp,2).'';?>$</td>
<td	colspan="1" style="text-align:right;font-size:22px;color:black;background:#DAA520"><?php echo''.number_format($myrece,2).'';?>$</td>
<td	colspan="1" style="text-align:right;font-size:22px;color:black;background:#F0FFF0"><?php echo''.number_format($mybalance,2).'';?>$</td>
<td	colspan="1" style="text-align:right;font-size:22px;color:black;background:#F0FFF0"><?php echo''.number_format($myincome,2).'';?>$</td>


<tr>
</table>
    </form>


<div class="ln_solid"></div>

</div>
</div>
</div>  




</div>			











<!-- /footer content -->
</div>
</div>




<!-- /footer content -->
</div>
</div>

<!-- jQuery -->
<script src="../vendors/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- Skycons -->
<script src="../vendors/skycons/skycons.js"></script>

<!-- Custom Theme Scripts -->
<script src="../build/js/custom.min.js"></script>

</body>



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
</html>