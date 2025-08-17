<?php include'review.php';?>
<!DOCTYPE html>
<html lang="en" ng-app="myApp">
<!-- Head -->
<head>
<meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
<title></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="../assets/styles/admin/bootstrap.min.css" rel="stylesheet" />
<link href="../assets/styles/admin/font-awesome.min.css" rel="stylesheet" />
</head>

<!-- /Head -->
<!-- Body -->
<body style="background:#F5F5F5">

<div class="col-md-21 col-sm-21" style="margin-top:-1px">
<div class="row">
<div class="col-md-12">
  <form action="" method="post">
  
<div class="clients-page">

<div class="col-sm-2 pull-right" align="center">
</div>


<div class="row" style="margin:30px">
<div class="col-md-12"  style="floar:left;background:white;padding:10px;min-height:150px;border:1px solid #DCDCDC;border:1px solid #DCDCDC;width:140%">

<?php
include'db.php';

include'function_contract.php';

$app = mysql_query("SELECT * FROM applicant
	LEFT JOIN  statuses
				ON applicant.applicant_status = statuses.number

				LEFT JOIN  	applicant_certificate
				ON applicant.applicant_id = applicant_certificate.certificate_applicant


				LEFT JOIN  	applicant_requirement
				ON applicant.applicant_id =applicant_requirement.requirement_applicant

				LEFT JOIN  	employer
				ON applicant.applicant_employer =employer.employer_id

				LEFT JOIN  	recruitment_agent
				ON applicant.applicant_source =recruitment_agent.agent_id


				LEFT JOIN  applicant_passport
				ON applicant.applicant_id =	applicant_passport.passport_applicant	

where
applicant_status  NOT IN (9,21,22,1,14,25,5)
AND applicant_employer!=0 
AND applicant_position_type='Household'

group by applicant_id

order by applicant_status ASC");
?>



<form action="" method="post">
<h1  style="color:black"><span><?=$selected?></span></h1> 
<div style="clear:both;height:10px"></div>


<?php
include'menuchange.php';
include'menu.php';
?>


<div class="hero">

<ul id="menu">
<li><a  href="dashboard.php"><?=$dashboard?></a></li>
<li><a  href="preselected.php"  ><?=$available?></a></li>
<li><a href="passporting.php"><?=$passporting?></a></li>
<li><a  href="line.php"> <?=$cv?></a></li>
<li><a   href="med.php"><?=$medical?></a></li>
<li><a  class="active" href="all-app.php"><?=$selected?></a></li>
<li><a   href="tesdanew.php"><?=$tesda?></a></li>
<li><a   href="musaned.php"><?=$musaned?></a></li>
<li><a   href="owwanew.php"><?=$owwa?></a></li>
<li><a   href="stampnew.php"><?=$visamenu?></a></li>
<li><a   href="oecnew.php"><?=$oecmenu?></a></li>
<li><a href="booking.php"><?=$bookingmenu?></a></li>
<li><a href="deployednew.php"><?=$deployed?></a></li>
<li><a href="repatnew.php"><?=$repat?></a></li>
<li><a href="atoa.php"><?=$atoa?></a></li>
<li><a href="backouts.php"><?=$backoutmenu?></a></li>

<li style="float:left">
<input type='submit'class="btn btn-sm btn-danger" name='updatedata' value='Update Data' style="height:28px;float:left;margin:10px;border:2px solid white">
</li>
</ul>
<div style="clear:both;height:10px"></div>
</div> 

<div class="row">
<div class="col-md-12"  style=" padding-bottom:20px;">
	<div  class="pmd-card pmd-z-depth pmd-card-custom-view">
<div class="table-responsive">
<h3>Under Process DH</h3>

<a href="all-app.php">Under Process DH </a> |  <a href="all-selected.php?user=<?=$_GET['user']?>" style="color:Red">Under Process Skilled</a>

<style>
TABLE th{
font-size:14px;	
}
TABLE tD{
font-size:12px;	
}
</style>



<table class="table table-hover table-striped table-bordered"  id="example-checkbox" STYLE="FONT-SIZE:12px;width:100%;MARGIN-top:5px" >
<thead>
<tr>
<th>STATUS</th>
<th>SUB STATUS</th>
<th>Covid Vac</th>
<th>Applicant</th>
<th style="width:100px">Date Applied</th>
<th>Days </th>
<th>Name</th>

<th>FRA</th>
<th>AGENT</th>
<th>Branch</th>
<th>Contract RECEIVED</th>
<th>Contract SIGNED</th>
<th>Visa  </th>	
<th>OWWA</th>	
<th>OEC</th>	
<th>Booking</th>	

<th>CONTACT</th>		
<th>SPOMSOR</th>
<th>PASSPORT</th>
<th>CV Send</th>
<th>MUSANED</th>	
<th>TESDA</th>										
										
</tr>
</thead>
<tbody>
   		
<?php

while($row=mysql_fetch_array($app))
{			


$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['applicant_employer']." ");
$employer=mysql_fetch_array($emp);

$cert = mysql_query("SELECT * FROM applicant_certificate where certificate_applicant=".$row['applicant_id']." ");
$row1=mysql_fetch_array($cert);

$req = mysql_query("SELECT * FROM applicant_requirement where requirement_applicant=".$row['applicant_id']." ");
$row2=mysql_fetch_array($req);

$agent = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
$agentrow1=mysql_fetch_array($agent);


if($row['applicant_status']=='4') {$color='orange';}
if($row['applicant_status']=='3') {$color='lightgray';}
if($row['applicant_status']=='2') {$color='blue';}
if($row['applicant_status']=='12') {$color='orange';}
if($row['applicant_status']=='8') {$color='orange';}


if($row['branch_type']==0){$branchme="N/A";}
if($row['branch_type']==7){$branchme="ILO ILO BRANCH";}
if($row['branch_type']==8){$branchme="CDO BRANCH";}
if($row['branch_type']==9){$branchme="DAVAO BRANCH";}
if($row['branch_type']==10){$branchme="BINANGON BRANCH";}
if($row['branch_type']==11){$branchme="CAVITE BRANCH";}
if($row['branch_type']==14){$branchme="MAIN MANILA BRANCH";}
if($row['branch_type']==15){$branchme="TACLOBAN BRANCH";}
if($row['branch_type']==13){$branchme="CEBU BRANCH";}



$number=str_pad($row['applicant_id'], 3, '0', STR_PAD_LEFT);
$effectiveDate = strtotime("+30 days", strtotime($date_now));
 $datenow1 = strtotime($date_now);
 $datenow= date("Y-m-d",$datenow1);
 
 


$date2=date("Y-m-d",$row1['certificate_medical_expiration']);
 $date3 = strtotime($date2);
echo"<tr>";	
echo'<td>
<a href='.$review.''.$row["applicant_id"].'  target="_blank">
<button type="button" class="btn btn-'.$row["statusColors"].' btn-xs" style="font-size:12px">'.$row["status"].'</button></a></td>';
echo"<td>".$row['sub_status']."</td>";
echo"<td>".$row['covidme']."</td>";
echo"<td>000".$row['applicant_id']."</td>";

$datecountapplied=date_create("".$row['applicant_date_applied']."");
$currentdate=date_create("".$date_now."");
$dateaddedfinal=date_diff($datecountapplied,$currentdate);
echo'<td>'; echo date('M-d-Y', strtotime($row['applicant_date_applied']));echo'</td>';
echo"<td style='color:black;width:100px'></div><u style='padding:5px;color:black;font-size:18px;background:orange';> ";
echo $dateaddedfinal->format("%r%a ");
echo "</u>";
echo"</td>";


echo'<td STYLE="FONT-SIZE:14PX;width:250px"><b>'.$row["applicant_first"].' '.$row["applicant_last"].'</b></td>';

echo"<td style='width:180px'>".$employer['employer_name']."</td>";
echo"<td  style='width:220px'>".$agentrow1['agent_first']."  ".$agentrow1['agent_last']."</td>";
echo'<td>'.$branchme.'</td>';
if(function_exists('date_default_timezone_set')) date_default_timezone_set($timezone);
$ip2= gethostbyaddr($_SERVER['REMOTE_ADDR']);
$timezone = "Asia/Kuala_Lumpur";
$date12=date('Y-m-d');


$line = mysql_query("SELECT * FROM applicants_logs_view where log_applicant=".$row['applicant_id']." AND log_status=5 Group by log_status desc");
$lineup=mysql_fetch_array($line);




if($row2['requirement_contract']=='1970-01-01' || $row2['requirement_contract']=='0000-00-00' || $row2['requirement_contract']==''){
$date_addedded12=date_create("".$lineup['log_created']."");
$visacr1=date_create("".$date12."");
$datelinefinal1=date_diff($date_addedded12,$visacr1);
}
else{

$dateline=date_create("".$lineup['log_created']."");
$datecontract=date_create("".$row2['requirement_contract']."");
$datelinefinal=date_diff($dateline,$currentdate);

}







IF($row2['requirement_contract']=='1970-01-01' || $row2['requirement_contract']=='0000-00-00' || $row2['requirement_contract']=='0001-01-01' || $row2['requirement_contract']=='' ) {

echo"<td></td>";
}
else{ 
echo"<td >"; echo date('M-d-Y', strtotime($row2['requirement_contract']));
echo"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <u style='background:red;padding:3px;color:white;font-size:14px';> ";
echo $datelinefinal->format("%r%a ");
echo "</u>";
echo"</td>";
}



IF($row2['requirement_contract_sign']=='1970-01-01' || $row2['requirement_contract_sign']=='0000-00-00' ||  $row2['requirement_contract_sign']=='0001-01-01' || $row2['requirement_contract_sign']=='' ) {
echo"<td></td>";
}

else{  echo'<td>'; echo date('M-d-Y', strtotime($row2['requirement_contract_sign']));echo'</td>'; }





$date_addedded122=date_create("".$row2['requirement_contract']."");
$visacr=date_create("".$row2['requirement_visa_stamp']."");
$diffvisacr=date_diff($date_addedded122,$visacr);
$d2=$diffvisacr;
IF($row2['requirement_visa_stamp']=='1970-01-01' || $row2['requirement_visa_stamp']=='0000-00-00' || $row2['requirement_visa_stamp']=='' ) {
echo"<td style='color:black'></td>";
}
else{ 
echo"<td >"; echo date('M-d-Y', strtotime($row['requirement_visa_stamp']));
echo"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <u style='background:red;padding:3px;color:white;font-size:14px';> ";
echo $diffvisacr->format("%r%a ");
echo "</u>";
echo"</td>";
}


 
IF($row1['certificate_owwa_to']=='1970-01-01' || $row1['certificate_owwa_to']=='0000-00-00' || $row1['certificate_owwa_to']=='' ) {
echo"<td></td>";
}
else{ 
 echo'<td>'; echo date('M-d-Y', strtotime($row1['certificate_owwa_to']));echo'</td>';
}



IF($row2['requirement_oec_release_date']=='1970-01-01' || $row2['requirement_oec_release_date']=='0000-00-00' || $row2['requirement_oec_release_date']=='' ) {
echo"<td></td>";
}
else{ 
echo'<td>'; echo date('M-d-Y', strtotime($row2['requirement_oec_release_date']));echo'</td>';
}




IF($row2['flight_date']=='1970-01-01' || $row2['flight_date']=='0000-00-00' || $row2['flight_date']=='' ) {
echo"<td></td>";
}
else{ 
echo'<td>'; echo date('M-d-Y', strtotime($row2['flight_date']));echo'</td>';
}

echo"<td>".$row['applicant_contacts']."</td>";
echo"<td>".$row['sub_employer']."</td>";
echo"<td>".$row['passport_number']."</td>";

 echo'<td>'; echo date('M-d-Y', strtotime($lineup['log_created']));echo'</td>';
IF($row2['requirement_musaned_approved']=='1970-01-01' || $row2['requirement_musaned_approved']=='0000-00-00' || $row2['requirement_musaned_approved']=='' ) {
echo"<td>--</td>";
}
else{ 
echo"<td >".$row2['requirement_musaned_approved']."</td>";
}
IF($row1['certificate_tesda_date']=='1970-01-01' || $row1['certificate_tesda_date']=='0000-00-00' || $row1['certificate_tesda_date']=='' ) {
echo"<td></td>";
}
else{ 
echo'<td>'; echo date('M-d-Y', strtotime($row1['certificate_tesda_date']));echo'</td>';
}


echo"</tr>";

}				
?>	

</tbody>
</table>

  </form>
<div style="clear:both;height:40px"></div>
</div>

</div>
</div>

</div>
</div>
</div>


</div>
</div>
</div>
</div>



</body>
<!--  /Body -->
</html>

<script src="assets/js/jquery-1.12.2.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/propeller.min.js"></script>


<!-- Datatable js -->
<script type="text/javascript" language="javascript" src="jsdatatables.js"></script>



<!-- Propeller Data table js-->
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
order: [ 7, 'desc' ],
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
"<'pmd-card-title'<'data-table-title'><'search-paper pmd-textfield'f>>" +
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
