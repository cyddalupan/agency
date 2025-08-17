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

<div class="col-md-14 col-sm-14" style="margin-top:-1px">
<div class="row">
<div class="col-md-12">
  <form action="" method="post">
  
<div class="clients-page">

<div class="col-sm-2 pull-right" align="center">
</div>


<div class="row" style="margin:30px">
<div class="col-md-12"  style="floar:left;background:white;padding:10px;min-height:150px;border:1px solid #DCDCDC;border:1px solid #DCDCDC;width:120%">






<form action="" method="post">


<?php
if($_GET['id']==0) { $stat="available";}
if($_GET['id']==5) { $stat="Line Up";}
if($_GET['id']==2) { $stat="Reserved";}
if($_GET['id']==4) { $stat="On Process";}
if($_GET['id']==12){ $stat="For Booking";}
if($_GET['id']==9) {$stat="Deployed";}
if($_GET['id']==15) { $stat="Passporting";}
?>

<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h1 style="color:black"><span>  Deployed </span></h1>
<!-- para -->
<?php
include'menuchange.php';
include'menu.php';
?>
<ul id="menu">
<li><a  href="dashboard.php"><?=$dashboard?></a></li>
<li><a  href="preselected.php"  ><?=$available?></a></li>
<li><a href="passporting.php"><?=$passporting?></a></li>
<li><a  href="line.php"> <?=$cv?></a></li>
<li><a  href="med.php"><?=$medical?></a></li>
<li><a   href="all-app.php"><?=$selected?></a></li>
<li><a   href="tesdanew.php"><?=$tesda?></a></li>
<li><a   href="musaned.php"><?=$musaned?></a></li>
<li><a   href="owwanew.php"><?=$owwa?></a></li>
<li><a    href="infosheet.php">Info Sheet</a></li>
<li><a   href="insurance.php">Insurance</a></li>
<li><a   href="bio.php">Biometrics</a></a></li>
<li><a   href="stampnew.php"><?=$visamenu?></a></li>
<li><a   href="oecnew.php"><?=$oecmenu?></a></li>
<li><a href="booking.php"><?=$bookingmenu?></a></li>
<li><a class="active"  href="deployednew.php"><?=$deployed?></a></li>
<li><a href="repatnew.php"><?=$repat?></a></li>
<li><a href="atoa.php"><?=$atoa?></a></li>
<li><a href="backouts.php"><?=$backoutmenu?></a></li>
<li style="float:right">

</li>
</ul>
<div style="clear:both;height:10px"></div>


</div> 

<div class="row">
<div class="col-md-12"  style=" padding-bottom:20px;">
	<div  class="pmd-card pmd-z-depth pmd-card-custom-view">
<div class="table-responsive">

<style>
TABLE th{
font-size:14px;	
}
TABLE tD{
font-size:12px;	
}
</style>



</br></br>

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


<a id="downloadLink" onclick="exportF(this)" style="color:Red">EXPORT TO EXCEL</a>


<table class="table table-hover table-striped table-bordered"  id="example-checkbox" STYLE="FONT-SIZE:12px;width:90%" >


<tr>

<th style="width:3%">#</th>
<th>Applicant #</th>
<th style="width:3%">Status</th>

<th style="width:6%">Flight Date</th>
<th >Med Expiry</th>
<th>Applicant Name</th>
<th>FRA</th>
<th>Age</th>
<th>Position</th>
<th>Country</th>
<th>Agent</th>
<th>Flight Date</th>

<th>Sponsor</th>

</tr>
<tr>
<?php

include'db.php';

?>
<?php
$count=1;
$fit = mysql_query("SELECT * FROM applicant
				LEFT JOIN  statuses
				ON applicant.applicant_status = statuses.number

			

				LEFT JOIN  	applicant_requirement
				ON applicant.applicant_id =applicant_requirement.requirement_applicant

				LEFT JOIN  	employer
				ON applicant.applicant_employer =employer.employer_id

				LEFT JOIN  	recruitment_agent
				ON applicant.applicant_source =recruitment_agent.agent_id


				LEFT JOIN  applicant_passport
				ON applicant.applicant_id =	applicant_passport.passport_applicant	
where  applicant_position_type='Household'
AND applicant_status  in(9)
group by applicant_id ORDER BY flight_date desc ");

while($row=mysql_fetch_array($fit))
{			
$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['applicant_employer']." ");
$employer=mysql_fetch_array($emp);

$agent = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
$agentrow1=mysql_fetch_array($agent);


$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);
$position = mysql_query("SELECT * FROM position where position_id=".$row['applicant_preferred_position']." ");
$position1=mysql_fetch_array($position);

$line = mysql_query("SELECT * FROM applicant_log where log_applicant=".$row['applicant_id']." and log_status=5 order by log_id desc");
$lineup=mysql_fetch_array($line);

echo'<tr>';

echo'<td>'.$count.'</td>';
echo"<td>".$row['applicantNumber']."</td>";
echo'<td>
<a href="'.$review.''.$row['applicant_id'].'"  target="_blank">
<button type="button" class="btn btn-'.$row["statusColors"].' btn-xs" style="font-size:12px">Deployed</button></a></td>';

echo'<td>'; echo date('M-d-Y', strtotime($row['flight_date']));echo'</td>';
IF($row['certificate_medical_expiration']=='1970-01-01' || $row['certificate_medical_expiration']=='0000-00-00'  || $row['certificate_medical_expiration']=='') { echo"<td></td>"; }
else{  echo'<td>'; echo date('M-d-Y', strtotime($row['certificate_medical_expiration']));echo'</td>'; }


echo'<td style="font-size:14px;font-weight:bold"><b>'.$row["applicant_first"].' '.$row["applicant_last"].'</b></td>';

echo'<td>'.$employer["employer_name"].'</td>';

echo"<td>".$row['applicant_age']."</td>";
echo"<td>".$position1['position_name']."</td>";
echo'<td>'.$countryrow["country_name"].'</td>';
echo'<td>'.$agentrow1["agent_first"].' '.$agentrow1["agent_last"].'</td>';
echo"<td>".$row['flight_date']."</td>";



echo'<td>'.$row["sub_employer"].'</td>';
echo'</tr>';
$count++;

}
ECHO'</table>';
	
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
order: [0, 'asc' ],
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
 