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


<form id="demo-form2" data-parsley-validate class="form-horizontal form-label-left" method="post">

<div class="form-group">
<div class="col-md-4 col-sm-4 col-xs-4 form-group">
<input type="text" placeholder="first name , last name , fra name" class="form-control" name="search1" required>
</div>

<div class="col-md-6 col-sm-12 col-xs-12 form-group">
<button type="submit" class="btn btn-info"  name="SearchME" >Search Applicant</button>

</div>
</form>  


    


<div class="clearfix"></div>
<form id="demo-form2" data-parsley-validate class="form-horizontal form-label-left" method="post">
<div class="col-md-2 col-sm-4 col-xs-4 form-group">
<select name="agent" class="form-control" required>
<option value="99999">-- All --</option>
<?php
$agent1 = mysql_query("SELECT * FROM  recruitment_agent WHERE agent_first!='0'
ORDER BY agent_first asc");
while($agent=mysql_fetch_array($agent1))
{
echo'<option value="'.$agent["agent_id"].'">'.$agent["agent_first"].' '.$agent["agent_last"].'</option>';
}
?>
</select>
</div>
<div class="col-md-4 col-sm-12 col-xs-12 form-group">
<button type="submit" class="btn btn-success"  name="Searchdeployed" >Filter Agent</button>

</div>
</form>       





<div class="clearfix"></div>





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



</br>
<a id="downloadLink" onclick="exportF(this)" style="color:Red;FLOAT:right;font-size:14px;padding:5px">Export File</a>
    <form id="batchreport" action="soa6.php" method="post" target="_blank">


<a href="applicants.php" style="font-size:18px;padding:8px;border:1px solid black;marngin:2px;font-weight:bold;background:white">Applicants</a> 
</br></br>
	<input type="submit" value="PRINT">


<table  cellspacing="0" id="example-checkbox">
<thead>
<tr>
<th></th>

<th>DATE</th>
<th>Name</th>
<th>Exp</th>
<th>Passport</th>
<th>Country</th>
<th>Agent</th>


<th>Status</th>

<th>Applicant Exp</th>
<th style="background:yellow">Commi Exp</th>
<th  style="background:yellow">Commi Agree</th>
<th  style="background:#FFD700">Deduction</th>
<th  style="background:orange">Bal Com</th>
<th>Position</th>

<th>Result</th>

<th>Con Sign</th>
<th>Deployed</th>
<th>FRA</th>
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

	FROM applicant
	LEFT JOIN  statuses
	ON applicant.applicant_status = statuses.number



	LEFT JOIN  	employer
	ON applicant.applicant_employer =employer.employer_id

	LEFT JOIN  	recruitment_agent
	ON applicant.applicant_source =recruitment_agent.agent_id

	LIMIT 50");

if(isset($_POST['SearchME']))
{

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
,applicant.applicant_ex
    FROM applicant
    	LEFT JOIN  statuses
		ON applicant.applicant_status = statuses.number
		

	
		LEFT JOIN  	employer
		ON applicant.applicant_employer =employer.employer_id

		LEFT JOIN  	recruitment_agent
		ON applicant.applicant_source =recruitment_agent.agent_id
where
applicant_last LIKE '%".$_POST['search1']."%'
OR applicant_first LIKE '%".$_POST['search1']."%'
OR employer_name LIKE '%".$_POST['search1']."%' LIMIT 20 ");

ECHO $_POST['search1'];
}




if(isset($_POST['Searchdeployed']))
{



 $app=mysql_query("SELECT applicant.applicant_id
	,applicant.applicant_first
	,applicant.applicant_last
	,applicant.applicant_employer
	,applicant.applicant_source
	,employer.employer_id
	,employer.employer_name
	,applicant.applicant_status
	,statuses.*
	,applicant.applicant_ex
	,recruitment_agent.*
	,applicant.applicant_date_applied
	,applicant.applicant_preferred_country
,applicant.agent_ftw
,applicant.agent_contract
,applicant.agent_ppt
,applicant.agent_deployed
	FROM applicant
	LEFT JOIN  statuses
	ON applicant.applicant_status = statuses.number



	LEFT JOIN  	employer
	ON applicant.applicant_employer =employer.employer_id

	LEFT JOIN  	recruitment_agent
	ON applicant.applicant_source =recruitment_agent.agent_id
where applicant_source=".$_POST['agent']." ");


}





if(isset($_POST['Searchdeployed1']))
{



 $app=mysql_query("SELECT applicant.applicant_id
	,applicant.applicant_first
	,applicant.applicant_last
	,applicant.applicant_employer
	,applicant.applicant_source
	,employer.employer_id
	,employer.employer_name
	,applicant.applicant_status
	,statuses.*
	,applicant.applicant_ex
	,recruitment_agent.*
	,applicant.applicant_date_applied
	,applicant.applicant_preferred_country
	,applicant.agent_ppt

,applicant.agent_ftw

,applicant.agent_contract

,applicant.agent_deployed


	FROM applicant
	LEFT JOIN  statuses
	ON applicant.applicant_status = statuses.number



	LEFT JOIN  	employer
	ON applicant.applicant_employer =employer.employer_id

	LEFT JOIN  	recruitment_agent
	ON applicant.applicant_source =recruitment_agent.agent_id
where account_agent=".$_POST['accountmanager']." ");


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



$passport = mysql_query("SELECT * FROM applicant_passport where passport_applicant=".$row['applicant_id']." ");
$passportrow=mysql_fetch_array($passport);

$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);


//$app2 = mysql_query("SELECT SUM(l_amount) as totalamount ,email_address.* FROM email_address
//WHERE liq_app=".$row["applicant_id"]." AND  l_status=0");
//$php=mysql_fetch_array($app2);




$app2 = mysql_query("SELECT sum(amount) as totalamount FROM  email_address 

where  app_id=".$row['applicant_id']."  
AND    account NOT IN ('Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
AND request_payment=3  ");
$php=mysql_fetch_array($app2);


$agentexpense = mysql_query("SELECT sum(amount) as totalamount FROM  email_address 
where  app_id=".$row['applicant_id']."  
AND  account  IN ('Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
AND request_payment=3  ");
$agentexpense1=mysql_fetch_array($agentexpense);


$accountmanager = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq888 
LEFT JOIN email_address
ON e_liq888.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq888.liq_app = applicant.applicant_id
where  liq_app=".$row['applicant_id']."  
AND request_payment=3  
and l_status=0");
$accountmanager1=mysql_fetch_array($accountmanager);

$totaldef = mysql_query("SELECT SUM(deduction_amount) AS totaldeduct, deduction.* FROM deduction
where app_id=".$row['applicant_id']." ");
$totaldef1=mysql_fetch_array($totaldef);


//$totaldef1['totaldeduct']

$totlexpenseper=$php["totalamount"]+$agentexpense1["totalamount"];
$total_agree=$row['agent_ppt']+$row['agent_ftw']+$row['agent_contract']+$row['agent_deployed'];
$combal=$total_agree-($agentexpense1["totalamount"]+$totaldef1['totaldeduct']);
echo'<tr>';


echo'<td>'.$count.'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['applicant_date_applied']));echo'</td>';
echo'<td style="color:black;font-size:13px"><b>'.$row["applicant_first"].' '.$row["applicant_last"].'</b></td>';
echo'<td>'.$row["applicant_ex"].'</td>';
echo'<td>'.$passportrow["passport_number"].'</td>';
echo'<td>'.$countryrow["country_name"].'</td>';

echo'<td style="font-size:12px">'.$row["agent_first"].' '.$row["agent_last"].'</td>';


echo'<td>
<a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">
<button type="button" class="btn btn-'.$row["statusColors"].' btn-xs" style="font-size:12px">'.$row["status"].'</button></a></td>';

echo'<td style="color:red"><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">'.number_format($php["totalamount"],2).'</a></td>';
echo'<td style="color:black;background:yellow;font-size:14px"><a href="soa1.php?appid='.$row['applicant_id'].'"  target="_blank" style="color:black">
'.number_format($agentexpense1["totalamount"],2).'</a></td>';

echo'<td style="color:black;background:#FFD700;font-weight:bold;font-size:14px">'.number_format($total_agree,2).'
<A href="applicant_fra.php?app='.$row["applicant_id"].'" style="color:white;font-size:14px;background:red;padding:2px" target="_blank">Review</a>
</td>';

echo'<td style="color:black;background:#FFD700;font-weight:bold;font-size:14px">'.number_format($totaldef1['totaldeduct'],2).'</td>';
echo'<td style="color:black;background:orange;font-size:14px">'.number_format($combal,2).'</td>';


echo'<td>'.$position1["position_name"].'</td>';


echo"<td>".$cert1['certificate_medical_result']."</td>";



IF($row2['requirement_contract_sign']=='1970-01-01' || $row2['requirement_contract_sign']=='0000-00-00' || $row2['requirement_contract_sign']=='' || $row2['requirement_contract']==null ) {
echo"<td></td>";
}

else{ 
echo"<td >".$row2['requirement_contract_sign']."  </td>";
}
echo"<td >".$deployed1['deployed_date']."</td>";


echo'<td>'.$row["employer_name"].'</td>';


//echo'<td style="color:red">'.number_format($totlexpenseper,2).'</td>';
echo'</tr>';
$count++;


$myapplicant=$php["totalamount"]+$myapplicant;
$myagent=$agentexpense1["totalamount"]+$myagent;
$myall=$totlexpenseper+$myall;

}
?>
</tbody>



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





<!-- Propeller Data table js-->
<script type="text/javascript" language="javascript" src="jsdatatables.js"></script>
<script>
//Propeller Customised Javascript code 
$(document).ready(function() {
$('#example-checkbox').DataTable({
responsive: false,
columnDefs: [ {
orderable: false,
className: 'select-checkbox',
targets:0,
} ],
select: {
style: 'multi',
selector: 'td:first-child'
},
order: [ 10, 'desc' ],
bFilter: true,
bLengthChange: true,
pagingType: "simple",
"paging": true,
"searching": true,
"language": {
"info": " _START_ - _END_ of _TOTAL_ ",
"sLengthMenu": "<span class='custom-select-title'>Rows per page:</span> <span class='custom-select'> _MENU_ </span>",
"sSearch": "",
"sSearchPlaceholder": "Search",
"paginate": {
"sNext": " ",
"sPrevious": " "
},
},
dom:
"<'row'<'col-sm-12'><'search-paper pmd-textfield'f>>" +
"<'custom-select-info'<'custom-select-item'><'custom-select-action'>>" +
"<'row'<'col-sm-12'tr>>" +
"<'pmd-card-footer' <'pmd-datatable-pagination' l i p>>",
});

/// Select value
$('.custom-select-info').hide();

$('#example-checkbox tbody').on( 'click', 'tr', function () {
if ( $(this).hasClass('selected') ) {
var rowinfo = $(this).closest('.dataTables_wrapper').find('.select-info').text();
$(this).closest('.dataTables_wrapper').find('.custom-select-info .custom-select-item').text(rowinfo);
if ($(this).closest('.dataTables_wrapper').find('.custom-select-info .custom-select-item').text() != null){
$(this).closest('.dataTables_wrapper').find('.custom-select-info').show();
//show delet button
} else{
$(this).closest('.dataTables_wrapper').find('.custom-select-info').hide();
}
}
else {
var rowinfo = $(this).closest('.dataTables_wrapper').find('.select-info').text();
$(this).closest('.dataTables_wrapper').find('.custom-select-info .custom-select-item').text(rowinfo);
}
if($('#example-checkbox').find('.selected').length == 0){
$(this).closest('.dataTables_wrapper').find('.custom-select-info').hide();
}
} );
$("div.data-table-title").html('');
$(".custom-select-action").html('<button class="btn btn-sm pmd-btn-fab pmd-btn-flat pmd-ripple-effect btn-primary" type="button"><i class="material-icons pmd-sm">delete</i></button><button class="btn btn-sm pmd-btn-fab pmd-btn-flat pmd-ripple-effect btn-primary" type="button"><i class="material-icons pmd-sm">more_vert</button>');

} );
</script>

</html>



</html>